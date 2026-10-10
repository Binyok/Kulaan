<x-layouts.app>
    <x-slot name="title">Master Kategori Produk</x-slot>

    <div class="space-y-8" x-data="{ openCreate: false, editModal: false, editData: {} }">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <span class="eyebrow">Master Data</span>
                <h1 class="font-display font-semibold text-[1.75rem] sm:text-4xl text-brand-dark tracking-tight text-balance mt-2">Kategori Bahan Baku</h1>
                <p class="text-[0.8125rem] text-brand-slate mt-1">Kelola pengelompokan bahan baku untuk kebutuhan UMKM.</p>
            </div>
            <button type="button" @click="openCreate = true" class="btn-primary text-sm py-3 px-6 shadow-md shadow-brand-primary/20 w-full sm:w-auto justify-center">
                Tambah Kategori
            </button>
        </div>

        @php $hasCategories = $categories->isNotEmpty(); @endphp

        <!-- Categories -->
        @if ($hasCategories)
            <div class="card-shell">
                <div class="card-core p-6 sm:p-7 space-y-5">
                    <!-- Desktop: table -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="table-premium">
                            <thead>
                                <tr>
                                    <th>Nama Kategori</th>
                                    <th>Slug</th>
                                    <th>Deskripsi</th>
                                    <th class="text-center">Jumlah Produk</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($categories as $category)
                                    <tr>
                                        <td class="font-bold text-brand-dark">{{ $category->name }}</td>
                                        <td class="text-brand-slate font-mono text-xs">{{ $category->slug }}</td>
                                        <td class="text-brand-slate text-xs max-w-xs truncate">{{ $category->description ?? '-' }}</td>
                                        <td class="text-center font-bold text-brand-dark">{{ $category->products_count }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $category->is_active ? 'bg-brand-mint text-brand-emerald' : 'bg-brand-cream text-brand-slate' }}">
                                                {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <button type="button" @click="editData = {{ json_encode($category) }}; editModal = true" class="min-h-11 px-3 text-xs text-brand-primary font-semibold hover:underline cursor-pointer">
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
                        @foreach ($categories as $category)
                            <div class="list-card space-y-2">
                                <div class="flex justify-between items-start gap-3">
                                    <div class="min-w-0">
                                        <p class="font-bold text-brand-dark text-sm">{{ $category->name }}</p>
                                        <p class="font-mono text-[0.6875rem] text-brand-slate mt-0.5">{{ $category->slug }}</p>
                                    </div>
                                    <span class="badge {{ $category->is_active ? 'bg-brand-mint text-brand-emerald' : 'bg-brand-cream text-brand-slate' }} shrink-0">
                                        {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </div>
                                @if ($category->description)
                                    <p class="text-[0.75rem] text-brand-slate">{{ $category->description }}</p>
                                @endif
                                <div class="flex justify-between items-center pt-2 border-t border-black/[0.04]">
                                    <span class="text-[0.75rem] text-brand-slate">{{ $category->products_count }} produk</span>
                                    <button type="button" @click="editData = {{ json_encode($category) }}; editModal = true" class="btn-ghost text-xs font-semibold text-brand-primary border-brand-primary/20">
                                        Edit
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-3 border-t border-black/[0.04]">
                        {{ $categories->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="card-shell">
                <div class="card-core px-6 py-12 text-center">
                    <p class="font-bold text-brand-dark">Belum ada data kategori.</p>
                    <p class="text-[0.8125rem] text-brand-slate mt-1.5 max-w-md mx-auto">Tambahkan kategori untuk mengelompokkan bahan baku di katalog dan pencarian UMKM.</p>
                    <button type="button" @click="openCreate = true" class="btn-primary text-sm mt-5">Tambah Kategori</button>
                </div>
            </div>
        @endif

        <!-- Create Modal -->
        <x-modal open="openCreate" size="sm" eyebrow="Master Data" title="Tambah Kategori Baru">
            <form method="POST" action="{{ route('admin.categories.store') }}" class="pt-5 space-y-4">
                @csrf
                <div>
                    <label for="category_name" class="field-label">Nama kategori</label>
                    <input id="category_name" type="text" name="name" required class="input-field">
                </div>
                <div>
                    <label for="category_description" class="field-label">Deskripsi (opsional)</label>
                    <textarea id="category_description" name="description" rows="3" class="input-field"></textarea>
                </div>
                <div class="flex flex-col-reverse sm:flex-row-reverse sm:justify-end gap-3 pt-3 border-t border-black/[0.06]">
                    <button type="submit" class="btn-primary text-sm py-3 px-6 w-full sm:w-auto">Simpan</button>
                    <button type="button" @click="openCreate = false" class="btn-ghost text-sm py-3 w-full sm:w-auto">Batal</button>
                </div>
            </form>
        </x-modal>

        <!-- Edit Modal -->
        <x-modal open="editModal" size="sm" eyebrow="Master Data" title="Edit Kategori">
            <form :action="`/admin/categories/${editData.id}`" method="POST" class="pt-5 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="edit_category_name" class="field-label">Nama kategori</label>
                    <input id="edit_category_name" type="text" name="name" x-model="editData.name" required class="input-field">
                </div>
                <div>
                    <label for="edit_category_description" class="field-label">Deskripsi (opsional)</label>
                    <textarea id="edit_category_description" name="description" x-model="editData.description" rows="3" class="input-field"></textarea>
                </div>
                <label class="flex items-center gap-2.5 min-h-11 cursor-pointer">
                    <input type="checkbox" name="is_active" id="edit_is_active" :checked="editData.is_active" value="1" class="w-4 h-4 rounded border-brand-soft-grey text-brand-primary">
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
