<x-layouts.admin title="Master Kategori Produk">
    @php
        $user = auth()->user();
        $pendingProposals = $pendingProposals ?? 0;
    @endphp

    <div x-data="{ openCreate: false, editModal: false, editData: {} }">
        <!-- ============================ MOBILE ============================ -->
        <div class="lg:hidden pb-24">
            <!-- greeting -->
            <div class="px-4 pt-2 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="relative" x-data="{ menuOpen: false }">
                        <button type="button" @click="menuOpen = !menuOpen" class="w-11 h-11 rounded-2xl bg-forest text-white flex items-center justify-center font-extrabold text-sm cursor-pointer">{{ strtoupper(substr($user->name, 0, 2)) }}</button>
                        <div x-show="menuOpen" @click.away="menuOpen = false" x-cloak class="absolute left-0 mt-2 w-48 bg-white border border-line rounded-2xl shadow-xl p-2 z-40 text-xs">
                            <a href="{{ route('profile.complete') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-ink hover:bg-cream font-medium">Profil Saya</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-3 py-2.5 rounded-xl text-danger hover:bg-danger/10 font-bold transition text-left cursor-pointer">Keluar</button>
                            </form>
                        </div>
                    </div>
                    <div>
                        <p class="text-[11px] text-muted">Kontrol Pusat,</p>
                        <p class="text-[15px] font-extrabold leading-tight">{{ $user->name }}</p>
                    </div>
                </div>
                <div class="relative" x-data="{ notifOpen: false }">
                    <button type="button" @click="notifOpen = !notifOpen" class="w-10 h-10 rounded-2xl bg-white border border-line flex items-center justify-center text-ink relative cursor-pointer">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14.86 17.08a23.85 23.85 0 0 0 5.45-1.31A8.97 8.97 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.97 8.97 0 0 1-2.31 6.02c1.73.64 3.56 1.09 5.45 1.31m5.72 0a24.26 24.26 0 0 1-5.72 0m5.72 0a3 3 0 1 1-5.72 0"/></svg>
                    </button>
                    <div x-show="notifOpen" @click.away="notifOpen = false" x-cloak class="absolute right-0 mt-2 w-72 bg-white border border-line rounded-2xl shadow-xl p-4 z-40 text-left">
                        <p class="text-sm font-extrabold text-ink">Notifikasi</p>
                        <div class="mt-3 space-y-1">
                            <a href="{{ route('admin.proposals.index') }}" class="flex items-start gap-2 rounded-xl p-2 hover:bg-cream">
                                <span class="w-2 h-2 mt-1.5 rounded-full bg-brass shrink-0"></span>
                                <span class="text-xs text-ink"><b>{{ $pendingProposals }}</b> usulan produk menunggu review</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- content -->
            <div class="px-4 py-4">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div>
                        <span class="eyebrow">Master Data</span>
                        <h1 class="text-[1.75rem] sm:text-3xl font-extrabold text-ink tracking-tight mt-2">Kategori Bahan Baku</h1>
                        <p class="text-[0.8125rem] text-muted mt-1">Kelola pengelompokan bahan baku untuk kebutuhan UMKM.</p>
                    </div>
                    <button type="button" @click="openCreate = true" class="btn-primary w-full justify-center">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4.5v15m7.5-7.5h-15"/></svg> Tambah Kategori
                    </button>
                </div>

                @if ($categories->isNotEmpty())
                    <div class="flex flex-col sm:flex-row gap-2 mb-4">
                        <div class="flex items-center gap-2 bg-white border border-line rounded-2xl pl-4 pr-2 py-2 flex-1">
                            <span class="text-muted"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m21 21-5.2-5.2m0 0A7.5 7.5 0 1 0 5.2 5.2a7.5 7.5 0 0 0 10.6 10.6Z"/></svg></span>
                            <input class="flex-1 bg-transparent text-sm outline-none placeholder:text-muted/70" placeholder="Cari kategori...">
                        </div>
                    </div>

                    @foreach ($categories as $category)
                        <div class="card p-4 space-y-2 mb-3">
                            <div class="flex justify-between items-start gap-3">
                                <div class="min-w-0">
                                    <p class="font-bold text-ink text-sm">{{ $category->name }}</p>
                                    <p class="font-mono text-[11px] text-muted mt-0.5">{{ $category->slug }}</p>
                                </div>
                                <span class="badge {{ $category->is_active ? 'bg-success/10 text-success' : 'bg-cream text-muted' }}">{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            </div>
                            @if ($category->description)
                                <p class="text-xs text-muted">{{ $category->description }}</p>
                            @endif
                            <div class="flex justify-between items-center pt-2 border-t border-black/[0.04]">
                                <span class="text-xs text-muted">{{ $category->products_count }} produk</span>
                                <button type="button" @click="editData = {{ json_encode($category) }}; editModal = true" class="btn-ghost !py-2 !px-4 text-xs !text-forest">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg> Edit
                                </button>
                            </div>
                        </div>
                    @endforeach

                    <div class="flex items-center justify-between pt-4 mt-4 border-t border-black/[0.05]">
                        <p class="text-xs text-muted">{{ $categories->firstItem() }}&ndash;{{ $categories->lastItem() }} dari {{ $categories->total() }} kategori</p>
                        <div class="flex items-center gap-1.5">
                            @if ($categories->onFirstPage())
                                <span class="w-9 h-9 rounded-xl border border-line bg-white text-muted/40 flex items-center justify-center" aria-disabled="true">
                                    <svg class="w-4 h-4 rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                                </span>
                            @else
                                <a href="{{ $categories->previousPageUrl() }}" aria-label="Sebelumnya" class="w-9 h-9 rounded-xl border border-line bg-white text-muted flex items-center justify-center">
                                    <svg class="w-4 h-4 rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                                </a>
                            @endif
                            @foreach ($categories->getUrlRange(1, $categories->lastPage()) as $page => $url)
                                @if ($page === $categories->currentPage())
                                    <span class="w-9 h-9 rounded-xl bg-forest text-white text-xs font-bold flex items-center justify-center">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="w-9 h-9 rounded-xl border border-line bg-white text-muted text-xs font-bold flex items-center justify-center">{{ $page }}</a>
                                @endif
                            @endforeach
                            @if ($categories->hasMorePages())
                                <a href="{{ $categories->nextPageUrl() }}" aria-label="Berikutnya" class="w-9 h-9 rounded-xl border border-line bg-white text-muted flex items-center justify-center">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                                </a>
                            @else
                                <span class="w-9 h-9 rounded-xl border border-line bg-white text-muted/40 flex items-center justify-center" aria-disabled="true">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                                </span>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="card px-6 py-12 text-center">
                        <p class="font-bold text-ink">Belum ada data kategori.</p>
                        <p class="text-[0.8125rem] text-muted mt-1.5 max-w-md mx-auto">Tambahkan kategori untuk mengelompokkan bahan baku di katalog dan pencarian UMKM.</p>
                        <button type="button" @click="openCreate = true" class="btn-primary text-sm mt-5 mx-auto">Tambah Kategori</button>
                    </div>
                @endif
            </div>
        </div>

        <!-- bottom tab -->
        <div class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-white border-t border-line px-2 pt-2 pb-5 flex items-start justify-around">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 w-14 text-forest">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m2.25 12 8.95-8.95a1.13 1.13 0 0 1 1.6 0l8.95 8.95M4.5 9.75V19.5a.75.75 0 0 0 .75.75h13.5a.75.75 0 0 0 .75-.75V9.75"/></svg>
                <span class="text-[10px] font-bold">Beranda</span>
            </a>
            <a href="{{ route('admin.proposals.index') }}" class="flex flex-col items-center gap-1 w-14 text-muted relative">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5v14m-7-7h14"/></svg>
                <span class="text-[10px] font-semibold">Usulan</span>
                @if ($pendingProposals > 0)
                    <span class="absolute top-0 right-2 min-w-[18px] h-[18px] px-1 rounded-full bg-brass text-white text-[10px] font-bold flex items-center justify-center">{{ $pendingProposals }}</span>
                @endif
            </a>
            <a href="{{ route('admin.spk.index') }}" class="flex flex-col items-center gap-1 w-14 text-muted">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 20h18M6 16v-5m6 5V8m6 8v-9"/></svg>
                <span class="text-[10px] font-semibold">SPK</span>
            </a>
            <a href="{{ route('profile.complete') }}" class="flex flex-col items-center gap-1 w-14 text-muted">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17.98 18.72a6 6 0 0 0-8.46 0M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm-9.75 8.25a9 9 0 1 1 13.5 0"/></svg>
                <span class="text-[10px] font-semibold">Akun</span>
            </a>
        </div>

        <!-- ============================ DESKTOP ============================ -->
        <div class="hidden lg:flex min-h-screen">
            <!-- SIDEBAR -->
            <aside class="w-64 shrink-0 bg-white border-r border-line flex flex-col sticky top-0 h-screen">
                <div class="flex items-center gap-2.5 px-6 h-[72px] border-b border-line">
                    <span class="w-10 h-10 rounded-2xl bg-forest flex items-center justify-center text-white">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.25 7.5l-.63 10.63a2.25 2.25 0 0 1-2.25 2.12H6.63a2.25 2.25 0 0 1-2.25-2.12L3.75 7.5M10 11.25h4M3.38 7.5h17.25c.62 0 1.12-.5 1.12-1.12v-1.5C21.75 4.26 21.25 3.75 20.63 3.75H3.38c-.62 0-1.13.5-1.13 1.12v1.5c0 .63.5 1.13 1.12 1.13Z"/></svg>
                    </span>
                    <span class="text-xl font-extrabold tracking-tight">Kulaan</span>
                </div>
                <nav class="flex-1 px-4 py-6 space-y-1 text-sm font-semibold">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m2.25 12 8.95-8.95a1.13 1.13 0 0 1 1.6 0l8.95 8.95M4.5 9.75V19.5a.75.75 0 0 0 .75.75h13.5a.75.75 0 0 0 .75-.75V9.75"/></svg>
                        Dashboard
                    </a>
                    <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-forest text-white">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7a2 2 0 0 1 2-2h4l2 2h6a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7Z"/></svg>
                        Master Kategori
                    </a>
                    <a href="{{ route('admin.products.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5M12 22V12"/></svg>
                        Master Produk
                    </a>
                    <a href="{{ route('admin.proposals.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5v14m-7-7h14"/></svg>
                        Usulan Produk
                        @if ($pendingProposals > 0)
                            <span class="ml-auto bg-brass text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $pendingProposals }}</span>
                        @endif
                    </a>
                    <a href="{{ route('admin.spk.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 20h18M6 16v-5m6 5V8m6 8v-9"/></svg>
                        Kriteria SPK
                    </a>
                </nav>
                <div class="p-4 border-t border-line">
                    <a href="{{ route('profile.complete') }}" class="flex items-center gap-3 px-2">
                        <span class="w-10 h-10 rounded-2xl bg-forest text-white flex items-center justify-center font-extrabold text-xs">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                        <div class="min-w-0">
                            <p class="text-sm font-bold truncate">{{ $user->name }}</p>
                            <p class="text-[11px] text-muted">Kontrol Pusat</p>
                        </div>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="mt-3">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2.5 text-xs font-bold text-danger bg-danger/10 hover:bg-danger/15 rounded-xl transition cursor-pointer">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3h4a3 3 0 0 1 3 3v1"/></svg>
                            Keluar
                        </button>
                    </form>
                </div>
            </aside>

            <!-- MAIN -->
            <div class="flex-1 min-w-0">
                <header class="bg-white/70 backdrop-blur border-b border-line sticky top-0 z-10">
                    <div class="px-8 h-[72px] flex items-center gap-4">
                        <div>
                            <h1 class="text-lg font-extrabold leading-tight">Kategori Bahan Baku</h1>
                            <p class="text-xs text-muted">{{ now()->translatedFormat('l, d F Y') }}</p>
                        </div>
                        <div class="ml-auto flex items-center gap-3">
                            <div class="hidden lg:flex items-center gap-2 bg-white border border-line rounded-2xl pl-4 pr-2 py-2 w-72">
                                <svg class="w-4 h-4 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21 21-5.2-5.2m0 0A7.5 7.5 0 1 0 5.2 5.2a7.5 7.5 0 0 0 10.6 10.6Z"/></svg>
                                <input class="flex-1 bg-transparent text-sm outline-none placeholder:text-muted/70" placeholder="Cari UMKM, distributor...">
                            </div>
                            <div class="relative" x-data="{ notifOpen: false }">
                                <button type="button" @click="notifOpen = !notifOpen" class="w-11 h-11 rounded-2xl bg-white border border-line flex items-center justify-center relative cursor-pointer">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14.86 17.08a23.85 23.85 0 0 0 5.45-1.31A8.97 8.97 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.97 8.97 0 0 1-2.31 6.02c1.73.64 3.56 1.09 5.45 1.31m5.72 0a24.26 24.26 0 0 1-5.72 0m5.72 0a3 3 0 1 1-5.72 0"/></svg>
                                </button>
                                <div x-show="notifOpen" @click.away="notifOpen = false" x-cloak class="absolute right-0 mt-2 w-72 bg-white border border-line rounded-2xl shadow-xl p-4 z-40 text-left">
                                    <p class="text-sm font-extrabold text-ink">Notifikasi</p>
                                    <div class="mt-3 space-y-1">
                                        <a href="{{ route('admin.proposals.index') }}" class="flex items-start gap-2 rounded-xl p-2 hover:bg-cream">
                                            <span class="w-2 h-2 mt-1.5 rounded-full bg-brass shrink-0"></span>
                                            <span class="text-xs text-ink"><b>{{ $pendingProposals }}</b> usulan produk menunggu review</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <main class="px-8 py-6 max-w-6xl">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                        <div>
                            <span class="eyebrow">Master Data</span>
                            <h1 class="text-[1.75rem] sm:text-3xl font-extrabold text-ink tracking-tight mt-2">Kategori Bahan Baku</h1>
                            <p class="text-[0.8125rem] text-muted mt-1">Kelola pengelompokan bahan baku untuk kebutuhan UMKM.</p>
                        </div>
                        <button type="button" @click="openCreate = true" class="btn-primary w-full sm:w-auto justify-center">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4.5v15m7.5-7.5h-15"/></svg> Tambah Kategori
                        </button>
                    </div>

                    @if ($categories->isNotEmpty())
                        <div class="card p-6">
                            <div class="flex flex-col sm:flex-row gap-2 mb-4">
                                <div class="flex items-center gap-2 bg-white border border-line rounded-2xl pl-4 pr-2 py-2 flex-1">
                                    <span class="text-muted"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m21 21-5.2-5.2m0 0A7.5 7.5 0 1 0 5.2 5.2a7.5 7.5 0 0 0 10.6 10.6Z"/></svg></span>
                                    <input class="flex-1 bg-transparent text-sm outline-none placeholder:text-muted/70" placeholder="Cari kategori...">
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="tbl w-full">
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
                                                <td class="font-bold text-ink">{{ $category->name }}</td>
                                                <td class="text-muted font-mono text-xs">{{ $category->slug }}</td>
                                                <td class="text-muted text-xs max-w-[220px] truncate">{{ $category->description ?? '-' }}</td>
                                                <td class="text-center font-bold text-ink">{{ $category->products_count }}</td>
                                                <td class="text-center">
                                                    <span class="badge {{ $category->is_active ? 'bg-success/10 text-success' : 'bg-cream text-muted' }}">{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                                </td>
                                                <td class="text-right">
                                                    <button type="button" @click="editData = {{ json_encode($category) }}; editModal = true" class="inline-flex items-center gap-1 text-xs text-forest font-bold hover:underline cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg> Edit
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="flex items-center justify-between pt-4 mt-4 border-t border-black/[0.05]">
                                <p class="text-xs text-muted">Menampilkan {{ $categories->firstItem() }}&ndash;{{ $categories->lastItem() }} dari {{ $categories->total() }} kategori</p>
                                <div class="flex items-center gap-1.5">
                                    @if ($categories->onFirstPage())
                                        <span class="w-9 h-9 rounded-xl border border-line bg-white text-muted/40 flex items-center justify-center" aria-disabled="true">
                                            <svg class="w-4 h-4 rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                                        </span>
                                    @else
                                        <a href="{{ $categories->previousPageUrl() }}" aria-label="Sebelumnya" class="w-9 h-9 rounded-xl border border-line bg-white text-muted flex items-center justify-center">
                                            <svg class="w-4 h-4 rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                                        </a>
                                    @endif
                                    @foreach ($categories->getUrlRange(1, $categories->lastPage()) as $page => $url)
                                        @if ($page === $categories->currentPage())
                                            <span class="w-9 h-9 rounded-xl bg-forest text-white text-xs font-bold flex items-center justify-center">{{ $page }}</span>
                                        @else
                                            <a href="{{ $url }}" class="w-9 h-9 rounded-xl border border-line bg-white text-muted text-xs font-bold flex items-center justify-center">{{ $page }}</a>
                                        @endif
                                    @endforeach
                                    @if ($categories->hasMorePages())
                                        <a href="{{ $categories->nextPageUrl() }}" aria-label="Berikutnya" class="w-9 h-9 rounded-xl border border-line bg-white text-muted flex items-center justify-center">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                                        </a>
                                    @else
                                        <span class="w-9 h-9 rounded-xl border border-line bg-white text-muted/40 flex items-center justify-center" aria-disabled="true">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="card px-6 py-12 text-center">
                            <p class="font-bold text-ink">Belum ada data kategori.</p>
                            <p class="text-[0.8125rem] text-muted mt-1.5 max-w-md mx-auto">Tambahkan kategori untuk mengelompokkan bahan baku di katalog dan pencarian UMKM.</p>
                            <button type="button" @click="openCreate = true" class="btn-primary text-sm mt-5 mx-auto">Tambah Kategori</button>
                        </div>
                    @endif
                </main>
            </div>
        </div>

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
                    <input type="checkbox" name="is_active" id="edit_is_active" :checked="editData.is_active" value="1" class="w-4 h-4 rounded border-line text-forest accent-forest">
                    <span class="text-[0.875rem] font-medium text-ink">Status aktif</span>
                </label>
                <div class="flex flex-col-reverse sm:flex-row-reverse sm:justify-end gap-3 pt-3 border-t border-black/[0.06]">
                    <button type="submit" class="btn-primary text-sm py-3 px-6 w-full sm:w-auto">Perbarui</button>
                    <button type="button" @click="editModal = false" class="btn-ghost text-sm py-3 w-full sm:w-auto">Batal</button>
                </div>
            </form>
        </x-modal>
    </div>
</x-layouts.admin>
