<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProposalStatus;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductProposal;
use App\Models\SupplierProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductProposalController extends Controller
{
    public function index(): View
    {
        $proposals = ProductProposal::with('distributor.user', 'category', 'product')
            ->latest()
            ->paginate(10);

        $pendingCount = ProductProposal::where('status', ProposalStatus::PENDING)->count();

        return view('admin.proposals.index', compact('proposals', 'pendingCount'));
    }

    public function approve(Request $request, ProductProposal $proposal): RedirectResponse
    {
        abort_unless($proposal->status === ProposalStatus::PENDING, 422, 'Hanya usulan yang menunggu review yang bisa disetujui.');

        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($proposal, $validated) {
            $product = Product::create([
                'category_id' => $proposal->category_id,
                'name' => $proposal->name,
                'unit' => $proposal->unit,
                'standard_specification' => $proposal->standard_specification,
                'benchmark_retail_price' => $proposal->base_retail_price,
                'is_active' => true,
            ]);

            SupplierProduct::create([
                'distributor_id' => $proposal->distributor_id,
                'product_id' => $product->id,
                'stock_available' => $proposal->stock_available,
                'moq' => $proposal->moq,
                'base_retail_price' => $proposal->base_retail_price,
                'is_available' => true,
            ]);

            $proposal->update([
                'status' => ProposalStatus::APPROVED,
                'admin_note' => $validated['admin_note'] ?? null,
                'product_id' => $product->id,
            ]);
        });

        return back()->with('success', "Usulan \"{$proposal->name}\" disetujui dan masuk katalog master.");
    }

    public function reject(Request $request, ProductProposal $proposal): RedirectResponse
    {
        abort_unless($proposal->status === ProposalStatus::PENDING, 422, 'Hanya usulan yang menunggu review yang bisa ditolak.');

        $validated = $request->validate([
            'admin_note' => ['required', 'string', 'max:500'],
        ]);

        $proposal->update([
            'status' => ProposalStatus::REJECTED,
            'admin_note' => $validated['admin_note'],
        ]);

        return back()->with('success', "Usulan \"{$proposal->name}\" ditolak dengan catatan untuk distributor.");
    }
}
