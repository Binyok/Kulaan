<x-layouts.app>
    <x-slot name="title">Master Kategori Produk</x-slot>

    <div class="space-y-8 animate-fade-up" x-data="{ openCreate: false, editModal: false, editData: {} }">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <span class="eyebrow">Master Data</span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-brand-dark tracking-tight mt-1.5">Kategori Bahan Baku</h1>
                <p class="text-[0.8125rem] text-brand-slate mt-1">Kelola pengelompokan bahan baku untuk kebutuhan UMKM.</p>
            </div>
            <button @click="openCreate = true" class="btn-primary text-sm py-3 px-6 shadow-md shadow-brand-primary/20">
                + Tambah Kategori
            </button>
        </div>

        <!-- Table (Double-Bezel) -->
        <div class="card-shell">
            <div class="card-core p-6 sm:p-7 space-y-5">
                <div class="overflow-x-auto -mx-6 sm:mx-0">
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
                            @forelse ($categories as $category)
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
                                    <td class="text-right space-x-2">
                                        <button @click="editData = {{ json_encode($category) }}; editModal = true" class="text-xs text-brand-primary font-semibold hover:underline cursor-pointer">
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-brand-slate text-[0.8125rem]">Belum ada data kategori.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="pt-3 border-t border-black/[0.04]">
                    {{ $categories->links() }}
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <div x-show="openCreate" x-cloak class="modal-overlay">
            <div @click.away="openCreate = false" class="modal-content max-w-md space-y-5 animate-fade-up">
                <div class="flex justify-between items-center pb-3 border-b border-black/[0.04]">
                    <h2 class="text-base font-bold text-brand-dark">Tambah Kategori Baru</h2>
                    <button @click="openCreate = false" class="text-brand-slate hover:text-brand-dark text-xl font-bold cursor-pointer">&times;</button>
                </div>
                <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Nama Kategori</label>
                        <input type="text" name="name" required class="input-field">
                    </div>
                    <div>
                        <label class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Deskripsi</label>
                        <textarea name="description" rows="3" class="input-field"></textarea>
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
            <div @click.away="editModal = false" class="modal-content max-w-md space-y-5 animate-fade-up">
                <div class="flex justify-between items-center pb-3 border-b border-black/[0.04]">
                    <h2 class="text-base font-bold text-brand-dark">Edit Kategori</h2>
                    <button @click="editModal = false" class="text-brand-slate hover:text-brand-dark text-xl font-bold cursor-pointer">&times;</button>
                </div>
                <form :action="`/admin/categories/${editData.id}`" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Nama Kategori</label>
                        <input type="text" name="name" x-model="editData.name" required class="input-field">
                    </div>
                    <div>
                        <label class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Deskripsi</label>
                        <textarea name="description" x-model="editData.description" rows="3" class="input-field"></textarea>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <input type="checkbox" name="is_active" id="edit_is_active" :checked="editData.is_active" value="1" class="w-4 h-4 rounded-md border-brand-soft-grey text-brand-primary">
                        <label for="edit_is_active" class="text-[0.8125rem] font-medium text-brand-dark">Status Aktif</label>
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
