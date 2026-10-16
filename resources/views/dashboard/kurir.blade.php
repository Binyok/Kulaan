<x-layouts.app>
    <x-slot name="title">Dashboard Kurir Armada</x-slot>

    @php
        $initials = strtoupper(substr($user->name, 0, 2));
    @endphp

    {{-- MOBILE --}}
    <div class="lg:hidden pb-24">
        <!-- greeting -->
        <div class="px-4 pt-6 flex items-center justify-between">
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
                        <a href="{{ route('kurir.shipments.index') }}" class="flex items-start gap-2 rounded-xl p-2 hover:bg-cream">
                            <span class="w-2 h-2 mt-1.5 rounded-full bg-brass shrink-0"></span>
                            <span class="text-xs text-ink"><b>{{ $assignedShipmentsCount }}</b> tugas menunggu</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- hero -->
        <div class="px-4 mt-4">
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-forest-deep via-forest to-forest-dark text-white p-5">
                <div class="absolute -right-10 -top-12 w-44 h-44 rounded-full bg-white/10"></div>
                <div class="absolute right-16 -bottom-14 w-36 h-36 rounded-full bg-brass/20"></div>
                <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-brass-soft">Kurir Armada</p>
                <h2 class="text-lg font-extrabold mt-1 leading-snug">Halo, {{ $user->name }}</h2>
                <p class="text-[11px] text-white/70 mt-1.5 leading-relaxed">Armada: {{ $courier?->vehicle_type ?? 'Motor' }} &bull; Plat: {{ $courier?->vehicle_number ?? '-' }}. Tugas baru muncul di bawah — tekan mulai biar GPS-mu live.</p>
                <div class="relative mt-4">
                    <a href="{{ route('kurir.shipments.index') }}" class="w-full h-11 rounded-xl bg-white text-forest-dark text-xs font-bold flex items-center justify-center">Buka Daftar Tugas</a>
                </div>
            </div>
        </div>

        <!-- big hero stat -->
        <div class="px-4 mt-4">
            <div class="bg-white rounded-3xl border-2 border-forest/30 p-5 relative overflow-hidden">
                <div class="absolute -right-6 -top-6 w-28 h-28 rounded-full bg-forest/10"></div>
                <div class="flex items-center justify-between relative">
                    <p class="text-xs font-bold uppercase tracking-wider text-muted">Pengiriman Hari Ini</p>
                    <span class="flex items-center gap-1.5 bg-forest/10 text-forest text-[10px] font-bold px-2.5 py-1 rounded-full">
                        <span class="relative flex w-2 h-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success opacity-60"></span><span class="relative inline-flex w-2 h-2 rounded-full bg-success"></span></span>
                        GPS Aktif
                    </span>
                </div>
                <p class="text-[2rem] font-extrabold text-forest tabular-nums mt-2 relative">{{ $assignedShipmentsCount + ($activeDelivery ? 1 : 0) }} <span class="text-base text-muted font-bold">paket</span></p>
                <p class="text-[11px] text-muted mt-1 relative">{{ $assignedShipmentsCount }} menunggu &bull; {{ $activeDelivery ? 1 : 0 }} dalam perjalanan</p>
            </div>
        </div>

        <!-- mini stats -->
        <div class="px-4 mt-3 grid grid-cols-3 gap-2.5">
            <div class="bg-white rounded-3xl border-2 border-brass/40 p-3.5">
                <p class="text-2xl font-extrabold text-brass">{{ $assignedShipmentsCount }}</p>
                <p class="text-[10px] text-muted font-semibold mt-1 leading-tight">Tugas baru menunggu</p>
            </div>
            <div class="bg-white rounded-3xl border border-line p-3.5">
                <p class="text-lg font-extrabold {{ $activeDelivery ? 'text-success' : 'text-muted' }} leading-tight mt-1">{{ $activeDelivery ? 'GPS Aktif' : 'Standby' }}</p>
                <p class="text-[10px] text-muted font-semibold mt-1 leading-tight">Sedang dalam perjalanan</p>
            </div>
            <div class="bg-white rounded-3xl border border-line p-3.5">
                <p class="text-2xl font-extrabold">{{ $completedCount }}</p>
                <p class="text-[10px] text-muted font-semibold mt-1 leading-tight">Pengiriman selesai</p>
            </div>
        </div>

        <!-- active deliveries -->
        <div class="px-4 mt-5">
            <h3 class="text-base font-extrabold">Pengiriman Aktif</h3>
            <div class="mt-3 space-y-2.5">
                @if ($activeDelivery)
                    <div class="relative overflow-hidden rounded-3xl bg-forest text-white p-4">
                        <div class="flex items-center gap-3">
                            <span class="relative flex w-10 h-10 shrink-0">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white/40"></span>
                                <span class="relative inline-flex w-10 h-10 rounded-full bg-white/15 border border-white/30 items-center justify-center">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 17h-2v-11a1 1 0 0 1 1-1h9v12m-8 0h8m4 0h2v-6l-3-4h-3v10"/><circle cx="7.5" cy="17.5" r="1.8"/><circle cx="17.5" cy="17.5" r="1.8"/></svg>
                                </span>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-[13px] font-extrabold">Lagi ngantar sekarang</p>
                                <p class="font-mono text-[10px] text-white/70 mt-0.5">{{ $activeDelivery->shipment_number }} &bull; Pesanan {{ $activeDelivery->order?->order_number }}</p>
                                <p class="text-[11px] text-white/70 mt-0.5 truncate">Tujuan: {{ $activeDelivery->order?->items->map(fn ($i) => $i->umkm?->business_name)->filter()->unique()->join(', ') }}</p>
                            </div>
                        </div>
                        <a href="{{ route('kurir.shipments.index') }}" class="w-full h-10 mt-3 rounded-xl bg-white text-forest-dark text-xs font-bold flex items-center justify-center">Update Lokasi</a>
                    </div>
                @else
                    <p class="text-xs text-muted">Tidak ada pengiriman aktif.</p>
                @endif

                @if ($pendingShipment)
                    <div class="bg-white rounded-3xl border border-line p-4">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-2xl bg-brass-soft text-brass flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-[13px] font-bold">{{ $pendingShipment->shipment_number }} &bull; {{ $pendingShipment->order?->order_number }}</p>
                                <p class="text-[11px] text-muted mt-0.5 truncate">Tujuan: {{ $pendingShipment->order?->items->map(fn ($i) => $i->umkm?->business_name)->filter()->unique()->join(', ') }} &bull; ambil di gudang</p>
                            </div>
                        </div>
                        <a href="{{ route('kurir.shipments.index') }}" class="w-full h-10 mt-3 rounded-xl bg-forest text-white text-xs font-bold flex items-center justify-center">Mulai Perjalanan</a>
                    </div>
                @else
                    <p class="text-xs text-muted">Tidak ada tugas menunggu.</p>
                @endif
            </div>
        </div>

        <!-- task panel CTA -->
        <div class="px-4 mt-4 pb-6">
            <div class="bg-cream border border-line rounded-3xl p-4 flex items-center gap-3">
                <span class="w-10 h-10 rounded-2xl bg-white border border-line text-forest flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 17h6m-5 4h4M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/><path d="M9 8h6M9 12h4"/></svg>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-[13px] font-bold">Panel tugas pengiriman</p>
                    <p class="text-[11px] text-muted mt-0.5 leading-snug">Mulai perjalanan biar GPS live, atau konfirmasi kalau barang sudah sampai.</p>
                </div>
            </div>
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
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-forest text-white">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m2.25 12 8.95-8.95a1.13 1.13 0 0 1 1.6 0l8.95 8.95M4.5 9.75V19.5a.75.75 0 0 0 .75.75h13.5a.75.75 0 0 0 .75-.75V9.75"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('kurir.shipments.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 17h6m-5 4h4M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/><path d="M9 8h6M9 12h4"/></svg>
                    Daftar Tugas
                    @if ($assignedShipmentsCount > 0)
                        <span class="ml-auto bg-brass text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $assignedShipmentsCount }}</span>
                    @endif
                </a>
            </nav>
            <div class="p-4 border-t border-line">
                <a href="{{ route('profile.complete') }}" class="flex items-center gap-3 px-2">
                    <span class="w-10 h-10 rounded-2xl bg-forest text-white flex items-center justify-center font-extrabold text-xs">{{ $initials }}</span>
                    <div class="min-w-0">
                        <p class="text-sm font-bold truncate">{{ $user->name }}</p>
                        <p class="text-[11px] text-muted">{{ $courier?->vehicle_type ?? 'Motor' }} &bull; {{ $courier?->vehicle_number ?? '-' }}</p>
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
                        <h1 class="text-lg font-extrabold leading-tight">Halo, {{ $user->name }}</h1>
                        <p class="text-xs text-muted">{{ now()->translatedFormat('l, d F Y') }}</p>
                    </div>
                    <div class="ml-auto flex items-center gap-3">
                        <span class="hidden md:flex items-center gap-2 bg-forest/10 text-forest text-xs font-bold px-4 py-2.5 rounded-2xl">
                            <span class="relative flex w-2 h-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success opacity-60"></span><span class="relative inline-flex w-2 h-2 rounded-full bg-success"></span></span>
                            GPS Aktif
                        </span>
                        <div class="relative" x-data="{ notifOpen: false }">
                            <button type="button" @click="notifOpen = !notifOpen" class="w-11 h-11 rounded-2xl bg-white border border-line flex items-center justify-center relative cursor-pointer">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14.86 17.08a23.85 23.85 0 0 0 5.45-1.31A8.97 8.97 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.97 8.97 0 0 1-2.31 6.02c1.73.64 3.56 1.09 5.45 1.31m5.72 0a24.26 24.26 0 0 1-5.72 0m5.72 0a3 3 0 1 1-5.72 0"/></svg>
                            </button>
                            <div x-show="notifOpen" @click.away="notifOpen = false" x-cloak class="absolute right-0 mt-2 w-72 bg-white border border-line rounded-2xl shadow-xl p-4 z-40 text-left">
                                <p class="text-sm font-extrabold text-ink">Notifikasi</p>
                                <div class="mt-3 space-y-1">
                                    <a href="{{ route('kurir.shipments.index') }}" class="flex items-start gap-2 rounded-xl p-2 hover:bg-cream">
                                        <span class="w-2 h-2 mt-1.5 rounded-full bg-brass shrink-0"></span>
                                        <span class="text-xs text-ink"><b>{{ $assignedShipmentsCount }}</b> tugas menunggu</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="px-8 py-6 max-w-6xl">
                <!-- hero -->
                <section class="relative overflow-hidden rounded-[2rem] bg-gradient-to-r from-forest-deep via-forest to-forest-dark text-white p-8">
                    <div class="absolute -right-16 -top-20 w-72 h-72 rounded-full bg-white/10"></div>
                    <div class="absolute right-40 -bottom-24 w-56 h-56 rounded-full bg-brass/20"></div>
                    <div class="relative flex items-center justify-between gap-6 flex-wrap">
                        <div class="max-w-xl">
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-brass-soft">Kurir Armada</p>
                            <h2 class="text-2xl font-extrabold mt-1">Halo, {{ $user->name }}</h2>
                            <p class="text-white/70 text-sm mt-2">Armada: {{ $courier?->vehicle_type ?? 'Motor' }} &bull; Plat: {{ $courier?->vehicle_number ?? '-' }}. Tugas baru muncul di bawah — tekan mulai biar GPS-mu live.</p>
                        </div>
                        <a href="{{ route('kurir.shipments.index') }}" class="h-12 px-6 rounded-2xl bg-white text-forest-dark text-sm font-bold flex items-center justify-center">Buka Daftar Tugas</a>
                    </div>
                </section>

                <!-- stats -->
                <section class="grid grid-cols-2 xl:grid-cols-4 gap-4 mt-6">
                    <div class="bg-white rounded-3xl border-2 border-forest/30 p-6 relative overflow-hidden">
                        <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-forest/10"></div>
                        <p class="text-xs font-bold uppercase tracking-wider text-muted relative">Pengiriman Hari Ini</p>
                        <p class="text-3xl font-extrabold text-forest tabular-nums mt-2 relative">{{ $assignedShipmentsCount + ($activeDelivery ? 1 : 0) }} <span class="text-base text-muted font-bold">paket</span></p>
                        <p class="text-[11px] text-muted mt-1 relative">{{ $assignedShipmentsCount }} menunggu &bull; {{ $activeDelivery ? 1 : 0 }} dalam perjalanan</p>
                    </div>
                    <div class="bg-white rounded-3xl border-2 border-brass/40 p-6">
                        <p class="text-3xl font-extrabold text-brass tabular-nums">{{ $assignedShipmentsCount }}</p>
                        <p class="text-xs text-muted font-semibold mt-1">Tugas baru menunggu</p>
                        <span class="inline-block mt-3 bg-brass-soft text-brass text-[11px] font-bold px-2.5 py-1 rounded-full">Segera ambil</span>
                    </div>
                    <div class="bg-white rounded-3xl border border-line p-6">
                        <p class="text-2xl font-extrabold {{ $activeDelivery ? 'text-success' : 'text-muted' }} mt-1">{{ $activeDelivery ? 'GPS Aktif' : 'Standby' }}</p>
                        <p class="text-xs text-muted font-semibold mt-1">Sedang dalam perjalanan</p>
                        <span class="inline-block mt-3 bg-success/10 text-success text-[11px] font-bold px-2.5 py-1 rounded-full">Live</span>
                    </div>
                    <div class="bg-white rounded-3xl border border-line p-6">
                        <p class="text-3xl font-extrabold tabular-nums">{{ $completedCount }}</p>
                        <p class="text-xs text-muted font-semibold mt-1">Pengiriman selesai</p>
                        <span class="inline-block mt-3 bg-forest/10 text-forest text-[11px] font-bold px-2.5 py-1 rounded-full">+{{ $completedThisWeek }} minggu ini</span>
                    </div>
                </section>

                <!-- active deliveries -->
                <section class="bg-white rounded-[2rem] border border-line p-6 sm:p-8 mt-6">
                    <div class="flex items-center justify-between mb-1">
                        <div>
                            <h2 class="text-lg font-extrabold tracking-tight">Pengiriman aktif</h2>
                            <p class="text-xs text-muted mt-0.5">Update lokasimu agar UMKM bisa melacak live</p>
                        </div>
                        <a href="{{ route('kurir.shipments.index') }}" class="text-sm font-bold text-brass">Live Map</a>
                    </div>
                    <div class="mt-4 space-y-4">
                        @if ($activeDelivery)
                            <div class="rounded-3xl bg-forest text-white p-5 flex flex-col sm:flex-row sm:items-center gap-4">
                                <span class="relative flex w-12 h-12 shrink-0">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white/40"></span>
                                    <span class="relative inline-flex w-12 h-12 rounded-full bg-white/15 border border-white/30 items-center justify-center">
                                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 17h-2v-11a1 1 0 0 1 1-1h9v12m-8 0h8m4 0h2v-6l-3-4h-3v10"/><circle cx="7.5" cy="17.5" r="1.8"/><circle cx="17.5" cy="17.5" r="1.8"/></svg>
                                    </span>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="font-extrabold">Lagi ngantar sekarang</p>
                                    <p class="font-mono text-xs text-white/70 mt-1">{{ $activeDelivery->shipment_number }} &bull; Pesanan {{ $activeDelivery->order?->order_number }}</p>
                                    <p class="text-xs text-white/70 mt-0.5">Tujuan: {{ $activeDelivery->order?->items->map(fn ($i) => $i->umkm?->business_name)->filter()->unique()->join(', ') }}</p>
                                </div>
                                <a href="{{ route('kurir.shipments.index') }}" class="h-11 px-6 rounded-2xl bg-white text-forest-dark text-sm font-bold shrink-0 flex items-center justify-center">Update Lokasi</a>
                            </div>
                        @else
                            <p class="text-xs text-muted">Tidak ada pengiriman aktif.</p>
                        @endif

                        @if ($pendingShipment)
                            <div class="rounded-3xl border border-line p-5 flex flex-col sm:flex-row sm:items-center gap-4">
                                <span class="w-12 h-12 rounded-2xl bg-brass-soft text-brass flex items-center justify-center shrink-0">
                                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="font-bold">{{ $pendingShipment->shipment_number }} &bull; {{ $pendingShipment->order?->order_number }}</p>
                                    <p class="text-xs text-muted mt-1">Tujuan: {{ $pendingShipment->order?->items->map(fn ($i) => $i->umkm?->business_name)->filter()->unique()->join(', ') }} &bull; ambil di gudang</p>
                                </div>
                                <a href="{{ route('kurir.shipments.index') }}" class="h-11 px-6 rounded-2xl bg-forest text-white text-sm font-bold shrink-0 flex items-center justify-center">Mulai Perjalanan</a>
                            </div>
                        @else
                            <p class="text-xs text-muted">Tidak ada tugas menunggu.</p>
                        @endif
                    </div>
                </section>

                <!-- task panel CTA -->
                <section class="bg-white rounded-[2rem] border border-line p-6 sm:p-7 mt-6 flex flex-col sm:flex-row sm:items-center gap-4">
                    <span class="w-12 h-12 rounded-2xl bg-forest/10 text-forest flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 17h6m-5 4h4M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/><path d="M9 8h6M9 12h4"/></svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <h3 class="font-bold text-base">Panel tugas pengiriman</h3>
                        <p class="text-sm text-muted mt-0.5">Mulai perjalanan biar GPS live, atau konfirmasi kalau barang sudah sampai.</p>
                    </div>
                    <a href="{{ route('kurir.shipments.index') }}" class="h-12 px-6 rounded-2xl bg-forest text-white text-sm font-bold shrink-0 flex items-center justify-center">Buka Daftar Tugas</a>
                </section>
            </main>
        </div>
    </div>

    <div class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-white border-t border-line px-2 pt-2 pb-5 flex items-start justify-around">
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 w-14 text-forest">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m2.25 12 8.95-8.95a1.13 1.13 0 0 1 1.6 0l8.95 8.95M4.5 9.75V19.5a.75.75 0 0 0 .75.75h13.5a.75.75 0 0 0 .75-.75V9.75"/></svg>
            <span class="text-[10px] font-bold">Beranda</span>
        </a>
        <a href="{{ route('kurir.shipments.index') }}" class="flex flex-col items-center gap-1 w-14 text-muted relative">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 17h6m-5 4h4M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/><path d="M9 8h6M9 12h4"/></svg>
            <span class="text-[10px] font-semibold">Tugas</span>
            @if ($assignedShipmentsCount > 0)
                <span class="absolute top-0 right-2 min-w-[18px] h-[18px] px-1 rounded-full bg-brass text-white text-[10px] font-bold flex items-center justify-center">{{ $assignedShipmentsCount }}</span>
            @endif
        </a>
        <a href="{{ route('profile.complete') }}" class="flex flex-col items-center gap-1 w-14 text-muted">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17.98 18.72a6 6 0 0 0-8.46 0M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm-9.75 8.25a9 9 0 1 1 13.5 0"/></svg>
            <span class="text-[10px] font-semibold">Akun</span>
        </a>
    </div>
</x-layouts.app>
