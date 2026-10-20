<?php

namespace App\Http\Controllers\Umkm;

use App\Enums\PaymentStatus;
use App\Enums\PurchaseRequestStatus;
use App\Enums\UrgencyLevel;
use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\PurchaseRequest;
use App\Rules\DistributorOffersProduct;
use App\Services\CartService;
use App\Services\Matching\MatchingEngineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index(Request $request): View
    {
        $umkm = $request->user()->umkm;
        if (! $umkm) {
            abort(403, 'Profil UMKM tidak ditemukan.');
        }

        $items = $this->cart->items();
        $total = $this->cart->total();
        $totalSavings = $this->cart->totalSavings();
        $moqProgress = $this->moqProgress($items);

        return view('umkm.cart.index', compact('items', 'total', 'totalSavings', 'umkm', 'moqProgress'));
    }

    /**
     * @param  array<string, array<string, mixed>>  $items
     * @return array{target: float, gathered: float, remaining: float, percent: int, unit: string}
     */
    private function moqProgress(array $items): array
    {
        $target = 0.0;
        $gathered = 0.0;
        $units = [];

        foreach ($items as $item) {
            $pooled = (float) PurchaseRequest::where('product_id', $item['product_id'])
                ->where('preferred_distributor_id', $item['distributor_id'])
                ->where('status', PurchaseRequestStatus::PENDING_MATCH->value)
                ->sum('quantity');

            $target += (float) $item['moq'];
            $gathered += $pooled + (float) $item['quantity'];
            $units[] = (string) $item['product']->unit;
        }

        $target = round($target, 2);
        $gathered = round($gathered, 2);

        return [
            'target' => $target,
            'gathered' => $gathered,
            'remaining' => round(max(0, $target - $gathered), 2),
            'percent' => $target > 0 ? (int) min(100, round($gathered / $target * 100)) : 0,
            'unit' => count(array_unique($units)) === 1 ? ($units[0] ?? '') : '',
        ];
    }

    public function add(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'distributor_id' => ['required', 'integer', 'exists:distributors,id'],
            'quantity' => ['required', 'numeric', 'min:0.1'],
        ]);

        $this->cart->add(
            (int) $validated['product_id'],
            (int) $validated['distributor_id'],
            (float) $validated['quantity']
        );

        return back()->with('success', 'Produk berhasil ditambahkan ke keranjang.');
    }

    public function buyNow(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'distributor_id' => ['required', 'integer', 'exists:distributors,id'],
            'quantity' => ['required', 'numeric', 'min:0.1'],
        ]);

        $this->cart->add(
            (int) $validated['product_id'],
            (int) $validated['distributor_id'],
            (float) $validated['quantity']
        );

        return redirect()->route('umkm.cart.index')->with('success', 'Produk ditambahkan. Lanjutkan ke pembayaran.');
    }

    public function updateItem(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer'],
            'distributor_id' => ['required', 'integer'],
            'quantity' => ['required', 'numeric', 'min:0'],
        ]);

        $this->cart->updateQty(
            (int) $validated['product_id'],
            (int) $validated['distributor_id'],
            (float) $validated['quantity']
        );

        if ($request->expectsJson()) {
            $key = $this->cart->itemKey((int) $validated['product_id'], (int) $validated['distributor_id']);
            $items = $this->cart->items();
            $updatedItem = $items[$key] ?? null;

            return response()->json([
                'success' => true,
                'removed' => $updatedItem === null,
                'item' => $updatedItem,
                'total' => $this->cart->total(),
                'total_savings' => $this->cart->totalSavings(),
                'count' => $this->cart->count(),
                'moq_progress' => $this->moqProgress($items),
            ]);
        }

        return back()->with('success', 'Keranjang diperbarui.');
    }

    public function removeItem(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer'],
            'distributor_id' => ['required', 'integer'],
        ]);

        $this->cart->remove((int) $validated['product_id'], (int) $validated['distributor_id']);

        return back()->with('success', 'Item dihapus dari keranjang.');
    }

    public function checkout(Request $request, MatchingEngineService $matchingEngine): RedirectResponse
    {
        $umkm = $request->user()->umkm;
        if (! $umkm) {
            abort(403, 'Profil UMKM tidak ditemukan.');
        }

        $items = $this->cart->items();

        if (empty($items)) {
            return back()->with('error', 'Keranjang kosong. Tambahkan produk terlebih dahulu.');
        }

        foreach ($items as $item) {
            $rule = new DistributorOffersProduct($item['product_id'], $item['quantity']);
            $messages = [];
            $rule->validate(
                'distributor_id',
                $item['distributor_id'],
                function (string $msg) use (&$messages) {
                    $messages[] = $msg;
                }
            );

            if (! empty($messages)) {
                return redirect()->route('umkm.cart.index')
                    ->with('error', 'Gagal checkout: '.implode('; ', $messages));
            }
        }

        $createdProductIds = [];
        $createdRequestIds = [];

        DB::transaction(function () use ($items, $umkm, &$createdProductIds, &$createdRequestIds) {
            foreach ($items as $item) {
                $req = PurchaseRequest::create([
                    'umkm_id' => $umkm->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['product']->unit,
                    'deadline' => now()->addHours(24),
                    'urgency' => UrgencyLevel::SEDANG,
                    'delivery_district' => $umkm->district ?? '',
                    'delivery_address' => $umkm->address ?? '',
                    'notes' => null,
                    'status' => PurchaseRequestStatus::PENDING_MATCH,
                    'preferred_distributor_id' => $item['distributor_id'],
                ]);

                $createdProductIds[] = $item['product_id'];
                $createdRequestIds[] = $req->id;
            }
        });

        $this->cart->clear();

        $groupsCreated = 0;
        foreach (array_unique($createdProductIds) as $productId) {
            $res = $matchingEngine->processMatchingForProduct($productId);
            $groupsCreated += ($res['groups_created'] ?? 0);
        }

        // Cek apakah ada purchase request baru yang langsung ORDERED (MOQ terpenuhi)
        $unpaidOrderItem = null;
        if (! empty($createdRequestIds)) {
            $unpaidOrderItem = OrderItem::whereIn('purchase_request_id', $createdRequestIds)
                ->where('umkm_id', $umkm->id)
                ->where('payment_status', PaymentStatus::UNPAID)
                ->latest()
                ->first();
        }

        if ($unpaidOrderItem) {
            return redirect()->route('payment.show', $unpaidOrderItem)
                ->with('success', 'Selamat! Kuota minimum (MOQ) langsung terpenuhi dan pesanan kolektif terbentuk. Silakan lakukan pembayaran tagihan Anda di bawah ini.');
        }

        return redirect()->route('umkm.requests.index')
            ->with('success', 'Checkout berhasil! '.count($createdProductIds).' pengajuan dibuat. Sistem sedang menggabungkan kuota dari UMKM lain.');
    }
}
