<x-layouts.app>
    <x-slot name="title">Dashboard Distributor</x-slot>

    @php
        $orderAction = function ($order) {
            $status = $order->status->value;

            $pill = match ($status) {
                'PAID' => ['Perlu diproses', 'bg-brass-soft text-brass'],
                'CONFIRMED', 'PREPARING' => ['Perlu diproses', 'bg-brass-soft text-brass'],
                'READY_TO_SHIP' => ['Siap kirim', 'bg-forest/10 text-forest'],
                'ASSIGNED_TO_COURIER' => ['Kurir ditugaskan', 'bg-brass/15 text-brass'],
                'IN_TRANSIT' => ['Dalam perjalanan', 'bg-brass/15 text-brass'],
                'DELIVERED', 'COMPLETED' => ['Selesai', 'bg-success/10 text-success'],
                'CANCELLED', 'EXPIRED' => ['Dibatalkan', 'bg-danger/10 text-danger'],
                default => [$order->status->label(), 'bg-brass-soft text-brass'],
            };

            $action = match ($status) {
                'PAID' => 'Konfirmasi',
                'CONFIRMED' => 'Siapkan barang',
                'PREPARING' => 'Siap kirim',
                'READY_TO_SHIP' => 'Tugaskan kurir',
                default => null,
            };

            return ['pill' => $pill, 'action' => $action];
        };
    @endphp

    <div class="lg:hidden bg-cream min-h-screen">
        <div class="pb-24">
            <div class="px-4 pt-4 flex items-center justify-between">
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
                        <p class="text-[11px] text-muted">Selamat pagi,</p>
                        <p class="text-[15px] font-extrabold leading-tight">{{ $distributor?->company_name ?? $user->name }}</p>
                    </div>
                </div>
                <x-notification-bell />
            </div>

            <div class="px-4 mt-4">
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-forest-deep via-forest to-forest-dark text-white p-5">
                    <div class="absolute -right-10 -top-12 w-44 h-44 rounded-full bg-white/10"></div>
                    <div class="absolute right-16 -bottom-14 w-36 h-36 rounded-full bg-brass/20"></div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-brass-soft">Distributor Ekosistem</p>
                    <h2 class="text-lg font-extrabold mt-1 leading-snug">{{ $distributor?->company_name ?? $user->name }}</h2>
                    <p class="text-[11px] text-white/70 mt-1.5 leading-relaxed">Area operasional: {{ $distributor?->city ?? 'Sidoarjo' }} &bull; Rata-rata kirim {{ $distributor?->delivery_lead_time_days ?? 2 }} hari. Kelola katalog, konfirmasi pesanan, dan tugaskan kurirmu dari sini.</p>
                    <div class="flex gap-2.5 mt-4 relative">
                        <a href="{{ route('distributor.products.index') }}" class="flex-1 h-11 rounded-xl bg-white text-forest-dark text-xs font-bold flex items-center justify-center">Kelola Katalog</a>
                        <a href="{{ route('distributor.orders.index') }}" class="flex-1 h-11 rounded-xl bg-white/10 border border-white/25 text-white text-xs font-bold flex items-center justify-center">Lihat Pesanan</a>
                    </div>
                </div>
            </div>

            <div class="px-4 mt-4">
                <div class="bg-white rounded-3xl border-2 border-brass/40 p-5 relative overflow-hidden">
                    <div class="absolute -right-6 -top-6 w-28 h-28 rounded-full bg-brass-soft"></div>
                    <div class="flex items-center justify-between relative">
                        <p class="text-xs font-bold uppercase tracking-wider text-muted">Total Penjualan</p>
                        <span class="w-9 h-9 rounded-xl bg-brass-soft text-brass flex items-center justify-center text-xs font-extrabold">Rp</span>
                    </div>
                    <p class="text-[2rem] font-extrabold text-brass tabular-nums mt-2 relative">Rp {{ number_format($totalSalesAmount, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-muted mt-1 relative">{{ $totalOrdersCount }} pesanan tercatat</p>
                </div>
            </div>

            <div class="px-4 mt-3 grid grid-cols-3 gap-2.5">
                <div class="bg-white rounded-3xl border border-line p-3.5">
                    <p class="text-2xl font-extrabold">{{ $catalogCount }}</p>
                    <p class="text-[10px] text-muted font-semibold mt-1 leading-tight">Produk aktif di katalog</p>
                    <span class="inline-block mt-2 bg-forest/10 text-forest text-[10px] font-bold px-2 py-0.5 rounded-full">+{{ $newProductsThisMonth }} bulan ini</span>
                </div>
                <div class="bg-white rounded-3xl border-2 border-brass/40 p-3.5">
                    <p class="text-2xl font-extrabold text-brass">{{ $pendingOrdersCount }}</p>
                    <p class="text-[10px] text-muted font-semibold mt-1 leading-tight">Pesanan perlu diproses</p>
                    <span class="inline-block mt-2 bg-brass-soft text-brass text-[10px] font-bold px-2 py-0.5 rounded-full">Perlu aksi</span>
                </div>
                <div class="bg-white rounded-3xl border border-line p-3.5">
                    <p class="text-2xl font-extrabold">{{ number_format($distributor?->rating ?? 0, 1) }}<span class="text-sm text-muted font-bold">/5</span></p>
                    <p class="text-[10px] text-muted font-semibold mt-1 leading-tight">Rating dari UMKM</p>
                    <span class="inline-block mt-2 bg-brass-soft text-brass text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $reviewCount }} ulasan</span>
                </div>
            </div>

            <div class="px-4 mt-5">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-extrabold">Pesanan kolektif masuk</h3>
                    <a href="{{ route('distributor.orders.index') }}" class="text-xs font-bold text-brass">Lihat Semua</a>
                </div>
                <p class="text-[11px] text-muted mt-0.5">Pesanan terbaru dari grup pilihan UMKM</p>
                <div class="mt-3 space-y-2.5">
                    @forelse ($orders->take(5) as $order)
                        @php
                            $oa = $orderAction($order);
                        @endphp
                        <div class="bg-white rounded-3xl {{ $oa['action'] ? 'border-2 border-brass/40' : 'border border-line' }} p-3.5">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="font-mono text-[13px] font-bold">{{ $order->order_number }}</p>
                                    <p class="text-[11px] text-muted mt-0.5 truncate">{{ number_format($order->total_quantity, 0) }} unit &bull; Rp {{ number_format($order->total_amount, 0, ',', '.') }} &bull; {{ $order->items->map(fn ($i) => $i->umkm?->business_name)->filter()->unique()->join(', ') }}</p>
                                </div>
                                <span class="shrink-0 {{ $oa['pill'][1] }} text-[10px] font-bold px-2 py-1 rounded-full">{{ $oa['pill'][0] }}</span>
                            </div>
                            <div class="flex items-center justify-between mt-2.5">
                                <p class="text-sm font-extrabold tabular-nums">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                                @if ($oa['action'])
                                    <a href="{{ route('distributor.orders.index') }}" class="text-xs font-bold text-forest bg-forest/10 px-3 py-2 rounded-xl cursor-pointer">{{ $oa['action'] }}</a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-muted py-4">Belum ada pesanan kolektif masuk.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <x-mobile-tab role="distributor" active="dashboard" :badges="['orders' => $pendingOrdersCount]" />
    </div>

    <div class="hidden lg:flex min-h-screen">
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
                <a href="{{ route('distributor.products.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5M12 22V12"/></svg>
                    Katalog Produk
                </a>
                <a href="{{ route('distributor.orders.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 17h6m-5 4h4M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/><path d="M9 8h6M9 12h4"/></svg>
                    Pesanan
                    @if ($pendingOrdersCount > 0)
                        <span class="ml-auto bg-brass text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $pendingOrdersCount }}</span>
                    @endif
                </a>
            </nav>
            <div class="p-4 border-t border-line">
                <a href="{{ route('profile.complete') }}" class="flex items-center gap-3 px-2">
                    <span class="w-10 h-10 rounded-2xl bg-forest text-white flex items-center justify-center font-extrabold text-xs">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                    <div class="min-w-0">
                        <p class="text-sm font-bold truncate">{{ $distributor?->company_name ?? $user->name }}</p>
                        <p class="text-[11px] text-muted">{{ $distributor?->city ?? 'Sidoarjo' }}, {{ $distributor?->province ?? 'Jawa Timur' }}</p>
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
                        <h1 class="text-lg font-extrabold leading-tight">Selamat pagi, {{ $distributor?->company_name ?? $user->name }}</h1>
                        <p class="text-xs text-muted">{{ now()->translatedFormat('l, d F Y') }}</p>
                    </div>
                    <div class="ml-auto flex items-center gap-3">
                        <div class="hidden lg:flex items-center gap-2 bg-white border border-line rounded-2xl pl-4 pr-2 py-2 w-72">
                            <svg class="w-4 h-4 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21 21-5.2-5.2m0 0A7.5 7.5 0 1 0 5.2 5.2a7.5 7.5 0 0 0 10.6 10.6Z"/></svg>
                            <input class="flex-1 bg-transparent text-sm outline-none placeholder:text-muted/70" placeholder="Cari pesanan, produk...">
                        </div>
                        <x-notification-bell />
                    </div>
                </div>
            </header>

            <main class="px-8 py-6 max-w-6xl">
                <section class="relative overflow-hidden rounded-[2rem] bg-gradient-to-r from-forest-deep via-forest to-forest-dark text-white p-8">
                    <div class="absolute -right-16 -top-20 w-72 h-72 rounded-full bg-white/10"></div>
                    <div class="absolute right-40 -bottom-24 w-56 h-56 rounded-full bg-brass/20"></div>
                    <div class="relative flex items-center justify-between gap-6 flex-wrap">
                        <div class="max-w-xl">
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-brass-soft">Distributor Ekosistem</p>
                            <h2 class="text-2xl font-extrabold mt-1">{{ $distributor?->company_name ?? $user->name }}</h2>
                            <p class="text-white/70 text-sm mt-2">Area operasional: {{ $distributor?->city ?? 'Sidoarjo' }} &bull; Rata-rata kirim {{ $distributor?->delivery_lead_time_days ?? 2 }} hari. Kelola katalog, konfirmasi pesanan, dan tugaskan kurirmu dari sini.</p>
                        </div>
                        <div class="flex gap-3">
                            <a href="{{ route('distributor.products.index') }}" class="h-12 px-6 rounded-2xl bg-white text-forest-dark text-sm font-bold flex items-center justify-center">Kelola Katalog</a>
                            <a href="{{ route('distributor.orders.index') }}" class="h-12 px-6 rounded-2xl bg-white/10 border border-white/25 text-white text-sm font-bold flex items-center justify-center">Lihat Pesanan</a>
                        </div>
                    </div>
                </section>

                <section class="grid grid-cols-2 xl:grid-cols-4 gap-4 mt-6">
                    <div class="bg-white rounded-3xl border-2 border-brass/40 p-6 relative overflow-hidden">
                        <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-brass-soft"></div>
                        <div class="flex items-center justify-between relative">
                            <p class="text-xs font-bold uppercase tracking-wider text-muted">Total Penjualan</p>
                            <span class="w-9 h-9 rounded-xl bg-brass-soft text-brass flex items-center justify-center text-xs font-extrabold">Rp</span>
                        </div>
                        <p class="text-3xl font-extrabold text-brass tabular-nums mt-2 relative">Rp {{ number_format($totalSalesAmount, 0, ',', '.') }}</p>
                        <p class="text-[11px] text-muted mt-1 relative">{{ $totalOrdersCount }} pesanan tercatat</p>
                    </div>
                    <div class="bg-white rounded-3xl border border-line p-6">
                        <p class="text-3xl font-extrabold tabular-nums">{{ $catalogCount }}</p>
                        <p class="text-xs text-muted font-semibold mt-1">Produk aktif di katalog</p>
                        <span class="inline-block mt-3 bg-forest/10 text-forest text-[11px] font-bold px-2.5 py-1 rounded-full">+{{ $newProductsThisMonth }} bulan ini</span>
                    </div>
                    <div class="bg-white rounded-3xl border-2 border-brass/40 p-6">
                        <p class="text-3xl font-extrabold text-brass tabular-nums">{{ $pendingOrdersCount }}</p>
                        <p class="text-xs text-muted font-semibold mt-1">Pesanan perlu diproses</p>
                        <span class="inline-block mt-3 bg-brass-soft text-brass text-[11px] font-bold px-2.5 py-1 rounded-full">Perlu aksi</span>
                    </div>
                    <div class="bg-white rounded-3xl border border-line p-6">
                        <p class="text-3xl font-extrabold tabular-nums">{{ number_format($distributor?->rating ?? 0, 1) }}<span class="text-base text-muted font-bold">/5</span></p>
                        <p class="text-xs text-muted font-semibold mt-1">Rating dari UMKM</p>
                        <span class="inline-block mt-3 bg-brass-soft text-brass text-[11px] font-bold px-2.5 py-1 rounded-full">{{ $reviewCount }} ulasan</span>
                    </div>
                </section>

                <section class="bg-white rounded-[2rem] border border-line p-6 sm:p-8 mt-6">
                    <div class="flex items-center justify-between mb-1">
                        <div>
                            <h2 class="text-lg font-extrabold tracking-tight">Pesanan kolektif masuk</h2>
                            <p class="text-xs text-muted mt-0.5">Pesanan terbaru dari grup pilihan UMKM</p>
                        </div>
                        <a href="{{ route('distributor.orders.index') }}" class="text-sm font-bold text-brass">Lihat Semua</a>
                    </div>
                    <div class="divide-y divide-line/70">
                        @forelse ($orders->take(5) as $order)
                            @php
                                $oa = $orderAction($order);
                            @endphp
                            <div class="py-4 flex items-center gap-4">
                                <span class="w-12 h-12 rounded-2xl {{ $oa['action'] ? 'bg-brass-soft text-brass' : 'bg-forest/10 text-forest' }} flex items-center justify-center shrink-0">
                                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 17h6m-5 4h4M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/><path d="M9 8h6M9 12h4"/></svg>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="font-mono font-bold text-sm">{{ $order->order_number }}</p>
                                    <p class="text-xs text-muted mt-0.5">{{ number_format($order->total_quantity, 0) }} unit &bull; Rp {{ number_format($order->total_amount, 0, ',', '.') }} &bull; {{ $order->items->map(fn ($i) => $i->umkm?->business_name)->filter()->unique()->join(', ') }}</p>
                                </div>
                                <p class="font-extrabold tabular-nums text-sm shrink-0">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                                <span class="{{ $oa['pill'][1] }} text-[11px] font-bold px-3 py-1.5 rounded-full shrink-0">{{ $oa['pill'][0] }}</span>
                                @if ($oa['action'])
                                    <a href="{{ route('distributor.orders.index') }}" class="text-xs font-bold text-white bg-forest px-4 py-2.5 rounded-xl shrink-0 cursor-pointer">{{ $oa['action'] }}</a>
                                @endif
                            </div>
                        @empty
                            <p class="text-xs text-muted py-4">Belum ada pesanan kolektif masuk.</p>
                        @endforelse
                    </div>
                </section>
            </main>
        </div>
    </div>
</x-layouts.app>
