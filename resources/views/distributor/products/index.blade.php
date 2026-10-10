<x-layouts.app>
    <x-slot name="title">Katalog & Tingkat Harga Grosir</x-slot>

    <div class="space-y-8" x-data="{
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
                <h1 class="font-display font-semibold text-[1.75rem] sm:text-4xl text-brand-dark tracking-tight text-balance mt-2">Katalog Bahan Baku &amp; Harga Tier</h1>
                <p class="text-[0.8125rem] text-brand-slate mt-1">Atur kapasitas stok, MOQ grosir, dan tingkatan harga bertingkat (price tiers).</p>
            </div>
            @if ($availableProducts->count() > 0)
                <button type="button" @click="openAddProduct = true" class="btn-primary text-sm py-3 px-6 shadow-md shadow-brand-primary/20 w-full sm:w-auto justify-center">
                    Daftarkan Produk Baru
                </button>
            @endif
        </div>

        @php $hasProducts = $supplierProducts->isNotEmpty(); @endphp

        <!-- Catalog -->
        @if ($hasProducts)
            <div class="card-shell">
                <div class="card-core p-6 sm:p-7 space-y-5">
                    <!-- Desktop: table -->
                    <div class="hidden lg:block overflow-x-auto">
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
                                @foreach ($supplierProducts as $sp)
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
                                                        <span class="font-extrabold text-brand-brass">
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
                                        <td class="text-right whitespace-nowrap">
                                            <button type="button" @click="
                                                selectedProduct = {{ json_encode($sp) }};
                                                tiers = {{ json_encode($sp->priceTiers) }};
                                                if (tiers.length === 0) {
                                                    tiers = [{ min_quantity: 1, max_quantity: 4.99, unit_price: {{ $sp->base_retail_price }} }];
                                                }
                                                tierModal = true;
                                            " class="min-h-11 px-2 text-xs text-brand-brass font-bold hover:underline cursor-pointer">
                                                Kelola Tier
                                            </button>
                                            <button type="button" @click="editProductData = {{ json_encode($sp) }}; openEditProduct = true;" class="min-h-11 px-2 text-xs text-brand-primary font-bold hover:underline cursor-pointer">
                                                Edit Stok/MOQ
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile: cards -->
                    <div class="lg:hidden space-y-3">
                        @foreach ($supplierProducts as $sp)
                            <div class="list-card space-y-3">
                                <div class="flex justify-between items-start gap-3">
                                    <div class="min-w-0">
                                        <p class="font-bold text-brand-dark text-sm">{{ $sp->product->name }}</p>
                                        <p class="text-[0.6875rem] text-brand-slate mt-0.5">{{ $sp->product->category->name }}</p>
                                    </div>
                                    <span class="badge {{ $sp->is_available ? 'bg-brand-mint text-brand-emerald' : 'bg-brand-cream text-brand-slate' }} shrink-0">
                                        {{ $sp->is_available ? 'Tersedia' : 'Habis' }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-3 gap-2 text-[0.75rem]">
                                    <div>
                                        <p class="text-brand-slate">Stok</p>
                                        <p class="font-bold text-brand-dark">{{ number_format($sp->stock_available, 0) }} {{ $sp->product->unit }}</p>
                                    </div>
                                    <div>
                                        <p class="text-brand-slate">MOQ</p>
                                        <p class="font-bold text-brand-dark">{{ number_format($sp->moq, 0) }} {{ $sp->product->unit }}</p>
                                    </div>
                                    <div>
                                        <p class="text-brand-slate">Eceran</p>
                                        <p class="font-semibold text-brand-dark">Rp {{ number_format($sp->base_retail_price, 0, ',', '.') }}</p>
                                    </div>
                                </div>

                                <div class="pt-2 border-t border-black/[0.04] space-y-1">
                                    @if ($sp->priceTiers->isEmpty())
                                        <p class="text-xs text-brand-slate italic">Belum ada tier harga</p>
                                    @else
                                        @foreach ($sp->priceTiers as $tier)
                                            <div class="text-xs flex items-center justify-between gap-2">
                                                <span class="font-medium text-brand-slate">
                                                    {{ number_format($tier->min_quantity, 0) }}{{ $tier->max_quantity ? ' - '.number_format($tier->max_quantity, 0) : '+' }} {{ $sp->product->unit }}
                                                </span>
                                                <span class="font-extrabold text-brand-brass">
                                                    Rp {{ number_format($tier->unit_price, 0, ',', '.') }}
                                                </span>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>

                                <div class="grid grid-cols-2 gap-2 pt-1">
                                    <button type="button" @click="
                                        selectedProduct = {{ json_encode($sp) }};
                                        tiers = {{ json_encode($sp->priceTiers) }};
                                        if (tiers.length === 0) {
                                            tiers = [{ min_quantity: 1, max_quantity: 4.99, unit_price: {{ $sp->base_retail_price }} }];
                                        }
                                        tierModal = true;
                                    " class="btn-ghost text-xs font-bold text-brand-brass border-amber-200">
                                        Kelola Tier
                                    </button>
                                    <button type="button" @click="editProductData = {{ json_encode($sp) }}; openEditProduct = true;" class="btn-ghost text-xs font-bold text-brand-primary border-brand-primary/20">
                                        Edit Stok/MOQ
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-3 border-t border-black/[0.04]">
                        {{ $supplierProducts->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="card-shell">
                <div class="card-core px-6 py-12 text-center">
                    <p class="font-bold text-brand-dark">Belum ada produk yang didaftarkan dalam katalog Anda.</p>
                    <p class="text-[0.8125rem] text-brand-slate mt-1.5 max-w-md mx-auto">Daftarkan produk baku beserta stok, MOQ, dan tier harga grosir untuk mulai menerima pesanan kolektif.</p>
                    @if ($availableProducts->count() > 0)
                        <button type="button" @click="openAddProduct = true" class="btn-primary text-sm mt-5">
                            Daftarkan Produk Baru
                        </button>
                    @endif
                </div>
            </div>
        @endif

        <!-- Add Product Modal -->
        <x-modal open="openAddProduct" size="lg"
                 eyebrow="Katalog"
                 title="Daftarkan Produk ke Toko Anda">
            <form method="POST" action="{{ route('distributor.products.store') }}" class="pt-5 space-y-4">
                @csrf
                <div>
                    <label for="product_id" class="field-label">Pilih produk baku</label>
                    <select id="product_id" name="product_id" required class="input-field bg-white">
                        @foreach ($availableProducts as $prod)
                            <option value="{{ $prod->id }}">{{ $prod->name }} ({{ $prod->unit }}) - Patokan Eceran: Rp {{ number_format($prod->benchmark_retail_price, 0) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="stock_available" class="field-label">Kapasitas stok</label>
                        <input id="stock_available" type="number" name="stock_available" value="1000" min="1" required class="input-field">
                    </div>
                    <div>
                        <label for="moq" class="field-label">MOQ grosir (min order)</label>
                        <input id="moq" type="number" name="moq" value="10" min="1" required class="input-field">
                    </div>
                </div>
                <div>
                    <label for="base_retail_price" class="field-label">Harga normal / eceran (Rp)</label>
                    <input id="base_retail_price" type="number" name="base_retail_price" step="100" placeholder="8000" required class="input-field">
                </div>

                <div class="pt-4 border-t border-black/[0.06]">
                    <p class="text-sm font-bold text-brand-dark mb-1">Tingkatan harga grosir kolektif</p>
                    <p class="field-hint mt-0 mb-3">Isi harga satuan per tingkatan. Kosongkan kolom max pada tier terakhir jika tanpa batas.</p>
                    <div class="space-y-3">
                        @foreach ([1, 2, 3] as $tierIndex)
                            <div class="p-3 rounded-xl border border-black/[0.06] bg-brand-cream/50">
                                <p class="text-[0.6875rem] font-bold text-brand-slate uppercase tracking-wide mb-2">Tier {{ $tierIndex }}</p>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                    <div>
                                        <label class="field-label" for="tier_{{ $tierIndex }}_min">Min qty</label>
                                        <input id="tier_{{ $tierIndex }}_min" type="number" name="tiers[{{ $tierIndex - 1 }}][min_quantity]" value="{{ [1, 5, 10][$tierIndex - 1] }}" class="input-field">
                                    </div>
                                    <div>
                                        <label class="field-label" for="tier_{{ $tierIndex }}_max">Max qty</label>
                                        <input id="tier_{{ $tierIndex }}_max" type="number" name="tiers[{{ $tierIndex - 1 }}][max_quantity]" value="{{ [4.99, 9.99, ''][$tierIndex - 1] }}" placeholder="Tanpa batas" class="input-field">
                                    </div>
                                    <div class="col-span-2 sm:col-span-1">
                                        <label class="field-label" for="tier_{{ $tierIndex }}_price">Harga / satuan (Rp)</label>
                                        <input id="tier_{{ $tierIndex }}_price" type="number" name="tiers[{{ $tierIndex - 1 }}][unit_price]" placeholder="{{ [8000, 7500, 6500][$tierIndex - 1] }}" class="input-field">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex flex-col-reverse sm:flex-row-reverse sm:justify-end gap-3 pt-3 border-t border-black/[0.06]">
                    <button type="submit" class="btn-primary text-sm py-3 px-6 w-full sm:w-auto">Simpan Produk</button>
                    <button type="button" @click="openAddProduct = false" class="btn-ghost text-sm py-3 w-full sm:w-auto">Batal</button>
                </div>
            </form>
        </x-modal>

        <!-- Edit Product Modal -->
        <x-modal open="openEditProduct" size="sm"
                 eyebrow="Katalog"
                 title="Edit Stok &amp; MOQ Produk">
            <form :action="`/distributor/products/${editProductData.id}`" method="POST" class="pt-5 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="edit_stock" class="field-label">Kapasitas stok tersedia</label>
                    <input id="edit_stock" type="number" name="stock_available" x-model="editProductData.stock_available" min="0" required class="input-field">
                </div>
                <div>
                    <label for="edit_moq" class="field-label">MOQ grosir (minimum kuantitas)</label>
                    <input id="edit_moq" type="number" name="moq" x-model="editProductData.moq" min="1" required class="input-field">
                </div>
                <div>
                    <label for="edit_price" class="field-label">Harga normal / eceran (Rp)</label>
                    <input id="edit_price" type="number" name="base_retail_price" x-model="editProductData.base_retail_price" step="100" required class="input-field">
                </div>
                <label class="flex items-center gap-2.5 min-h-11 cursor-pointer">
                    <input type="checkbox" name="is_available" id="sp_avail" :checked="editProductData.is_available" value="1" class="w-4 h-4 rounded border-brand-soft-grey text-brand-primary">
                    <span class="text-[0.875rem] font-medium text-brand-dark">Tersedia untuk pengadaan kolektif</span>
                </label>
                <div class="flex flex-col-reverse sm:flex-row-reverse sm:justify-end gap-3 pt-3 border-t border-black/[0.06]">
                    <button type="submit" class="btn-primary text-sm py-3 px-6 w-full sm:w-auto">Simpan Perubahan</button>
                    <button type="button" @click="openEditProduct = false" class="btn-ghost text-sm py-3 w-full sm:w-auto">Batal</button>
                </div>
            </form>
        </x-modal>

        <!-- Manage Tiers Modal -->
        <x-modal open="tierModal" size="lg"
                 eyebrow="Harga Grosir"
                 title="Kelola Tingkatan Harga Grosir">
            <p class="text-xs text-brand-slate mt-4 font-semibold" x-text="selectedProduct ? selectedProduct.product?.name : ''"></p>

            <form :action="`/distributor/products/${selectedProduct?.id}/tiers`" method="POST" class="pt-4 space-y-4">
                @csrf
                @method('PUT')

                <div class="space-y-3">
                    <template x-for="(tier, index) in tiers" :key="index">
                        <div class="p-3 rounded-xl border border-black/[0.06] bg-brand-cream/50">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[0.6875rem] font-bold text-brand-slate uppercase tracking-wide" x-text="`Tier ${index + 1}`"></span>
                                <button type="button" @click="tiers.splice(index, 1)" class="btn-icon min-w-11 min-h-11 text-brand-brick hover:bg-red-50" :aria-label="`Hapus tier ${index + 1}`">&times;</button>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                <div>
                                    <label class="field-label">Min qty</label>
                                    <input type="number" :name="`tiers[${index}][min_quantity]`" x-model="tier.min_quantity" required placeholder="Min" class="input-field">
                                </div>
                                <div>
                                    <label class="field-label">Max qty</label>
                                    <input type="number" :name="`tiers[${index}][max_quantity]`" x-model="tier.max_quantity" placeholder="Kosongkan = tanpa batas" class="input-field">
                                </div>
                                <div class="col-span-2 sm:col-span-1">
                                    <label class="field-label">Harga / satuan (Rp)</label>
                                    <input type="number" :name="`tiers[${index}][unit_price]`" x-model="tier.unit_price" required placeholder="Harga" class="input-field">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <button type="button" @click="tiers.push({ min_quantity: '', max_quantity: '', unit_price: '' })" class="min-h-11 px-2 text-sm font-semibold text-brand-primary hover:underline cursor-pointer">
                    + Tambah Baris Tier
                </button>

                <div class="flex flex-col-reverse sm:flex-row-reverse sm:justify-end gap-3 pt-3 border-t border-black/[0.06]">
                    <button type="submit" class="btn-primary text-sm py-3 px-6 w-full sm:w-auto">Simpan Tier</button>
                    <button type="button" @click="tierModal = false" class="btn-ghost text-sm py-3 w-full sm:w-auto">Batal</button>
                </div>
            </form>
        </x-modal>
    </div>
</x-layouts.app>
