<x-layouts.app>
    <x-slot name="title">Katalog & Tingkat Harga Grosir</x-slot>

    <div class="space-y-8 animate-fade-up" x-data="{ 
        openAddProduct: false, 
        tierModal: false, 
        selectedProduct: null,
        tiers: [],
        openEditProduct: false,
        editProductData: {}
    }">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <span class="eyebrow">Distributor Portal</span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-brand-dark tracking-tight mt-1.5">Katalog Bahan Baku & Harga Tier</h1>
                <p class="text-[0.8125rem] text-brand-slate mt-1">Atur kapasitas stok, MOQ grosir, dan tingkatan harga bertingkat (price tiers).</p>
            </div>
            @if ($availableProducts->count() > 0)
                <button @click="openAddProduct = true" class="btn-primary text-sm py-3 px-6 shadow-md shadow-brand-primary/20">
                    + Daftarkan Produk Baru
                </button>
            @endif
        </div>

        <!-- Table (Double-Bezel) -->
        <div class="card-shell">
            <div class="card-core p-6 sm:p-7 space-y-5">
                <div class="overflow-x-auto -mx-6 sm:mx-0">
                    <table class="table-premium">
                        <thead>
                            <tr>
                                <th>Nama Produk Baku</th>
                                <th>Stok Tersedia</th>
                                <th>MOQ Grosir</th>
                                <th>Harga Eceran</th>
                                <th>Tingkat Harga Grosir (Price Tiers)</th>
                                <th class="text-center">Status</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($supplierProducts as $sp)
                                <tr>
                                    <td>
                                        <div class="font-bold text-brand-dark">{{ $sp->product->name }}</div>
                                        <div class="text-[0.6875rem] text-brand-slate">{{ $sp->product->category->name }}</div>
                                    </td>
                                    <td class="font-bold text-brand-dark">
                                        {{ number_format($sp->stock_available, 0) }} {{ $sp->product->unit }}
                                    </td>
                                    <td>
                                        <span class="badge bg-brand-mint text-brand-primary">
                                            Min {{ number_format($sp->moq, 0) }} {{ $sp->product->unit }}
                                        </span>
                                    </td>
                                    <td class="font-semibold text-brand-dark">
                                        Rp {{ number_format($sp->base_retail_price, 0, ',', '.') }}
                                    </td>
                                    <td>
                                        <div class="space-y-1">
                                            @forelse ($sp->priceTiers as $tier)
                                                <div class="text-xs flex items-center gap-1.5">
                                                    <span class="font-medium text-brand-slate">
                                                        {{ number_format($tier->min_quantity, 0) }}{{ $tier->max_quantity ? ' - '.number_format($tier->max_quantity, 0) : '+' }} {{ $sp->product->unit }}:
                                                    </span>
                                                    <span class="font-extrabold text-brand-accent">
                                                        Rp {{ number_format($tier->unit_price, 0, ',', '.') }}
                                                    </span>
                                                </div>
                                            @empty
                                                <span class="text-xs text-brand-slate italic">Belum ada tier harga</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $sp->is_available ? 'bg-brand-mint text-brand-emerald' : 'bg-brand-cream text-brand-slate' }}">
                                            {{ $sp->is_available ? 'Tersedia' : 'Habis' }}
                                        </span>
                                    </td>
                                    <td class="text-right space-x-2 whitespace-nowrap">
                                        <button @click="
                                            selectedProduct = {{ json_encode($sp) }}; 
                                            tiers = {{ json_encode($sp->priceTiers) }}; 
                                            if (tiers.length === 0) {
                                                tiers = [{ min_quantity: 1, max_quantity: 4.99, unit_price: {{ $sp->base_retail_price }} }];
                                            }
                                            tierModal = true;
                                        " class="text-xs text-brand-accent font-bold hover:underline cursor-pointer">
                                            Kelola Tier
                                        </button>
                                        <button @click="editProductData = {{ json_encode($sp) }}; openEditProduct = true;" class="text-xs text-brand-primary font-bold hover:underline cursor-pointer">
                                            Edit Stok/MOQ
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-brand-slate text-[0.8125rem]">
                                        Belum ada produk yang didaftarkan dalam katalog Anda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="pt-3 border-t border-black/[0.04]">
                    {{ $supplierProducts->links() }}
                </div>
            </div>
        </div>

        <!-- Add Product Modal -->
        <div x-show="openAddProduct" x-cloak class="modal-overlay">
            <div @click.away="openAddProduct = false" class="modal-content max-w-lg space-y-5 animate-fade-up max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center pb-3 border-b border-black/[0.04]">
                    <h2 class="text-base font-bold text-brand-dark">Daftarkan Produk ke Toko Anda</h2>
                    <button @click="openAddProduct = false" class="text-brand-slate hover:text-brand-dark text-xl font-bold cursor-pointer">&times;</button>
                </div>
                <form method="POST" action="{{ route('distributor.products.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Pilih Produk Baku</label>
                        <select name="product_id" required class="input-field bg-white">
                            @foreach ($availableProducts as $prod)
                                <option value="{{ $prod->id }}">{{ $prod->name }} ({{ $prod->unit }}) - Patokan Eceran: Rp {{ number_format($prod->benchmark_retail_price, 0) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Kapasitas Stok</label>
                            <input type="number" name="stock_available" value="1000" min="1" required class="input-field">
                        </div>
                        <div>
                            <label class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">MOQ Grosir (Min Order)</label>
                            <input type="number" name="moq" value="10" min="1" required class="input-field">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Harga Normal / Eceran (Rp)</label>
                        <input type="number" name="base_retail_price" step="100" placeholder="8000" required class="input-field">
                    </div>

                    <div class="pt-3 border-t border-black/[0.04]">
                        <p class="text-xs font-bold text-brand-dark mb-2">Tingkatan Harga Grosir Kolektif Awal</p>
                        <div class="space-y-2">
                            <div class="grid grid-cols-3 gap-2 text-[0.6875rem] text-brand-slate font-medium">
                                <span>Min Qty</span>
                                <span>Max Qty</span>
                                <span>Harga Satuan (Rp)</span>
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <input type="number" name="tiers[0][min_quantity]" value="1" class="input-field py-1 text-xs">
                                <input type="number" name="tiers[0][max_quantity]" value="4.99" class="input-field py-1 text-xs">
                                <input type="number" name="tiers[0][unit_price]" placeholder="8000" class="input-field py-1 text-xs">
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <input type="number" name="tiers[1][min_quantity]" value="5" class="input-field py-1 text-xs">
                                <input type="number" name="tiers[1][max_quantity]" value="9.99" class="input-field py-1 text-xs">
                                <input type="number" name="tiers[1][unit_price]" placeholder="7500" class="input-field py-1 text-xs">
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <input type="number" name="tiers[2][min_quantity]" value="10" class="input-field py-1 text-xs">
                                <input type="number" name="tiers[2][max_quantity]" placeholder="Tanpa batas" class="input-field py-1 text-xs">
                                <input type="number" name="tiers[2][unit_price]" placeholder="6500" class="input-field py-1 text-xs">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2.5 pt-2">
                        <button type="button" @click="openAddProduct = false" class="btn-ghost text-xs py-2 px-4">Batal</button>
                        <button type="submit" class="btn-primary text-xs py-2 px-5">Simpan Produk</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Product Modal -->
        <div x-show="openEditProduct" x-cloak class="modal-overlay">
            <div @click.away="openEditProduct = false" class="modal-content max-w-md space-y-5 animate-fade-up">
                <div class="flex justify-between items-center pb-3 border-b border-black/[0.04]">
                    <h2 class="text-base font-bold text-brand-dark">Edit Stok & MOQ Produk</h2>
                    <button @click="openEditProduct = false" class="text-brand-slate hover:text-brand-dark text-xl font-bold cursor-pointer">&times;</button>
                </div>
                <form :action="`/distributor/products/${editProductData.id}`" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Kapasitas Stok Tersedia</label>
                        <input type="number" name="stock_available" x-model="editProductData.stock_available" min="0" required class="input-field">
                    </div>
                    <div>
                        <label class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">MOQ Grosir (Minimum Kuantitas)</label>
                        <input type="number" name="moq" x-model="editProductData.moq" min="1" required class="input-field">
                    </div>
                    <div>
                        <label class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Harga Normal / Eceran (Rp)</label>
                        <input type="number" name="base_retail_price" x-model="editProductData.base_retail_price" step="100" required class="input-field">
                    </div>
                    <div class="flex items-center gap-2.5">
                        <input type="checkbox" name="is_available" id="sp_avail" :checked="editProductData.is_available" value="1" class="w-4 h-4 rounded-md border-brand-soft-grey text-brand-primary">
                        <label for="sp_avail" class="text-[0.8125rem] font-medium text-brand-dark">Tersedia untuk Pengadaan Kolektif</label>
                    </div>
                    <div class="flex justify-end gap-2.5 pt-2">
                        <button type="button" @click="openEditProduct = false" class="btn-ghost text-xs py-2 px-4">Batal</button>
                        <button type="submit" class="btn-primary text-xs py-2 px-5">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Manage Tiers Modal -->
        <div x-show="tierModal" x-cloak class="modal-overlay">
            <div @click.away="tierModal = false" class="modal-content max-w-lg space-y-5 animate-fade-up">
                <div class="flex justify-between items-center pb-3 border-b border-black/[0.04]">
                    <div>
                        <h2 class="text-base font-bold text-brand-dark">Kelola Tingkatan Harga Grosir</h2>
                        <p class="text-[0.6875rem] text-brand-slate" x-text="selectedProduct ? selectedProduct.product?.name : ''"></p>
                    </div>
                    <button @click="tierModal = false" class="text-brand-slate hover:text-brand-dark text-xl font-bold cursor-pointer">&times;</button>
                </div>
                
                <form :action="`/distributor/products/${selectedProduct?.id}/tiers`" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    
                    <div class="space-y-2">
                        <template x-for="(tier, index) in tiers" :key="index">
                            <div class="flex items-center gap-2">
                                <div class="w-1/3">
                                    <label class="text-[10px] text-brand-slate font-medium" x-show="index === 0">Min Qty</label>
                                    <input type="number" :name="`tiers[${index}][min_quantity]`" x-model="tier.min_quantity" required placeholder="Min" class="input-field py-1 text-xs">
                                </div>
                                <div class="w-1/3">
                                    <label class="text-[10px] text-brand-slate font-medium" x-show="index === 0">Max Qty (opsional)</label>
                                    <input type="number" :name="`tiers[${index}][max_quantity]`" x-model="tier.max_quantity" placeholder="Max" class="input-field py-1 text-xs">
                                </div>
                                <div class="w-1/3">
                                    <label class="text-[10px] text-brand-slate font-medium" x-show="index === 0">Harga/Satuan (Rp)</label>
                                    <input type="number" :name="`tiers[${index}][unit_price]`" x-model="tier.unit_price" required placeholder="Harga" class="input-field py-1 text-xs">
                                </div>
                                <div :class="index === 0 ? 'pt-4' : ''">
                                    <button type="button" @click="tiers.splice(index, 1)" class="text-brand-brick hover:text-red-700 text-sm font-bold px-1.5 py-1 cursor-pointer">&times;</button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <button type="button" @click="tiers.push({ min_quantity: '', max_quantity: '', unit_price: '' })" class="text-xs font-semibold text-brand-primary hover:underline cursor-pointer">
                        + Tambah Baris Tier
                    </button>

                    <div class="flex justify-end gap-2.5 pt-3 border-t border-black/[0.04]">
                        <button type="button" @click="tierModal = false" class="btn-ghost text-xs py-2 px-4">Batal</button>
                        <button type="submit" class="btn-primary text-xs py-2 px-5">Simpan Tier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
