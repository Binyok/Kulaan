<?php

namespace App\Http\Controllers\Distributor;

use App\Enums\ProposalStatus;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductProposal;
use App\Models\SupplierPriceTier;
use App\Models\SupplierProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SupplierProductController extends Controller
{
    public function index(Request $request): View
    {
        $distributor = $request->user()->distributor;
        if (! $distributor) {
            abort(403, 'Akses khusus profil distributor.');
        }

        $supplierProducts = SupplierProduct::with('product.category', 'priceTiers')
            ->where('distributor_id', $distributor->id)
            ->latest()
            ->paginate(10);

        $availableProducts = Product::where('is_active', true)
            ->whereNotIn('id', $supplierProducts->pluck('product_id'))
            ->get();

        $proposals = ProductProposal::with('category')
            ->where('distributor_id', $distributor->id)
            ->latest()
            ->paginate(10, ['*'], 'usulan');

        $categories = ProductCategory::where('is_active', true)->orderBy('name')->get();

        return view('distributor.products.index', compact('supplierProducts', 'availableProducts', 'proposals', 'categories', 'distributor'));
    }

    public function store(Request $request): RedirectResponse
    {
        $distributor = $request->user()->distributor;
        if (! $distributor) {
            abort(403, 'Akses khusus profil distributor.');
        }

        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'stock_available' => ['required', 'numeric', 'min:1'],
            'moq' => ['required', 'numeric', 'min:1'],
            'base_retail_price' => ['required', 'numeric', 'min:0'],
            'tiers' => ['nullable', 'array', $this->tiersMustNotOverlap()],
            'tiers.*.min_quantity' => ['required_with:tiers', 'numeric', 'min:1'],
            'tiers.*.max_quantity' => ['nullable', 'numeric', 'gte:tiers.*.min_quantity'],
            'tiers.*.unit_price' => ['required_with:tiers', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($distributor, $validated) {
            $supplierProduct = SupplierProduct::create([
                'distributor_id' => $distributor->id,
                'product_id' => $validated['product_id'],
                'stock_available' => $validated['stock_available'],
                'moq' => $validated['moq'],
                'base_retail_price' => $validated['base_retail_price'],
                'is_available' => true,
            ]);

            if (! empty($validated['tiers'])) {
                foreach ($validated['tiers'] as $tier) {
                    if (! empty($tier['min_quantity']) && ! empty($tier['unit_price'])) {
                        SupplierPriceTier::create([
                            'supplier_product_id' => $supplierProduct->id,
                            'min_quantity' => $tier['min_quantity'],
                            'max_quantity' => $tier['max_quantity'] ?? null,
                            'unit_price' => $tier['unit_price'],
                        ]);
                    }
                }
            }
        });

        return back()->with('success', 'Katalog produk & tingkatan harga tier berhasil ditambahkan.');
    }

    public function update(Request $request, SupplierProduct $supplierProduct): RedirectResponse
    {
        $distributor = $request->user()->distributor;
        if ($supplierProduct->distributor_id !== $distributor->id) {
            abort(403);
        }

        $validated = $request->validate([
            'stock_available' => ['required', 'numeric', 'min:0'],
            'moq' => ['required', 'numeric', 'min:1'],
            'base_retail_price' => ['required', 'numeric', 'min:0'],
            'is_available' => ['nullable', 'boolean'],
        ]);

        $supplierProduct->update([
            'stock_available' => $validated['stock_available'],
            'moq' => $validated['moq'],
            'base_retail_price' => $validated['base_retail_price'],
            'is_available' => $request->has('is_available'),
        ]);

        return back()->with('success', 'Data stok dan MOQ berhasil diperbarui.');
    }

    public function updateTiers(Request $request, SupplierProduct $supplierProduct): RedirectResponse
    {
        $distributor = $request->user()->distributor;
        if ($supplierProduct->distributor_id !== $distributor->id) {
            abort(403);
        }

        $validated = $request->validate([
            'tiers' => ['required', 'array', $this->tiersMustNotOverlap()],
            'tiers.*.min_quantity' => ['required', 'numeric', 'min:1'],
            'tiers.*.max_quantity' => ['nullable', 'numeric', 'gte:tiers.*.min_quantity'],
            'tiers.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($supplierProduct, $validated) {
            $supplierProduct->priceTiers()->delete();
            foreach ($validated['tiers'] as $tier) {
                if (! empty($tier['min_quantity']) && ! empty($tier['unit_price'])) {
                    SupplierPriceTier::create([
                        'supplier_product_id' => $supplierProduct->id,
                        'min_quantity' => $tier['min_quantity'],
                        'max_quantity' => $tier['max_quantity'] ?: null,
                        'unit_price' => $tier['unit_price'],
                    ]);
                }
            }
        });

        return back()->with('success', 'Tingkatan harga tier berhasil disimpan.');
    }

    public function storeProposal(Request $request): RedirectResponse
    {
        $distributor = $request->user()->distributor;
        if (! $distributor) {
            abort(403, 'Akses khusus profil distributor.');
        }

        $validated = $request->validate([
            'category_id' => ['required', 'exists:product_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:50'],
            'standard_specification' => ['nullable', 'string', 'max:1000'],
            'stock_available' => ['required', 'numeric', 'min:1'],
            'moq' => ['required', 'numeric', 'min:1'],
            'base_retail_price' => ['required', 'numeric', 'min:0'],
        ]);

        $alreadyPending = ProductProposal::where('distributor_id', $distributor->id)
            ->where('name', $validated['name'])
            ->where('status', ProposalStatus::PENDING)
            ->exists();

        if ($alreadyPending) {
            return back()->with('error', 'Usulan produk ini masih menunggu review admin.');
        }

        ProductProposal::create([
            ...$validated,
            'distributor_id' => $distributor->id,
            'status' => ProposalStatus::PENDING,
        ]);

        return back()->with('success', 'Usulan produk dikirim. Admin akan mereview sebelum masuk katalog master.');
    }

    public function destroyProposal(Request $request, ProductProposal $proposal): RedirectResponse
    {
        $distributor = $request->user()->distributor;
        if (! $distributor || $proposal->distributor_id !== $distributor->id) {
            abort(403);
        }

        abort_unless($proposal->status === ProposalStatus::PENDING, 422, 'Hanya usulan yang menunggu review yang bisa ditarik.');

        $proposal->delete();

        return back()->with('success', 'Usulan produk ditarik kembali.');
    }

    /**
     * Ensure each tier has a valid range (max >= min) and that no two tiers overlap.
     *
     * @return \Closure(string, mixed, \Closure): void
     */
    private function tiersMustNotOverlap(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if (! is_array($value)) {
                return;
            }

            $ranges = [];

            foreach ($value as $index => $tier) {
                if (! is_array($tier) || ! isset($tier['min_quantity'])) {
                    continue;
                }

                $min = (float) $tier['min_quantity'];
                $max = isset($tier['max_quantity']) && $tier['max_quantity'] !== null
                    ? (float) $tier['max_quantity']
                    : null;

                if ($max !== null && $max < $min) {
                    $fail('Setiap tier harga: batas maksimal kuantitas harus lebih besar atau sama dengan batas minimal.');

                    return;
                }

                $ranges[] = ['min' => $min, 'max' => $max];
            }

            usort($ranges, fn (array $a, array $b) => $a['min'] <=> $b['min']);

            for ($i = 1; $i < count($ranges); $i++) {
                $previous = $ranges[$i - 1];
                $current = $ranges[$i];

                if ($previous['max'] === null || $current['min'] <= $previous['max']) {
                    $fail('Rentang kuantitas antar tier harga saling tumpang tindih. Pastikan batas tiap tier tidak beririsan.');

                    return;
                }
            }
        };
    }
}
