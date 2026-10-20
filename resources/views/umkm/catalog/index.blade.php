<x-layouts.app>
    <x-slot name="title">Katalog Bahan Baku</x-slot>

    @php
        $displayName = $umkm?->business_name ?? auth()->user()->name;
        $initials = strtoupper(substr(auth()->user()->name, 0, 2));
        $cartCount = app(\App\Services\CartService::class)->count();
        $categories = $products->getCollection()->pluck('category')->filter()->unique('id')->values();
    @endphp

    {{-- MOBILE --}}
    <div class="lg:hidden pb-24">
        <div class="px-4 pt-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="relative" x-data="{ menuOpen: false }">
                    <button type="button" @click="menuOpen = !menuOpen" class="w-11 h-11 rounded-2xl bg-forest text-white flex items-center justify-center font-extrabold text-sm cursor-pointer">{{ $initials }}</button>
                    <div x-show="menuOpen" @click.away="menuOpen = false" x-cloak class="absolute left-0 mt-2 w-48 bg-white border border-line rounded-2xl shadow-xl p-2 z-40 text-xs">
                        <a href="{{ route('profile.complete') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-ink hover:bg-cream font-medium">Profil Saya</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2 px-3 py-2.5 rounded-xl text-danger hover:bg-danger/10 font-bold transition text-left cursor-pointer">Keluar</button>
                        </form>
                    </div>
                </div>
                <div>
                    <p class="text-[11px] text-muted">Selamat pagi,</p>
                    <p class="text-[15px] font-extrabold leading-tight">{{ $displayName }}</p>
                </div>
            </div>
            <x-notification-bell size="sm" />
        </div>

        <div class="px-4 mt-4">
            <form method="GET" action="{{ route('umkm.catalog.index') }}" class="flex items-center gap-2 bg-white border border-line rounded-2xl pl-4 pr-2 py-2 shadow-sm">
                <svg class="w-5 h-5 text-muted shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21 21-5.2-5.2m0 0A7.5 7.5 0 1 0 5.2 5.2a7.5 7.5 0 0 0 10.6 10.6Z"/></svg>
                <input type="search" name="q" value="{{ $search }}" placeholder="Cari beras, minyak, gula..." class="flex-1 bg-transparent text-sm outline-none placeholder:text-muted/70">
                <button type="submit" class="h-9 px-4 rounded-xl bg-forest text-white text-xs font-bold">Cari</button>
            </form>
        </div>

        <div class="px-4 mt-4">
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-forest-deep via-forest to-forest-dark text-white p-5">
                <div class="absolute -right-8 -top-10 w-40 h-40 rounded-full bg-white/10"></div>
                <div class="absolute right-10 -bottom-12 w-32 h-32 rounded-full bg-brass/20"></div>
                <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-brass-soft relative">Kulakan Kolektif</p>
                <h2 class="text-xl font-extrabold mt-1 leading-snug relative">Hemat hingga 20%<br>dengan harga grosir</h2>
                <p class="text-xs text-white/75 mt-1.5 relative">Gabung kuota bareng UMKM lain di Jember</p>
                <a href="{{ route('umkm.catalog.index') }}" class="mt-3 inline-flex bg-white text-forest-dark text-xs font-bold px-4 py-2.5 rounded-xl relative">Lihat Katalog</a>
            </div>
            <div class="flex justify-center gap-1.5 mt-2.5">
                <span class="w-5 h-1.5 rounded-full bg-forest"></span>
                <span class="w-1.5 h-1.5 rounded-full bg-line"></span>
                <span class="w-1.5 h-1.5 rounded-full bg-line"></span>
            </div>
        </div>

        @if ($categories->isNotEmpty())
            <div class="mt-4">
                <div class="flex gap-4 overflow-x-auto no-scrollbar px-4 pb-1">
                    @foreach ($categories->take(5) as $category)
                        <a href="{{ route('umkm.catalog.index', ['q' => $category->name]) }}" class="flex flex-col items-center gap-1.5 shrink-0">
                            <span class="w-14 h-14 rounded-full bg-white border border-line flex items-center justify-center text-forest font-extrabold text-lg">{{ strtoupper(substr($category->name, 0, 1)) }}</span>
                            <span class="text-[11px] font-semibold text-ink">{{ $category->name }}</span>
                        </a>
                    @endforeach
                    <a href="{{ route('umkm.catalog.index') }}" class="flex flex-col items-center gap-1.5 shrink-0">
                        <span class="w-14 h-14 rounded-full bg-cream border border-line flex items-center justify-center text-forest">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>
                        </span>
                        <span class="text-[11px] font-semibold text-ink">Semua</span>
                    </a>
                </div>
            </div>
        @endif

        <div class="px-4 mt-5 flex items-center justify-between">
            <h3 class="text-base font-extrabold text-ink">Produk Populer</h3>
            <a href="{{ route('umkm.catalog.index') }}" class="text-xs font-bold text-brass">Lihat semua</a>
        </div>

        @if ($products->isNotEmpty())
            <div class="px-4 mt-3 grid grid-cols-2 gap-3 pb-6">
                @foreach ($products as $prod)
                    @php
                        $available = $prod->supplierProducts->where('is_available', true);
                        $availablePrices = $available->flatMap(fn ($sp) => collect([(float) $sp->base_retail_price])->merge($sp->priceTiers->pluck('unit_price')->map(fn ($p) => (float) $p)));
                        $minPrice = $availablePrices->min() ?? (float) $prod->benchmark_retail_price;
                        $discountPct = (float) $prod->benchmark_retail_price > 0 && $minPrice < (float) $prod->benchmark_retail_price
                            ? (int) round((1 - $minPrice / (float) $prod->benchmark_retail_price) * 100)
                            : null;
                        $tone = ['bg-forest/10 text-forest/30', 'bg-brass-soft text-brass/30', 'bg-white border-b border-line text-muted/25'][$loop->index % 3];
                    @endphp
                    <a href="{{ route('umkm.catalog.show', $prod) }}" class="bg-white rounded-3xl border border-line overflow-hidden shadow-sm flex flex-col">
                        <div class="relative aspect-square flex items-center justify-center {{ $tone }}">
                            <span class="text-5xl font-extrabold">{{ strtoupper(substr($prod->name, 0, 1)) }}</span>
                            @if ($discountPct !== null && $discountPct > 0)
                                <span class="absolute top-2 left-2 bg-danger text-white text-[10px] font-bold px-2 py-0.5 rounded-full">-{{ $discountPct }}%</span>
                            @endif
                            <span class="absolute bottom-2 right-2 w-9 h-9 rounded-full bg-forest text-white flex items-center justify-center shadow-md">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            </span>
                        </div>
                        <div class="p-3 flex flex-col flex-1">
                            <p class="text-[13px] font-bold text-ink leading-tight">{{ $prod->name }}</p>
                            <p class="text-[11px] text-muted mt-0.5">{{ $prod->unit }}</p>
                            <div class="mt-auto pt-1">
                                <p class="text-[11px] text-muted">mulai dari</p>
                                <p class="text-sm font-extrabold text-forest tnum whitespace-nowrap">Rp {{ number_format($minPrice, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </a>
                @endforeach
                <a href="{{ route('umkm.catalog.index') }}" class="bg-white rounded-3xl border border-dashed border-line flex flex-col items-center justify-center p-6 min-h-[220px] text-center">
                    <span class="w-12 h-12 rounded-full bg-cream border border-line flex items-center justify-center text-brass">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>
                    </span>
                    <p class="text-xs font-bold text-ink mt-3">{{ number_format($products->total(), 0, ',', '.') }} produk<br>sembako tersedia</p>
                    <p class="text-[11px] text-brass font-bold mt-1">Jelajahi</p>
                </a>
            </div>
            <div class="px-4 pb-6">{{ $products->links() }}</div>
        @else
            <div class="px-4 mt-3 pb-6">
                <div class="bg-white rounded-3xl border border-line p-8 text-center">
                    <h3 class="font-extrabold text-ink">Produk tidak ditemukan</h3>
                    <p class="text-xs text-muted mt-1">Coba kata kunci lain atau lihat semua produk.</p>
                    <a href="{{ route('umkm.catalog.index') }}" class="btn-primary text-xs font-bold px-4 py-2.5 mt-4 inline-flex">Tampilkan Semua</a>
                </div>
            </div>
        @endif
    </div>

    {{-- DESKTOP --}}
    <div class="hidden lg:flex min-h-screen">
        <aside class="w-64 shrink-0 bg-white border-r border-line flex flex-col sticky top-0 h-screen">
            <div class="flex items-center gap-2.5 px-6 h-[72px] border-b border-line">
                <span class="w-10 h-10 rounded-2xl bg-forest flex items-center justify-center text-white">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.25 7.5l-.63 10.63a2.25 2.25 0 0 1-2.25 2.12H6.63a2.25 2.25 0 0 1-2.25-2.12L3.75 7.5M10 11.25h4M3.38 7.5h17.25c.62 0 1.12-.5 1.12-1.12v-1.5C21.75 4.26 21.25 3.75 20.63 3.75H3.38c-.62 0-1.13.5-1.13 1.12v1.5c0 .63.5 1.13 1.12 1.13Z"/></svg>
                </span>
                <span class="text-xl font-extrabold tracking-tight">Kulaan</span>
            </div>
            <x-umkm.sidebar-nav active="catalog" />
            <div class="p-4 border-t border-line">
                <a href="{{ route('profile.complete') }}" class="flex items-center gap-3 px-2">
                    <span class="w-10 h-10 rounded-2xl bg-forest text-white flex items-center justify-center font-extrabold text-xs">{{ $initials }}</span>
                    <div class="min-w-0">
                        <p class="text-sm font-bold truncate">{{ $displayName }}</p>
                        <p class="text-[11px] text-muted">{{ $umkm?->city ?? 'Sidoarjo' }}, {{ $umkm?->province ?? 'Jawa Timur' }}</p>
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

        <div class="flex-1 min-w-0">
            <header class="bg-white/70 backdrop-blur border-b border-line sticky top-0 z-10">
                <div class="px-8 h-[72px] flex items-center gap-4">
                    <div>
                        <h1 class="text-lg font-extrabold leading-tight">Katalog Bahan Baku</h1>
                        <p class="text-xs text-muted">{{ now()->translatedFormat('l, d F Y') }}</p>
                    </div>
                    <div class="ml-auto flex items-center gap-3">
                        <form method="GET" action="{{ route('umkm.catalog.index') }}" class="hidden lg:flex items-center gap-2 bg-white border border-line rounded-2xl pl-4 pr-2 py-2 w-72">
                            <svg class="w-4 h-4 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21 21-5.2-5.2m0 0A7.5 7.5 0 1 0 5.2 5.2a7.5 7.5 0 0 0 10.6 10.6Z"/></svg>
                            <input type="search" name="q" value="{{ $search }}" class="flex-1 bg-transparent text-sm outline-none placeholder:text-muted/70" placeholder="Cari beras, minyak goreng, gula...">
                            <button type="submit" class="h-9 px-4 rounded-xl bg-forest text-white text-xs font-bold">Cari</button>
                        </form>
                        <x-notification-bell />
                    </div>
                </div>
            </header>

            <main class="px-8 py-6 max-w-6xl">
                <section class="relative overflow-hidden rounded-[2rem] bg-gradient-to-r from-forest-deep via-forest to-forest-dark text-white p-8 lg:p-12">
                    <div class="absolute -right-16 -top-20 w-72 h-72 rounded-full bg-white/10"></div>
                    <div class="absolute right-32 -bottom-24 w-56 h-56 rounded-full bg-brass/20"></div>
                    <div class="relative max-w-2xl">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-brass-soft">Kulakan Kolektif UMKM</p>
                        <h2 class="text-3xl lg:text-[2.75rem] font-extrabold leading-tight mt-2">Kulakan bareng,<br>dapat harga grosir.</h2>
                        <p class="text-white/75 mt-3 max-w-lg">Gabung kuota pembelian bersama ribuan UMKM. Makin banyak yang ikut, makin murah harganya &mdash; sistem rekomendasi SPK bantu pilihkan distributor terbaik.</p>
                        <div class="flex flex-wrap gap-3 mt-6">
                            <a href="{{ route('umkm.catalog.index') }}" class="bg-white text-forest-dark font-bold px-6 py-3 rounded-2xl text-sm">Mulai Belanja</a>
                            <a href="{{ route('home') }}" class="border border-white/30 text-white font-bold px-6 py-3 rounded-2xl text-sm">Cara Kerja</a>
                        </div>
                        <div class="flex flex-wrap gap-x-8 gap-y-4 mt-8">
                            <div><p class="text-2xl font-extrabold">{{ number_format($stats['total_umkm'], 0, ',', '.') }}</p><p class="text-xs text-white/65">UMKM bergabung</p></div>
                            <div><p class="text-2xl font-extrabold">{{ number_format($stats['total_distributor'], 0, ',', '.') }}</p><p class="text-xs text-white/65">Distributor mitra</p></div>
                            <div><p class="text-2xl font-extrabold">Rp {{ number_format($stats['total_savings'], 0, ',', '.') }}</p><p class="text-xs text-white/65">Total dihemat</p></div>
                        </div>
                    </div>
                </section>

                @if ($categories->isNotEmpty())
                    <section class="mt-8 grid grid-cols-3 sm:grid-cols-6 gap-3">
                        @foreach ($categories->take(5) as $category)
                            <a href="{{ route('umkm.catalog.index', ['q' => $category->name]) }}" class="bg-white border border-line rounded-3xl p-4 flex flex-col items-center gap-2 hover:border-forest/40 transition">
                                <span class="w-12 h-12 rounded-2xl bg-forest/10 text-forest font-extrabold text-lg flex items-center justify-center">{{ strtoupper(substr($category->name, 0, 1)) }}</span>
                                <span class="text-xs font-bold">{{ $category->name }}</span>
                            </a>
                        @endforeach
                        <a href="{{ route('umkm.catalog.index') }}" class="bg-white border border-line rounded-3xl p-4 flex flex-col items-center gap-2 hover:border-forest/40 transition">
                            <span class="w-12 h-12 rounded-2xl bg-forest text-white flex items-center justify-center">
                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                            </span>
                            <span class="text-xs font-bold">Semua</span>
                        </a>
                    </section>
                @endif

                <section class="mt-10">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-extrabold">Jelajahi Produk</h2>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('umkm.catalog.index') }}" class="text-xs font-bold px-4 py-2 rounded-full bg-forest text-white">Semua</a>
                            @foreach ($categories->take(3) as $category)
                                <a href="{{ route('umkm.catalog.index', ['q' => $category->name]) }}" class="text-xs font-bold px-4 py-2 rounded-full bg-white border border-line text-muted hover:text-ink transition">{{ $category->name }}</a>
                            @endforeach
                        </div>
                    </div>

                    @if ($products->isNotEmpty())
                        <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 mt-5">
                            @foreach ($products as $prod)
                                @php
                                    $available = $prod->supplierProducts->where('is_available', true);
                                    $availablePrices = $available->flatMap(fn ($sp) => collect([(float) $sp->base_retail_price])->merge($sp->priceTiers->pluck('unit_price')->map(fn ($p) => (float) $p)));
                                    $minPrice = $availablePrices->min() ?? (float) $prod->benchmark_retail_price;
                                    $topRating = $topRatingByProduct[$prod->id] ?? null;
                                    $distributorCount = $available->pluck('distributor_id')->unique()->count();
                                    $discountPct = (float) $prod->benchmark_retail_price > 0 && $minPrice < (float) $prod->benchmark_retail_price
                                        ? (int) round((1 - $minPrice / (float) $prod->benchmark_retail_price) * 100)
                                        : null;
                                    $tone = ['bg-forest/10 text-forest/25', 'bg-brass-soft text-brass/25', 'bg-white border-b border-line text-muted/20'][$loop->index % 3];
                                @endphp
                                <a href="{{ route('umkm.catalog.show', $prod) }}" class="bg-white rounded-3xl border border-line overflow-hidden shadow-sm hover:shadow-md transition flex flex-col">
                                    <div class="relative aspect-[4/3] flex items-center justify-center {{ $tone }}">
                                        <span class="text-6xl font-extrabold">{{ strtoupper(substr($prod->name, 0, 1)) }}</span>
                                        @if ($discountPct !== null && $discountPct > 0)
                                            <span class="absolute top-3 left-3 bg-danger text-white text-[11px] font-bold px-2.5 py-1 rounded-full">-{{ $discountPct }}%</span>
                                        @endif
                                        <span class="absolute top-3 right-3 w-9 h-9 rounded-full bg-white/90 border border-line flex items-center justify-center text-muted">
                                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 8.25c0-2.49-2.1-4.5-4.69-4.5-1.93 0-3.6 1.13-4.31 2.73-.71-1.6-2.38-2.73-4.31-2.73C5.1 3.75 3 5.77 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                                        </span>
                                    </div>
                                    <div class="p-4 flex flex-col flex-1">
                                        <div class="flex items-center gap-1 text-xs font-bold {{ $topRating && $topRating['rating'] !== null ? 'text-brass' : 'text-muted' }} min-w-0">
                                            @if ($topRating && $topRating['rating'] !== null)
                                                <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                                <span class="shrink-0">{{ number_format($topRating['rating'], 1) }}</span>
                                                @if (! empty($topRating['name']))
                                                    <span class="text-muted font-medium truncate">({{ $topRating['name'] }})</span>
                                                @endif
                                            @else
                                                <span class="truncate">Belum ada rating distributor</span>
                                            @endif
                                        </div>
                                        <p class="font-bold mt-1 leading-snug">{{ $prod->name }}</p>
                                        <p class="text-xs text-muted">{{ $prod->unit }} &middot; {{ $distributorCount }} distributor</p>
                                        <div class="flex items-end justify-between gap-2 mt-auto pt-3">
                                            <div class="min-w-0">
                                                <p class="text-[11px] text-muted">mulai dari</p>
                                                <p class="font-extrabold text-forest tnum whitespace-nowrap">Rp {{ number_format($minPrice, 0, ',', '.') }}</p>
                                            </div>
                                            <span class="h-10 px-4 rounded-xl bg-forest text-white text-xs font-bold flex items-center shrink-0">Lihat</span>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                        <div class="pt-6">{{ $products->links() }}</div>
                    @else
                        <div class="bg-white rounded-3xl border border-line p-10 text-center max-w-md mx-auto my-12">
                            <h3 class="font-extrabold text-ink text-lg">Produk tidak ditemukan</h3>
                            <p class="text-xs text-muted mt-1">Coba kata kunci lain atau lihat semua produk.</p>
                            <a href="{{ route('umkm.catalog.index') }}" class="btn-primary text-xs font-bold px-4 py-2.5 mt-4 inline-flex">Tampilkan Semua</a>
                        </div>
                    @endif
                </section>

                <section class="mt-12 grid sm:grid-cols-3 gap-4">
                    <div class="bg-white border border-line rounded-3xl p-6 flex gap-4">
                        <span class="w-12 h-12 rounded-2xl bg-forest/10 text-forest flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 12.75 11.25 15 15 9.75m-3-7.04A11.96 11.96 0 0 1 3.6 6 12 12 0 0 0 3 9.75c0 5.59 3.82 10.29 9 11.62 5.18-1.33 9-6.03 9-11.62 0-1.31-.21-2.57-.6-3.75h-.15c-3.2 0-6.1-1.25-8.25-3.29Z"/></svg>
                        </span>
                        <div><p class="font-bold">Rekomendasi SPK</p><p class="text-sm text-muted mt-1">Distributor terbaik dipilih dengan AHP, TOPSIS &amp; SAW &mdash; transparan.</p></div>
                    </div>
                    <div class="bg-white border border-line rounded-3xl p-6 flex gap-4">
                        <span class="w-12 h-12 rounded-2xl bg-brass-soft text-brass flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.38a1.13 1.13 0 0 1-1.13-1.13V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.13c.62 0 1.13-.5 1.09-1.12a18 18 0 0 0-3.21-9.19 2.06 2.06 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.18v-.96c0-.57-.42-1.05-.99-1.11a48.55 48.55 0 0 0-10.02 0 1.11 1.11 0 0 0-.99 1.11v7.63m12-6.68v6.68m0 4.5v-4.5m0 0h-12"/></svg>
                        </span>
                        <div><p class="font-bold">Diantar Terlacak</p><p class="text-sm text-muted mt-1">Posisi kurir terpantau live di peta sampai ke tokomu.</p></div>
                    </div>
                    <div class="bg-white border border-line rounded-3xl p-6 flex gap-4">
                        <span class="w-12 h-12 rounded-2xl bg-success/10 text-success flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2.25 18.75a60.68 60.68 0 0 1 2.25-2.25 60.69 60.69 0 0 1 2.25-2.25m0 0a60.69 60.69 0 0 1 2.25-2.25m-4.5 4.5a60.68 60.68 0 0 1 4.5-4.5m4.5 4.5a60.68 60.68 0 0 1-4.5-4.5m4.5 4.5a60.69 60.69 0 0 1-4.5-4.5m12 6.75a60.68 60.68 0 0 1-2.25-2.25m2.25 2.25a60.69 60.69 0 0 1-2.25-2.25m0 0a60.69 60.69 0 0 1-2.25-2.25m4.5 4.5a60.68 60.68 0 0 1-4.5-4.5m4.5 4.5a60.69 60.69 0 0 1-4.5-4.5"/></svg>
                        </span>
                        <div><p class="font-bold">Pembayaran Aman</p><p class="text-sm text-muted mt-1">Via Midtrans: transfer bank, QRIS, e-wallet, kartu.</p></div>
                    </div>
                </section>
            </main>

            <footer class="border-t border-line mt-4">
                <div class="px-8 py-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-sm font-extrabold">Kulaan <span class="text-muted font-medium">&mdash; Kulakan kolektif UMKM Indonesia</span></p>
                    <p class="text-xs text-muted">GTNIC 2026 &middot; Web Development</p>
                </div>
            </footer>
        </div>
    </div>

    <x-umkm.mobile-tab active="catalog" />
</x-layouts.app>
