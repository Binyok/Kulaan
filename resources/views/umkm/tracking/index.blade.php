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
            <x-notification-bell />
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
            <x-umkm.sidebar-nav active="tracking" />
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
                        <x-notification-bell />
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
    <x-umkm.mobile-tab active="tracking" />
</x-layouts.app>
