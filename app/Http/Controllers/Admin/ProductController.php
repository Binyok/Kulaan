<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::with('category', 'supplierProducts.distributor')->latest()->paginate(10);
        $categories = ProductCategory::where('is_active', true)->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:product_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:50', 'unique:products,sku'],
            'unit' => ['required', 'string', 'max:20'],
            'standard_specification' => ['nullable', 'string'],
            'benchmark_retail_price' => ['required', 'numeric', 'min:0'],
        ]);

        Product::create($validated);

        return back()->with('success', 'Master produk berhasil ditambahkan.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:product_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:50', 'unique:products,sku,'.$product->id],
            'unit' => ['required', 'string', 'max:20'],
            'standard_specification' => ['nullable', 'string'],
            'benchmark_retail_price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $product->update([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'sku' => $validated['sku'] ?? $product->sku,
            'unit' => $validated['unit'],
            'standard_specification' => $validated['standard_specification'] ?? $product->standard_specification,
            'benchmark_retail_price' => $validated['benchmark_retail_price'],
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Master produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return back()->with('success', 'Master produk berhasil dihapus.');
    }
}
