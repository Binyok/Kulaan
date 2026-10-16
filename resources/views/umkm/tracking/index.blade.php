<x-layouts.app>
    <x-slot name="title">Pantau Pengiriman & Posisi Kurir</x-slot>

    @php
        $user = auth()->user();
        $displayName = $umkm?->business_name ?? $user->name;
        $initials = strtoupper(substr($user->name, 0, 2));
        $cartCount = app(\App\Services\CartService::class)->count();
        $hasShipments = $shipments->isNotEmpty();
        $isFiltering = $tab !== 'all' || $q !== '' || $from || $to;
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
            <div class="relative" x-data="{ notifOpen: false }">
                <button type="button" @click="notifOpen = !notifOpen" class="w-10 h-10 rounded-2xl bg-white border border-line flex items-center justify-center text-ink relative cursor-pointer">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14.86 17.08a23.85 23.85 0 0 0 5.45-1.31A8.97 8.97 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.97 8.97 0 0 1-2.31 6.02c1.73.64 3.56 1.09 5.45 1.31m5.72 0a24.26 24.26 0 0 1-5.72 0m5.72 0a3 3 0 1 1-5.72 0"/></svg>
                </button>
                <div x-show="notifOpen" @click.away="notifOpen = false" x-cloak class="absolute right-0 mt-2 w-72 bg-white border border-line rounded-2xl shadow-xl p-4 z-40 text-left">
                    <p class="text-sm font-extrabold text-ink">Notifikasi</p>
                    <div class="mt-3 space-y-1">
                        <a href="{{ route('umkm.orders.index') }}" class="flex items-start gap-2 rounded-xl p-2 hover:bg-cream">
                            <span class="w-2 h-2 mt-1.5 rounded-full bg-brass shrink-0"></span>
                            <span class="text-xs text-ink">Cek tagihan yang menunggu pembayaran</span>
                        </a>
                        <a href="{{ route('umkm.tracking.index') }}" class="flex items-start gap-2 rounded-xl p-2 hover:bg-cream">
                            <span class="w-2 h-2 mt-1.5 rounded-full bg-forest shrink-0"></span>
                            <span class="text-xs text-ink">Pengiriman sedang berjalan</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="px-4 pt-4">
            <div class="bg-gradient-to-r from-forest to-forest-dark text-white rounded-2xl p-5 shadow-sm">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[0.6875rem] font-semibold bg-white/15 text-white">Live Tracking GPS</span>
                <h1 class="font-extrabold text-xl mt-2 tracking-tight">Pantau Pengiriman Bahan Baku</h1>
                <p class="text-xs text-white/80 mt-1 leading-relaxed">Pantau posisi armada kurir pengantar bahan bakumu secara realtime di peta.</p>
                <a href="{{ route('umkm.tracking.live') }}" class="inline-block mt-3 bg-brass text-white text-xs font-bold px-4 py-2.5 rounded-xl">Peta Semua Kurir Aktif &rarr;</a>
            </div>
        </div>

        <div class="px-4 mt-4">
            <div class="flex gap-2" role="tablist">
                @foreach (['all' => 'Semua', 'active' => 'Sedang Jalan', 'history' => 'Riwayat Tiba'] as $key => $label)
                    <a href="{{ route('umkm.tracking.index', array_filter(['tab' => $key === 'all' ? null : $key, 'q' => $q ?: null, 'from' => $from, 'to' => $to])) }}"
                       class="px-4 py-2 rounded-xl text-xs font-bold {{ $tab === $key ? 'bg-forest text-white' : 'bg-white text-muted border border-line' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="px-4 mt-4">
            <form method="GET" action="{{ route('umkm.tracking.index') }}" class="flex gap-2">
                @if ($tab !== 'all')
                    <input type="hidden" name="tab" value="{{ $tab }}">
                @endif
                <input type="hidden" name="from" value="{{ $from }}">
                <input type="hidden" name="to" value="{{ $to }}">
                <input type="search" name="q" value="{{ $q }}" placeholder="Cari surat jalan, produk, distributor..." class="flex-1 text-xs bg-white border border-line rounded-xl px-3 py-2.5 placeholder:text-muted/60 focus:outline-none">
                <button type="submit" class="bg-forest text-white text-xs font-bold px-4 rounded-xl">Cari</button>
            </form>
        </div>

        <div class="px-4 mt-4 pb-6">
            @if ($hasShipments)
                <div class="space-y-3">
                    @foreach ($shipments as $shipment)
                        @php
                            $inTransit = $shipment->status === 'IN_TRANSIT';
                            $statusLabel = $inTransit ? 'Sedang Diantar' : ($shipment->status === 'DELIVERED' ? 'Telah Sampai' : 'Diproses');
                            $statusClass = $inTransit ? 'bg-success/10 text-success' : ($shipment->status === 'DELIVERED' ? 'bg-forest text-white' : 'bg-cream text-muted border border-line');
                        @endphp
                        <article class="bg-white rounded-2xl {{ $inTransit ? 'border-2 border-forest/40 ring-2 ring-forest/10' : 'border border-line' }} p-4 shadow-sm">
                            <div class="flex items-start gap-3">
                                <span class="w-11 h-11 rounded-2xl {{ $inTransit ? 'bg-forest text-white' : 'bg-cream text-muted' }} flex items-center justify-center shrink-0">
                                    @if ($inTransit)
                                        <span class="relative flex w-4 h-4">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brass opacity-75"></span>
                                            <span class="relative inline-flex w-4 h-4 rounded-full bg-brass"></span>
                                        </span>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                    @endif
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2 mb-1">
                                        <span class="font-mono text-xs font-bold text-ink">{{ $shipment->shipment_number }}</span>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $statusClass }}">{{ $statusLabel }}</span>
                                    </div>
                                    <p class="font-bold text-ink text-sm">{{ $shipment->order->items->pluck('product.name')->join(', ') }}</p>
                                    <p class="text-xs text-muted mt-0.5">
                                        Mitra {{ $shipment->order->distributor->company_name }}
                                        @if ($shipment->courier)
                                            &bull; Kurir: <strong class="text-ink">{{ $shipment->courier->user->name }}</strong> ({{ $shipment->courier->vehicle_type }})
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <a href="{{ route('umkm.tracking.show', $shipment) }}"
                               class="block text-center w-full mt-3 text-xs font-bold py-2.5 rounded-xl {{ $inTransit ? 'bg-forest text-white' : 'text-forest border border-forest/20' }}">
                                {{ $inTransit ? 'Buka Live GPS &rarr;' : 'Lihat Rute Peta' }}
                            </a>
                        </article>
                    @endforeach
                </div>
                <div class="pt-4">{{ $shipments->links() }}</div>
            @else
                <section class="bg-white rounded-2xl border border-line p-10 text-center max-w-md mx-auto shadow-sm">
                    <div class="w-12 h-12 rounded-2xl bg-forest/10 text-forest flex items-center justify-center mx-auto font-extrabold text-xl">K</div>
                    <h2 class="font-extrabold text-ink text-base mt-3">Tidak Ada Pengiriman</h2>
                    <p class="text-xs text-muted mt-1 max-w-sm mx-auto">
                        Pengiriman aktif akan tampil di sini saat distributor menugaskan armada kurir.
                    </p>
                </section>
            @endif
        </div>
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
            <nav class="flex-1 px-4 py-6 space-y-1 text-sm font-semibold">
                <a href="{{ route('umkm.catalog.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>
                    Katalog
                </a>
                <a href="{{ route('umkm.cart.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2.25 3h1.39c.51 0 .96.34 1.09.84l.38 1.44M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.22c1.12-2.3 2.1-4.68 2.92-7.14a60.11 60.11 0 0 0-16.54-1.84M7.5 14.25 5.11 5.27M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                    Keranjang
                    @if ($cartCount > 0)
                        <span class="ml-auto bg-brass text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $cartCount }}</span>
                    @endif
                </a>
                <a href="{{ route('umkm.requests.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                    Pengajuan
                </a>
                <a href="{{ route('umkm.orders.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.25 7.5l-.63 10.63a2.25 2.25 0 0 1-2.25 2.12H6.63a2.25 2.25 0 0 1-2.25-2.12L3.75 7.5M10 11.25h4M3.38 7.5h17.25c.62 0 1.12-.5 1.12-1.12v-1.5C21.75 4.26 21.25 3.75 20.63 3.75H3.38c-.62 0-1.13.5-1.13 1.12v1.5c0 .63.5 1.13 1.12 1.13Z"/></svg>
                    Pesanan
                </a>
                <a href="{{ route('umkm.tracking.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-forest text-white">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path d="M19.5 10.5c0 7.14-7.5 11.25-7.5 11.25S4.5 17.64 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                    Pelacakan
                </a>
            </nav>
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
                        <h1 class="text-lg font-extrabold leading-tight">Selamat pagi, {{ $displayName }}</h1>
                        <p class="text-xs text-muted">{{ now()->translatedFormat('l, d F Y') }}</p>
                    </div>
                    <div class="ml-auto flex items-center gap-3">
                        <form method="GET" action="{{ route('umkm.catalog.index') }}" class="hidden lg:flex items-center gap-2 bg-white border border-line rounded-2xl pl-4 pr-2 py-2 w-72">
                            <svg class="w-4 h-4 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21 21-5.2-5.2m0 0A7.5 7.5 0 1 0 5.2 5.2a7.5 7.5 0 0 0 10.6 10.6Z"/></svg>
                            <input name="q" class="flex-1 bg-transparent text-sm outline-none placeholder:text-muted/70" placeholder="Cari pesanan, produk...">
                        </form>
                        <div class="relative" x-data="{ notifOpen: false }">
                            <button type="button" @click="notifOpen = !notifOpen" class="w-11 h-11 rounded-2xl bg-white border border-line flex items-center justify-center relative cursor-pointer">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14.86 17.08a23.85 23.85 0 0 0 5.45-1.31A8.97 8.97 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.97 8.97 0 0 1-2.31 6.02c1.73.64 3.56 1.09 5.45 1.31m5.72 0a24.26 24.26 0 0 1-5.72 0m5.72 0a3 3 0 1 1-5.72 0"/></svg>
                            </button>
                            <div x-show="notifOpen" @click.away="notifOpen = false" x-cloak class="absolute right-0 mt-2 w-72 bg-white border border-line rounded-2xl shadow-xl p-4 z-40 text-left">
                                <p class="text-sm font-extrabold text-ink">Notifikasi</p>
                                <div class="mt-3 space-y-1">
                                    <a href="{{ route('umkm.orders.index') }}" class="flex items-start gap-2 rounded-xl p-2 hover:bg-cream">
                                        <span class="w-2 h-2 mt-1.5 rounded-full bg-brass shrink-0"></span>
                                        <span class="text-xs text-ink">Cek tagihan yang menunggu pembayaran</span>
                                    </a>
                                    <a href="{{ route('umkm.tracking.index') }}" class="flex items-start gap-2 rounded-xl p-2 hover:bg-cream">
                                        <span class="w-2 h-2 mt-1.5 rounded-full bg-forest shrink-0"></span>
                                        <span class="text-xs text-ink">Pengiriman sedang berjalan</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="px-8 py-6 max-w-6xl">
                <div class="bg-gradient-to-r from-forest to-forest-dark text-white rounded-2xl p-7 shadow-sm mb-6">
                    <div class="flex items-center justify-between gap-4 flex-wrap">
                        <div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[0.6875rem] font-semibold bg-white/15 text-white">Live Tracking GPS</span>
                            <h1 class="font-extrabold text-3xl mt-2 tracking-tight">Pantau Pengiriman Bahan Baku</h1>
                            <p class="text-sm text-white/80 mt-1">Pantau posisi armada kurir pengantar bahan bakumu secara realtime di peta.</p>
                        </div>
                        <a href="{{ route('umkm.tracking.live') }}" class="bg-brass text-white text-sm font-bold px-5 py-3 rounded-xl">Peta Semua Kurir Aktif &rarr;</a>
                    </div>
                </div>

                <section class="bg-white rounded-2xl border border-line p-5 mb-6 space-y-4 shadow-sm">
                    <div class="flex flex-wrap gap-2" role="tablist">
                        @foreach (['all' => 'Semua', 'active' => 'Sedang Jalan', 'history' => 'Riwayat Tiba'] as $key => $label)
                            <a href="{{ route('umkm.tracking.index', array_filter(['tab' => $key === 'all' ? null : $key, 'q' => $q ?: null, 'from' => $from, 'to' => $to])) }}"
                               class="px-4 py-2 rounded-xl text-xs font-bold {{ $tab === $key ? 'bg-forest text-white' : 'bg-cream text-muted' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                    <form method="GET" action="{{ route('umkm.tracking.index') }}" class="grid grid-cols-1 md:grid-cols-[1fr_auto_auto_auto] gap-2">
                        @if ($tab !== 'all')
                            <input type="hidden" name="tab" value="{{ $tab }}">
                        @endif
                        <input type="search" name="q" value="{{ $q }}" placeholder="Cari surat jalan, produk, distributor..." class="text-xs bg-cream/50 border border-line rounded-xl px-3 py-2.5 placeholder:text-muted/60 focus:outline-none">
                        <input type="date" name="from" value="{{ $from }}" class="text-xs bg-cream/50 border border-line rounded-xl px-3 py-2.5 text-muted">
                        <input type="date" name="to" value="{{ $to }}" class="text-xs bg-cream/50 border border-line rounded-xl px-3 py-2.5 text-muted">
                        <div class="flex gap-2">
                            <button type="submit" class="bg-forest text-white text-xs px-5 py-2.5 font-bold rounded-xl">Cari</button>
                            @if ($isFiltering)
                                <a href="{{ route('umkm.tracking.index') }}" class="text-muted text-xs px-4 py-2.5 font-bold rounded-xl border border-line">Reset</a>
                            @endif
                        </div>
                    </form>
                </section>

                @if ($hasShipments)
                    <div class="space-y-4">
                        @foreach ($shipments as $shipment)
                            @php
                                $inTransit = $shipment->status === 'IN_TRANSIT';
                                $statusLabel = $inTransit ? 'Sedang Diantar' : ($shipment->status === 'DELIVERED' ? 'Telah Sampai' : 'Diproses');
                                $statusClass = $inTransit ? 'bg-success/10 text-success' : ($shipment->status === 'DELIVERED' ? 'bg-forest text-white' : 'bg-cream text-muted border border-line');
                            @endphp
                            <article class="bg-white rounded-2xl {{ $inTransit ? 'border-2 border-forest/40 ring-2 ring-forest/10' : 'border border-line' }} p-5 shadow-sm flex items-center justify-between gap-4 flex-wrap">
                                <div class="flex items-start gap-4 min-w-0">
                                    <span class="w-12 h-12 rounded-2xl {{ $inTransit ? 'bg-forest text-white' : 'bg-cream text-muted' }} flex items-center justify-center shrink-0">
                                        @if ($inTransit)
                                            <span class="relative flex w-4 h-4">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brass opacity-75"></span>
                                                <span class="relative inline-flex w-4 h-4 rounded-full bg-brass"></span>
                                            </span>
                                        @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                        @endif
                                    </span>
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2 mb-1">
                                            <span class="font-mono text-xs font-bold text-ink">{{ $shipment->shipment_number }}</span>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $statusClass }}">{{ $statusLabel }}</span>
                                        </div>
                                        <p class="font-bold text-ink text-sm">{{ $shipment->order->items->pluck('product.name')->join(', ') }}</p>
                                        <p class="text-xs text-muted mt-0.5">
                                            Mitra {{ $shipment->order->distributor->company_name }}
                                            @if ($shipment->courier)
                                                &bull; Kurir: <strong class="text-ink">{{ $shipment->courier->user->name }}</strong> ({{ $shipment->courier->vehicle_type }})
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <a href="{{ route('umkm.tracking.show', $shipment) }}"
                                   class="text-xs font-bold py-3 px-5 rounded-xl {{ $inTransit ? 'bg-forest text-white' : 'text-forest border border-forest/20' }}">
                                    {{ $inTransit ? 'Buka Live GPS &rarr;' : 'Lihat Rute Peta' }}
                                </a>
                            </article>
                        @endforeach
                    </div>

                    <div class="pt-4">{{ $shipments->links() }}</div>
                @else
                    <section class="bg-white rounded-2xl border border-line p-10 sm:p-14 text-center max-w-md mx-auto shadow-sm">
                        <div class="w-12 h-12 rounded-2xl bg-forest/10 text-forest flex items-center justify-center mx-auto font-extrabold text-xl">K</div>
                        <h2 class="font-extrabold text-ink text-base mt-3">Tidak Ada Pengiriman</h2>
                        <p class="text-xs text-muted mt-1 max-w-sm mx-auto">
                            Pengiriman aktif akan tampil di sini saat distributor menugaskan armada kurir.
                        </p>
                    </section>
                @endif
            </main>
        </div>
    </div>

    {{-- BOTTOM TAB --}}
    <div class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-white border-t border-line px-2 pt-2 pb-5 flex items-start justify-around">
        <a href="{{ route('umkm.catalog.index') }}" class="flex flex-col items-center gap-1 w-14 text-muted">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>
            <span class="text-[10px] font-semibold">Katalog</span>
        </a>
        <a href="{{ route('umkm.cart.index') }}" class="flex flex-col items-center gap-1 w-14 text-muted relative">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2.25 3h1.39c.51 0 .96.34 1.09.84l.38 1.44M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.22c1.12-2.3 2.1-4.68 2.92-7.14a60.11 60.11 0 0 0-16.54-1.84M7.5 14.25 5.11 5.27M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
            <span class="text-[10px] font-semibold">Keranjang</span>
            @if ($cartCount > 0)
                <span class="absolute top-0 right-2 min-w-[18px] h-[18px] px-1 rounded-full bg-brass text-white text-[10px] font-bold flex items-center justify-center">{{ $cartCount }}</span>
            @endif
        </a>
        <a href="{{ route('umkm.requests.index') }}" class="flex flex-col items-center gap-1 w-14 text-muted">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
            <span class="text-[10px] font-semibold">Pengajuan</span>
        </a>
        <a href="{{ route('umkm.orders.index') }}" class="flex flex-col items-center gap-1 w-14 text-muted">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.25 7.5l-.63 10.63a2.25 2.25 0 0 1-2.25 2.12H6.63a2.25 2.25 0 0 1-2.25-2.12L3.75 7.5M10 11.25h4M3.38 7.5h17.25c.62 0 1.12-.5 1.12-1.12v-1.5C21.75 4.26 21.25 3.75 20.63 3.75H3.38c-.62 0-1.13.5-1.13 1.12v1.5c0 .63.5 1.13 1.12 1.13Z"/></svg>
            <span class="text-[10px] font-semibold">Pesanan</span>
        </a>
        <a href="{{ route('umkm.tracking.index') }}" class="flex flex-col items-center gap-1 w-14 text-forest">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path d="M19.5 10.5c0 7.14-7.5 11.25-7.5 11.25S4.5 17.64 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
            <span class="text-[10px] font-bold">Pelacakan</span>
        </a>
    </div>
</x-layouts.app>
