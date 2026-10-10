<x-layouts.app>
    <x-slot name="title">Master Katalog Produk</x-slot>

    <div class="space-y-8" x-data="{ openCreate: false, editModal: false, editData: {} }">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <span class="eyebrow">Master Data</span>
                <h1 class="font-display font-semibold text-[1.75rem] sm:text-4xl text-brand-dark tracking-tight text-balance mt-2">Master Katalog Produk Baku</h1>
                <p class="text-[0.8125rem] text-brand-slate mt-1">Katalog acuan bahan baku untuk proses auto-matching kolektif.</p>
            </div>
            <button type="button" @click="openCreate = true" class="btn-primary text-sm py-3 px-6 shadow-md shadow-brand-primary/20 w-full sm:w-auto justify-center">
                Tambah Master Produk
            </button>
        </div>

        @php $hasProducts = $products->isNotEmpty(); @endphp

        <!-- Products -->
        @if ($hasProducts)
            <div class="card-shell">
                <div class="card-core p-6 sm:p-7 space-y-5">
                    <!-- Desktop: table -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="table-premium">
                            <thead>
                                <tr>
                                    <th>SKU / Nama Produk</th>
                                    <th>Kategori</th>
                                    <th>Satuan</th>
                                    <th>Spesifikasi Standar</th>
                                    <th class="text-right">Patokan Harga Eceran</th>
                                    <th class="text-center">Distributor</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $product)
                                    <tr>
                                        <td>
                                            <div class="font-bold text-brand-dark">{{ $product->name }}</div>
                                            <div class="text-[0.6875rem] text-brand-slate font-mono">{{ $product->sku ?? '-' }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-brand-mint text-brand-primary">
                                                {{ $product->category->name }}
                                            </span>
                                        </td>
                                        <td class="font-bold text-brand-dark">{{ $product->unit }}</td>
                                        <td class="text-brand-slate text-xs max-w-xs truncate">{{ $product->standard_specification ?? '-' }}</td>
                                        <td class="text-right font-bold text-brand-dark">
                                            Rp {{ number_format($product->benchmark_retail_price, 0, ',', '.') }} / {{ $product->unit }}
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-brand-ochre-soft text-brand-brass">
                                                {{ $product->supplierProducts->count() }} Distributor
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <button type="button" @click="editData = {{ json_encode($product) }}; editModal = true" class="min-h-11 px-3 text-xs text-brand-primary font-semibold hover:underline cursor-pointer">
                                                Edit
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile: cards -->
                    <div class="sm:hidden space-y-3">
                        @foreach ($products as $product)
                            <div class="list-card space-y-2.5">
                                <div class="flex justify-between items-start gap-3">
                                    <div class="min-w-0">
                                        <p class="font-bold text-brand-dark text-sm">{{ $product->name }}</p>
                                        <p class="font-mono text-[0.6875rem] text-brand-slate mt-0.5">{{ $product->sku ?? '-' }}</p>
                                    </div>
                                    <span class="badge bg-brand-mint text-brand-primary shrink-0">{{ $product->category->name }}</span>
                                </div>
                                <div class="flex items-baseline justify-between gap-3">
                                    <span class="text-[0.75rem] text-brand-slate">Per {{ $product->unit }}</span>
                                    <span class="font-extrabold text-brand-dark text-sm">Rp {{ number_format($product->benchmark_retail_price, 0, ',', '.') }}</span>
                                </div>
                                @if ($product->standard_specification)
                                    <p class="text-[0.75rem] text-brand-slate">{{ $product->standard_specification }}</p>
                                @endif
                                <div class="flex justify-between items-center pt-2 border-t border-black/[0.04]">
                                    <span class="badge bg-brand-ochre-soft text-brand-brass">{{ $product->supplierProducts->count() }} Distributor</span>
                                    <button type="button" @click="editData = {{ json_encode($product) }}; editModal = true" class="btn-ghost text-xs font-semibold text-brand-primary border-brand-primary/20">
                                        Edit
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-3 border-t border-black/[0.04]">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="card-shell">
                <div class="card-core px-6 py-12 text-center">
                    <p class="font-bold text-brand-dark">Belum ada data produk.</p>
                    <p class="text-[0.8125rem] text-brand-slate mt-1.5 max-w-md mx-auto">Tambahkan master produk sebagai acuan pencocokan harga bahan baku antar distributor.</p>
                    <button type="button" @click="openCreate = true" class="btn-primary text-sm mt-5">Tambah Master Produk</button>
                </div>
            </div>
        @endif

        <!-- Create Modal -->
        <x-modal open="openCreate" size="lg" eyebrow="Master Data" title="Tambah Master Produk Baru">
            <form method="POST" action="{{ route('admin.products.store') }}" class="pt-5 space-y-4">
                @csrf
                <div>
                    <label for="product_category" class="field-label">Kategori</label>
                    <select id="product_category" name="category_id" required class="input-field bg-white">
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="product_name" class="field-label">Nama bahan baku</label>
                    <input id="product_name" type="text" name="name" placeholder="Contoh: Beras Ramos Premium IR64" required class="input-field">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="product_sku" class="field-label">SKU</label>
                        <input id="product_sku" type="text" name="sku" placeholder="PRD-BRS-001" class="input-field">
                    </div>
                    <div>
                        <label for="product_unit" class="field-label">Satuan</label>
                        <input id="product_unit" type="text" name="unit" placeholder="kg / liter / sak" required class="input-field">
                    </div>
                </div>
                <div>
                    <label for="product_price" class="field-label">Patokan harga eceran pasar (Rp)</label>
                    <input id="product_price" type="number" name="benchmark_retail_price" step="100" placeholder="9000" required class="input-field">
                </div>
                <div>
                    <label for="product_spec" class="field-label">Spesifikasi standar</label>
                    <textarea id="product_spec" name="standard_specification" rows="2" placeholder="Standar mutu..." class="input-field"></textarea>
                </div>
                <div class="flex flex-col-reverse sm:flex-row-reverse sm:justify-end gap-3 pt-3 border-t border-black/[0.06]">
                    <button type="submit" class="btn-primary text-sm py-3 px-6 w-full sm:w-auto">Simpan</button>
                    <button type="button" @click="openCreate = false" class="btn-ghost text-sm py-3 w-full sm:w-auto">Batal</button>
                </div>
            </form>
        </x-modal>

        <!-- Edit Modal -->
        <x-modal open="editModal" size="lg" eyebrow="Master Data" title="Edit Master Produk">
            <form :action="`/admin/products/${editData.id}`" method="POST" class="pt-5 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="edit_product_category" class="field-label">Kategori</label>
                    <select id="edit_product_category" name="category_id" x-model="editData.category_id" required class="input-field bg-white">
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="edit_product_name" class="field-label">Nama bahan baku</label>
                    <input id="edit_product_name" type="text" name="name" x-model="editData.name" required class="input-field">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="edit_product_sku" class="field-label">SKU</label>
                        <input id="edit_product_sku" type="text" name="sku" x-model="editData.sku" class="input-field">
                    </div>
                    <div>
                        <label for="edit_product_unit" class="field-label">Satuan</label>
                        <input id="edit_product_unit" type="text" name="unit" x-model="editData.unit" required class="input-field">
                    </div>
                </div>
                <div>
                    <label for="edit_product_price" class="field-label">Patokan harga eceran pasar (Rp)</label>
                    <input id="edit_product_price" type="number" name="benchmark_retail_price" x-model="editData.benchmark_retail_price" step="100" required class="input-field">
                </div>
                <div>
                    <label for="edit_product_spec" class="field-label">Spesifikasi standar</label>
                    <textarea id="edit_product_spec" name="standard_specification" x-model="editData.standard_specification" rows="2" class="input-field"></textarea>
                </div>
                <label class="flex items-center gap-2.5 min-h-11 cursor-pointer">
                    <input type="checkbox" name="is_active" id="edit_p_active" :checked="editData.is_active" value="1" class="w-4 h-4 rounded border-brand-soft-grey text-brand-primary">
                    <span class="text-[0.875rem] font-medium text-brand-dark">Status aktif</span>
                </label>
                <div class="flex flex-col-reverse sm:flex-row-reverse sm:justify-end gap-3 pt-3 border-t border-black/[0.06]">
                    <button type="submit" class="btn-primary text-sm py-3 px-6 w-full sm:w-auto">Perbarui</button>
                    <button type="button" @click="editModal = false" class="btn-ghost text-sm py-3 w-full sm:w-auto">Batal</button>
                </div>
            </form>
        </x-modal>
    </div>
</x-layouts.app>
