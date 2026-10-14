<?php

namespace App\Http\Controllers\Umkm;

use App\Enums\PurchaseRequestStatus;
use App\Enums\UrgencyLevel;
use App\Http\Controllers\Controller;
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

        return view('umkm.cart.index', compact('items', 'total', 'totalSavings', 'umkm'));
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

        DB::transaction(function () use ($items, $umkm, &$createdProductIds) {
            foreach ($items as $item) {
                PurchaseRequest::create([
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
            }
        });

        $this->cart->clear();

        foreach (array_unique($createdProductIds) as $productId) {
            $matchingEngine->processMatchingForProduct($productId);
        }

        return redirect()->route('umkm.requests.index')
            ->with('success', 'Checkout berhasil! '.count($createdProductIds).' pengajuan dibuat. Sistem sedang menggabungkan kuota dari UMKM lain.');
    }
}
