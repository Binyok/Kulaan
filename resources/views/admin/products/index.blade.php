<x-layouts.app>
    <x-slot name="title">Master Katalog Produk</x-slot>

    <div class="space-y-8 animate-fade-up" x-data="{ openCreate: false, editModal: false, editData: {} }">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <span class="eyebrow">Master Data</span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-brand-dark tracking-tight mt-1.5">Master Katalog Produk Baku</h1>
                <p class="text-[0.8125rem] text-brand-slate mt-1">Katalog acuan bahan baku untuk proses auto-matching kolektif.</p>
            </div>
            <button @click="openCreate = true" class="btn-primary text-sm py-3 px-6 shadow-md shadow-brand-primary/20">
                + Tambah Master Produk
            </button>
        </div>

        <!-- Table (Double-Bezel) -->
        <div class="card-shell">
            <div class="card-core p-6 sm:p-7 space-y-5">
                <div class="overflow-x-auto -mx-6 sm:mx-0">
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
                            @forelse ($products as $product)
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
                                        <span class="badge bg-brand-ochre-soft text-brand-accent">
                                            {{ $product->supplierProducts->count() }} Distributor
                                        </span>
                                    </td>
                                    <td class="text-right space-x-2">
                                        <button @click="editData = {{ json_encode($product) }}; editModal = true" class="text-xs text-brand-primary font-semibold hover:underline cursor-pointer">
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-brand-slate text-[0.8125rem]">Belum ada data produk.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="pt-3 border-t border-black/[0.04]">
                    {{ $products->links() }}
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <div x-show="openCreate" x-cloak class="modal-overlay">
            <div @click.away="openCreate = false" class="modal-content max-w-lg space-y-5 animate-fade-up">
                <div class="flex justify-between items-center pb-3 border-b border-black/[0.04]">
                    <h2 class="text-base font-bold text-brand-dark">Tambah Master Produk Baru</h2>
                    <button @click="openCreate = false" class="text-brand-slate hover:text-brand-dark text-xl font-bold cursor-pointer">&times;</button>
                </div>
                <form method="POST" action="{{ route('admin.products.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Kategori</label>
                        <select name="category_id" required class="input-field bg-white">
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Nama Bahan Baku</label>
                        <input type="text" name="name" placeholder="Contoh: Beras Ramos Premium IR64" required class="input-field">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">SKU</label>
                            <input type="text" name="sku" placeholder="PRD-BRS-001" class="input-field">
                        </div>
                        <div>
                            <label class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Satuan</label>
                            <input type="text" name="unit" placeholder="kg / liter / sak" required class="input-field">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Patokan Harga Eceran Pasar (Rp)</label>
                        <input type="number" name="benchmark_retail_price" step="100" placeholder="9000" required class="input-field">
                    </div>
                    <div>
                        <label class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Spesifikasi Standar</label>
                        <textarea name="standard_specification" rows="2" placeholder="Standar mutu..." class="input-field"></textarea>
                    </div>
                    <div class="flex justify-end gap-2.5 pt-2">
                        <button type="button" @click="openCreate = false" class="btn-ghost text-xs py-2 px-4">Batal</button>
                        <button type="submit" class="btn-primary text-xs py-2 px-5">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Modal -->
        <div x-show="editModal" x-cloak class="modal-overlay">
            <div @click.away="editModal = false" class="modal-content max-w-lg space-y-5 animate-fade-up">
                <div class="flex justify-between items-center pb-3 border-b border-black/[0.04]">
                    <h2 class="text-base font-bold text-brand-dark">Edit Master Produk</h2>
                    <button @click="editModal = false" class="text-brand-slate hover:text-brand-dark text-xl font-bold cursor-pointer">&times;</button>
                </div>
                <form :action="`/admin/products/${editData.id}`" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Kategori</label>
                        <select name="category_id" x-model="editData.category_id" required class="input-field bg-white">
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Nama Bahan Baku</label>
                        <input type="text" name="name" x-model="editData.name" required class="input-field">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">SKU</label>
                            <input type="text" name="sku" x-model="editData.sku" class="input-field">
                        </div>
                        <div>
                            <label class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Satuan</label>
                            <input type="text" name="unit" x-model="editData.unit" required class="input-field">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Patokan Harga Eceran Pasar (Rp)</label>
                        <input type="number" name="benchmark_retail_price" x-model="editData.benchmark_retail_price" step="100" required class="input-field">
                    </div>
                    <div>
                        <label class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Spesifikasi Standar</label>
                        <textarea name="standard_specification" x-model="editData.standard_specification" rows="2" class="input-field"></textarea>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <input type="checkbox" name="is_active" id="edit_p_active" :checked="editData.is_active" value="1" class="w-4 h-4 rounded-md border-brand-soft-grey text-brand-primary">
                        <label for="edit_p_active" class="text-[0.8125rem] font-medium text-brand-dark">Status Aktif</label>
                    </div>
                    <div class="flex justify-end gap-2.5 pt-2">
                        <button type="button" @click="editModal = false" class="btn-ghost text-xs py-2 px-4">Batal</button>
                        <button type="submit" class="btn-primary text-xs py-2 px-5">Perbarui</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
