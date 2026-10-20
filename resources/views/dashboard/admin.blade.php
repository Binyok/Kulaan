<x-layouts.app>
    <x-slot name="title">Dashboard Administrator</x-slot>

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
            <x-notification-bell />
        </div>

        <!-- hero -->
        <div class="px-4 mt-4">
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-forest-deep via-forest to-forest-dark text-white p-5">
                <div class="absolute -right-10 -top-12 w-44 h-44 rounded-full bg-white/10"></div>
                <div class="absolute right-16 -bottom-14 w-36 h-36 rounded-full bg-brass/20"></div>
                <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-brass-soft">Kontrol Pusat</p>
                <h2 class="text-lg font-extrabold mt-1 leading-snug">Dashboard Administrator</h2>
                <p class="text-[11px] text-white/70 mt-1.5 leading-relaxed">Pantau denyut ekosistem: siapa gabung, berapa hemat tercipta, dan seberapa sehat mesin SPK-nya.</p>
            </div>
        </div>

        <!-- big gold hero stat -->
        <div class="px-4 mt-4">
            <div class="bg-white rounded-3xl border-2 border-brass/40 p-5 relative overflow-hidden">
                <div class="absolute -right-6 -top-6 w-28 h-28 rounded-full bg-brass-soft"></div>
                <div class="flex items-center justify-between relative">
                    <p class="text-xs font-bold uppercase tracking-wider text-muted">Total Hemat Ekosistem</p>
                    <span class="w-9 h-9 rounded-xl bg-brass-soft text-brass flex items-center justify-center text-xs font-extrabold">Rp</span>
                </div>
                <p class="text-[2rem] font-extrabold text-brass tabular-nums mt-2 relative">Rp {{ number_format($totalSavingsSystem, 0, ',', '.') }}</p>
                <p class="text-[11px] text-muted mt-1 relative">akumulasi hemat seluruh UMKM vs harga eceran</p>
            </div>
        </div>

        <!-- mini stats -->
        <div class="px-4 mt-3 grid grid-cols-3 gap-2.5">
            <div class="bg-white rounded-3xl border border-line p-3.5">
                <p class="text-2xl font-extrabold">{{ $totalUmkm }}</p>
                <p class="text-[10px] text-muted font-semibold mt-1 leading-tight">UMKM terdaftar</p>
                <span class="inline-block mt-2 bg-forest/10 text-forest text-[10px] font-bold px-2 py-0.5 rounded-full">+{{ $newUmkmThisWeek }} minggu ini</span>
            </div>
            <div class="bg-white rounded-3xl border border-line p-3.5">
                <p class="text-2xl font-extrabold">{{ $totalDistributors }}</p>
                <p class="text-[10px] text-muted font-semibold mt-1 leading-tight">Distributor rekanan</p>
                <span class="inline-block mt-2 bg-forest/10 text-forest text-[10px] font-bold px-2 py-0.5 rounded-full">+{{ $newDistributorsThisMonth }} bulan ini</span>
            </div>
            <div class="bg-white rounded-3xl border border-line p-3.5">
                <p class="text-2xl font-extrabold">{{ $totalCouriers }}</p>
                <p class="text-[10px] text-muted font-semibold mt-1 leading-tight">Armada kurir</p>
                <span class="inline-block mt-2 bg-success/10 text-success text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $activeCouriers }} aktif</span>
            </div>
        </div>

        <!-- pending approvals callout -->
        <div class="px-4 mt-4">
            <div class="rounded-3xl bg-brass-soft border border-brass/30 p-4 flex items-center gap-3">
                <span class="w-11 h-11 rounded-2xl bg-white text-brass flex items-center justify-center shrink-0 font-extrabold">{{ $pendingProposals }}</span>
                <div class="flex-1 min-w-0">
                    <p class="text-[13px] font-extrabold text-ink">Usulan produk menunggu review</p>
                    <p class="text-[11px] text-muted mt-0.5">Produk custom dari distributor butuh persetujuanmu.</p>
                </div>
                <a href="{{ route('admin.proposals.index') }}" class="shrink-0 h-10 px-4 rounded-xl bg-brass text-white text-xs font-bold flex items-center justify-center">Review</a>
            </div>
        </div>

        <!-- trade activity -->
        <div class="px-4 mt-5">
            <h3 class="text-base font-extrabold">Aktivitas perdagangan</h3>
            <p class="text-[11px] text-muted mt-0.5">Volume dan pesanan kolektif yang berjalan di ekosistem.</p>
            <div class="grid grid-cols-2 gap-2.5 mt-3">
                <div class="bg-white rounded-3xl border border-line p-4">
                    <p class="text-2xl font-extrabold tabular-nums">{{ $totalOrders }}</p>
                    <p class="text-[10px] text-muted font-semibold mt-1">Pesanan kolektif</p>
                </div>
                <div class="bg-white rounded-3xl border border-line p-4">
                    <p class="text-2xl font-extrabold tabular-nums">{{ number_format($totalCollectiveVolume, 0, ',', '.') }}</p>
                    <p class="text-[10px] text-muted font-semibold mt-1">Total volume (unit)</p>
                </div>
            </div>
        </div>

        <!-- chart -->
        <div class="px-4 mt-5">
            <h3 class="text-base font-extrabold">Tren Ekosistem</h3>
            <div class="bg-white rounded-3xl border border-line p-4 mt-3">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold">Pesanan kolektif / minggu</p>
                </div>
                <div class="flex items-end justify-between gap-1.5 h-28 mt-4 px-1">
                    @foreach ($orderTrend as $index => $trend)
                        <div class="flex-1 rounded-t-lg {{ $index === $orderTrend->count() - 1 ? 'bg-brass' : 'bg-forest/60' }}" style="height: {{ max(4, (int) round($trend['count'] / $orderTrendMax * 100)) }}%"></div>
                    @endforeach
                </div>
                <div class="flex justify-between text-[9px] text-muted font-semibold mt-1.5 px-1">
                    @foreach ($orderTrend as $trend)<span>{{ $trend['label'] }}</span>@endforeach
                </div>
            </div>
        </div>

        <!-- recent activity -->
        <div class="px-4 mt-5">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-extrabold">Aktivitas Terbaru</h3>
                <button class="text-xs font-bold text-brass">Lihat semua</button>
            </div>
            <div class="mt-3 space-y-2.5">
                <div class="bg-white rounded-3xl border border-line p-3.5 flex gap-3">
                    <span class="w-10 h-10 rounded-2xl bg-forest/10 text-forest flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-4h6v4"/></svg>
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-bold leading-snug">Distributor baru bergabung</p>
                        <p class="text-[11px] text-muted mt-0.5">{{ $recentDistributor?->company_name ?? 'Belum ada distributor' }}{{ $recentDistributor?->city ? ' • '.$recentDistributor->city : '' }}</p>
                    </div>
                    <span class="text-[10px] text-muted shrink-0">{{ optional($recentDistributor?->created_at)->diffForHumans() ?? '-' }}</span>
                </div>
                <div class="bg-white rounded-3xl border border-line p-3.5 flex gap-3">
                    <span class="w-10 h-10 rounded-2xl bg-brass-soft text-brass flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5v14m-7-7h14"/></svg>
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-bold leading-snug">{{ $pendingProposals }} usulan produk menunggu review</p>
                        <p class="text-[11px] text-muted mt-0.5">2 dari UD Berkah Jaya &bull; 3 lainnya</p>
                    </div>
                    <span class="text-[10px] text-muted shrink-0">1 jam</span>
                </div>
                <div class="bg-white rounded-3xl border border-line p-3.5 flex gap-3">
                    <span class="w-10 h-10 rounded-2xl bg-forest/10 text-forest flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 20h18M6 16v-5m6 5V8m6 8v-9"/></svg>
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-bold leading-snug">SPK terakhir dihitung</p>
                        <p class="text-[11px] text-muted mt-0.5">{{ $latestSpk ? 'Rekomendasi diperbarui' : 'Belum ada perhitungan' }}</p>
                    </div>
                    <span class="text-[10px] text-muted shrink-0">{{ optional($latestSpk?->updated_at)->diffForHumans() ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- manage ecosystem -->
        <div class="px-4 mt-5 pb-6">
            <h3 class="text-base font-extrabold">Kelola Ekosistem</h3>
            <div class="grid grid-cols-2 gap-2.5 mt-3">
                <a href="{{ route('admin.categories.index') }}" class="bg-white rounded-3xl border border-line p-4 text-left">
                    <span class="w-10 h-10 rounded-2xl bg-forest/10 text-forest flex items-center justify-center">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7a2 2 0 0 1 2-2h4l2 2h6a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7Z"/></svg>
                    </span>
                    <p class="text-[13px] font-bold mt-2.5">Master Kategori</p>
                    <p class="text-[10px] text-muted mt-0.5 leading-snug">Kategori produk baku &amp; standar mutu.</p>
                </a>
                <a href="{{ route('admin.products.index') }}" class="bg-white rounded-3xl border border-line p-4 text-left">
                    <span class="w-10 h-10 rounded-2xl bg-forest/10 text-forest flex items-center justify-center">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5M12 22V12"/></svg>
                    </span>
                    <p class="text-[13px] font-bold mt-2.5">Master Produk Baku</p>
                    <p class="text-[10px] text-muted mt-0.5 leading-snug">Produk acuan &amp; harga benchmark.</p>
                </a>
                <a href="{{ route('admin.proposals.index') }}" class="bg-white rounded-3xl border-2 border-brass/40 p-4 text-left relative">
                    <span class="absolute top-3 right-3 bg-brass text-white text-[9px] font-bold px-2 py-0.5 rounded-full">{{ $pendingProposals }} baru</span>
                    <span class="w-10 h-10 rounded-2xl bg-brass-soft text-brass flex items-center justify-center">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5v14m-7-7h14"/></svg>
                    </span>
                    <p class="text-[13px] font-bold mt-2.5">Usulan Produk</p>
                    <p class="text-[10px] text-muted mt-0.5 leading-snug">Review produk custom distributor.</p>
                </a>
                <a href="{{ route('admin.spk.index') }}" class="rounded-3xl bg-forest p-4 text-left text-white">
                    <span class="w-10 h-10 rounded-2xl bg-white/15 border border-white/25 text-white flex items-center justify-center">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 20h18M6 16v-5m6 5V8m6 8v-9"/></svg>
                    </span>
                    <p class="text-[13px] font-bold mt-2.5">Kriteria SPK (AHP)</p>
                    <p class="text-[10px] text-white/70 mt-0.5 leading-snug">Bobot &amp; uji konsistensi.</p>
                </a>
            </div>
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
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-forest text-white">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m2.25 12 8.95-8.95a1.13 1.13 0 0 1 1.6 0l8.95 8.95M4.5 9.75V19.5a.75.75 0 0 0 .75.75h13.5a.75.75 0 0 0 .75-.75V9.75"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
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
                        <h1 class="text-lg font-extrabold leading-tight">Dashboard Administrator</h1>
                        <p class="text-xs text-muted">{{ now()->translatedFormat('l, d F Y') }}</p>
                    </div>
                    <div class="ml-auto flex items-center gap-3">
                        <div class="hidden lg:flex items-center gap-2 bg-white border border-line rounded-2xl pl-4 pr-2 py-2 w-72">
                            <svg class="w-4 h-4 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21 21-5.2-5.2m0 0A7.5 7.5 0 1 0 5.2 5.2a7.5 7.5 0 0 0 10.6 10.6Z"/></svg>
                            <input class="flex-1 bg-transparent text-sm outline-none placeholder:text-muted/70" placeholder="Cari UMKM, distributor...">
                        </div>
                        <x-notification-bell />
                    </div>
                </div>
            </header>

            <main class="px-8 py-6 max-w-6xl">
                <!-- hero -->
                <section class="relative overflow-hidden rounded-[2rem] bg-gradient-to-r from-forest-deep via-forest to-forest-dark text-white p-8">
                    <div class="absolute -right-16 -top-20 w-72 h-72 rounded-full bg-white/10"></div>
                    <div class="absolute right-40 -bottom-24 w-56 h-56 rounded-full bg-brass/20"></div>
                    <div class="relative">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-brass-soft">Kontrol Pusat</p>
                        <h2 class="text-2xl font-extrabold mt-1">Dashboard Administrator</h2>
                        <p class="text-white/70 text-sm mt-2 max-w-xl">Pantau denyut ekosistem: siapa gabung, berapa hemat tercipta, dan seberapa sehat mesin SPK-nya.</p>
                    </div>
                </section>

                <!-- stats -->
                <section class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
                    <div class="bg-white rounded-3xl border-2 border-brass/40 p-6 relative overflow-hidden">
                        <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-brass-soft"></div>
                        <div class="flex items-center justify-between relative">
                            <p class="text-xs font-bold uppercase tracking-wider text-muted">Total Hemat Ekosistem</p>
                            <span class="w-9 h-9 rounded-xl bg-brass-soft text-brass flex items-center justify-center text-xs font-extrabold">Rp</span>
                        </div>
                        <p class="text-2xl font-extrabold text-brass tabular-nums mt-2 relative">Rp {{ number_format($totalSavingsSystem, 0, ',', '.') }}</p>
                        <p class="text-[11px] text-muted mt-1 relative">vs harga eceran</p>
                    </div>
                    <div class="bg-white rounded-3xl border border-line p-6">
                        <p class="text-3xl font-extrabold tabular-nums">{{ $totalUmkm }}</p>
                        <p class="text-xs text-muted font-semibold mt-1">UMKM terdaftar</p>
                        <span class="inline-block mt-3 bg-forest/10 text-forest text-[11px] font-bold px-2.5 py-1 rounded-full">+{{ $newUmkmThisWeek }} minggu ini</span>
                    </div>
                    <div class="bg-white rounded-3xl border border-line p-6">
                        <p class="text-3xl font-extrabold tabular-nums">{{ $totalDistributors }}</p>
                        <p class="text-xs text-muted font-semibold mt-1">Distributor rekanan</p>
                        <span class="inline-block mt-3 bg-forest/10 text-forest text-[11px] font-bold px-2.5 py-1 rounded-full">+{{ $newDistributorsThisMonth }} bulan ini</span>
                    </div>
                    <div class="bg-white rounded-3xl border border-line p-6">
                        <p class="text-3xl font-extrabold tabular-nums">{{ $totalCouriers }}</p>
                        <p class="text-xs text-muted font-semibold mt-1">Armada kurir</p>
                        <span class="inline-block mt-3 bg-success/10 text-success text-[11px] font-bold px-2.5 py-1 rounded-full">{{ $activeCouriers }} aktif</span>
                    </div>
                </section>

                <!-- charts row -->
                <section class="grid lg:grid-cols-3 gap-4 mt-6">
                    <div class="lg:col-span-2 bg-white rounded-[2rem] border border-line p-6 lg:p-8">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-extrabold tracking-tight">Tren pesanan kolektif</h2>
                                <p class="text-xs text-muted mt-0.5">7 hari terakhir</p>
                            </div>
                        </div>
                        <div class="flex items-end justify-between gap-2 h-44 mt-6 px-2">
                            @foreach ($orderTrend as $index => $trend)
                                <div class="flex-1 rounded-t-xl {{ $index === $orderTrend->count() - 1 ? 'bg-brass' : 'bg-forest/60' }}" style="height: {{ max(4, (int) round($trend['count'] / $orderTrendMax * 100)) }}%"></div>
                            @endforeach
                        </div>
                        <div class="flex justify-between text-[11px] text-muted font-semibold mt-2 px-2">
                            @foreach ($orderTrend as $trend)<span>{{ $trend['label'] }}</span>@endforeach
                        </div>
                    </div>
                    <div class="bg-white rounded-[2rem] border border-line p-6 lg:p-8">
                        <h2 class="text-lg font-extrabold tracking-tight">Aktivitas perdagangan</h2>
                        <p class="text-xs text-muted mt-0.5">Volume berjalan di ekosistem</p>
                        <div class="mt-6 space-y-4">
                            <div class="flex items-center gap-4">
                                <span class="min-w-12 h-12 px-2 rounded-2xl bg-forest/10 text-forest flex items-center justify-center shrink-0 font-extrabold text-sm">{{ $totalOrders }}</span>
                                <div><p class="text-sm font-bold">Pesanan kolektif</p><p class="text-[11px] text-muted">semua status</p></div>
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="min-w-12 h-12 px-2 rounded-2xl bg-brass-soft text-brass flex items-center justify-center shrink-0 font-extrabold text-xs">{{ number_format($totalCollectiveVolume, 0, ',', '.') }}</span>
                                <div><p class="text-sm font-bold">Total volume</p><p class="text-[11px] text-muted">{{ number_format($totalCollectiveVolume, 0, ',', '.') }} unit terkumpul</p></div>
                            </div>
                        </div>
                        <div class="mt-6 rounded-2xl bg-brass-soft border border-brass/30 p-4">
                            <p class="text-sm font-extrabold text-ink">{{ $pendingProposals }} usulan menunggu</p>
                            <p class="text-[11px] text-muted mt-0.5">Review produk custom distributor.</p>
                            <a href="{{ route('admin.proposals.index') }}" class="mt-3 w-full h-10 rounded-xl bg-brass text-white text-xs font-bold flex items-center justify-center">Review Usulan</a>
                        </div>
                    </div>
                </section>

                <!-- activity + manage -->
                <section class="grid lg:grid-cols-2 gap-4 mt-6">
                    <div class="bg-white rounded-[2rem] border border-line p-6 lg:p-8">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-extrabold tracking-tight">Aktivitas terbaru</h2>
                            <button class="text-sm font-bold text-brass">Lihat semua</button>
                        </div>
                        <div class="space-y-3">
                            <div class="flex gap-3 items-start">
                                <span class="w-10 h-10 rounded-2xl bg-forest/10 text-forest flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-4h6v4"/></svg>
                                </span>
                                <div class="flex-1 min-w-0"><p class="text-sm font-bold">Distributor baru bergabung</p><p class="text-xs text-muted mt-0.5">{{ $recentDistributor?->company_name ?? 'Belum ada distributor' }}{{ $recentDistributor?->city ? ' • '.$recentDistributor->city : '' }}</p></div>
                                <span class="text-[11px] text-muted shrink-0">{{ optional($recentDistributor?->created_at)->diffForHumans() ?? '-' }}</span>
                            </div>
                            <div class="flex gap-3 items-start">
                                <span class="w-10 h-10 rounded-2xl bg-brass-soft text-brass flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5v14m-7-7h14"/></svg>
                                </span>
                                <div class="flex-1 min-w-0"><p class="text-sm font-bold">{{ $pendingProposals }} usulan produk menunggu review</p><p class="text-xs text-muted mt-0.5">2 dari UD Berkah Jaya &bull; 3 lainnya</p></div>
                                <span class="text-[11px] text-muted shrink-0">1 jam</span>
                            </div>
                            <div class="flex gap-3 items-start">
                                <span class="w-10 h-10 rounded-2xl bg-forest/10 text-forest flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 20h18M6 16v-5m6 5V8m6 8v-9"/></svg>
                                </span>
                                <div class="flex-1 min-w-0"><p class="text-sm font-bold">SPK terakhir dihitung</p><p class="text-xs text-muted mt-0.5">{{ $latestSpk ? 'Rekomendasi diperbarui' : 'Belum ada perhitungan' }}</p></div>
                                <span class="text-[11px] text-muted shrink-0">{{ optional($latestSpk?->updated_at)->diffForHumans() ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h2 class="text-lg font-extrabold tracking-tight mb-4">Kelola ekosistem</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <a href="{{ route('admin.categories.index') }}" class="bg-white rounded-3xl border border-line p-5 text-left">
                                <span class="w-10 h-10 rounded-2xl bg-forest/10 text-forest flex items-center justify-center">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7a2 2 0 0 1 2-2h4l2 2h6a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7Z"/></svg>
                                </span>
                                <p class="text-sm font-bold mt-3">Master Kategori</p>
                                <p class="text-[11px] text-muted mt-0.5 leading-snug">Kategori produk baku &amp; standar mutu.</p>
                            </a>
                            <a href="{{ route('admin.products.index') }}" class="bg-white rounded-3xl border border-line p-5 text-left">
                                <span class="w-10 h-10 rounded-2xl bg-forest/10 text-forest flex items-center justify-center">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5M12 22V12"/></svg>
                                </span>
                                <p class="text-sm font-bold mt-3">Master Produk Baku</p>
                                <p class="text-[11px] text-muted mt-0.5 leading-snug">Produk acuan &amp; harga benchmark.</p>
                            </a>
                            <a href="{{ route('admin.proposals.index') }}" class="bg-white rounded-3xl border-2 border-brass/40 p-5 text-left relative">
                                <span class="absolute top-4 right-4 bg-brass text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $pendingProposals }} baru</span>
                                <span class="w-10 h-10 rounded-2xl bg-brass-soft text-brass flex items-center justify-center">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5v14m-7-7h14"/></svg>
                                </span>
                                <p class="text-sm font-bold mt-3">Usulan Produk</p>
                                <p class="text-[11px] text-muted mt-0.5 leading-snug">Review produk custom distributor.</p>
                            </a>
                            <a href="{{ route('admin.spk.index') }}" class="rounded-3xl bg-forest p-5 text-left text-white">
                                <span class="w-10 h-10 rounded-2xl bg-white/15 border border-white/25 text-white flex items-center justify-center">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 20h18M6 16v-5m6 5V8m6 8v-9"/></svg>
                                </span>
                                <p class="text-sm font-bold mt-3">Kriteria SPK (AHP)</p>
                                <p class="text-[11px] text-white/70 mt-0.5 leading-snug">Bobot &amp; uji konsistensi.</p>
                            </a>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>
</x-layouts.app>
