<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = ProductCategory::withCount('products')->latest()->paginate(10);

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:product_categories,name'],
            'description' => ['nullable', 'string'],
        ]);

        ProductCategory::create($validated);

        return back()->with('success', 'Kategori produk berhasil ditambahkan.');
    }

    public function update(Request $request, ProductCategory $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:product_categories,name,'.$category->id],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? $category->description,
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Kategori produk berhasil diperbarui.');
    }

    public function destroy(ProductCategory $category): RedirectResponse
    {
        $category->delete();

        return back()->with('success', 'Kategori produk berhasil dihapus.');
    }
}
