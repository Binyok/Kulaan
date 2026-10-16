<x-layouts.app>
    <x-slot name="title">Tugas Pengiriman Kurir</x-slot>

    @php
        $user = auth()->user();
        $initials = strtoupper(substr($user->name, 0, 2));
        $hasShipments = $shipments->isNotEmpty();
        $totalShipments = $shipments->total();
    @endphp

    {{-- MOBILE --}}
    <div class="lg:hidden pb-24">
        {{-- greeting header --}}
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
                            <span class="text-xs text-ink"><b>{{ $totalShipments }}</b> tugas pengiriman</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- forest header --}}
        <div class="mx-4 mt-2 rounded-3xl bg-gradient-to-br from-forest-deep via-forest to-forest-dark text-white p-5 relative overflow-hidden">
            <div class="absolute -right-10 -top-12 w-44 h-44 rounded-full bg-white/10"></div>
            <span class="relative inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold bg-white/10 text-white/85 uppercase tracking-[0.14em]">Panel Kurir Armada</span>
            <h1 class="relative text-2xl font-extrabold mt-2 tracking-tight">Tugas Pengiriman</h1>
            <p class="relative text-xs text-white/70 leading-relaxed mt-1.5">Tekan "Mulai Pengiriman" pas berangkat biar GPS-mu live di HP UMKM. Sampai tujuan? Tekan selesai.</p>
        </div>

        {{-- GPS strip --}}
        <div class="mx-4 mt-4 rounded-2xl bg-success/10 border border-forest/15 p-3.5 flex items-start gap-3">
            <span class="w-9 h-9 rounded-full bg-white text-forest flex items-center justify-center shrink-0 border border-forest/15">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>
            </span>
            <p class="text-[11px] leading-relaxed text-forest"><strong>GPS otomatis aktif</strong> begitu kamu tekan "Mulai Pengiriman". Pastikan izin lokasi di browser/HP-mu nyala ya.</p>
        </div>

        @if ($hasShipments)
            {{-- shipment list --}}
            <div class="px-4 mt-4 space-y-3 pb-6">
                @foreach ($shipments as $shipment)
                    @php
                        $isTransit = $shipment->status === 'IN_TRANSIT';
                        $isDelivered = $shipment->status === 'DELIVERED';
                        $isPending = $shipment->status === 'PENDING';
                    @endphp
                    <article
                        x-data="{
                            isTransmitting: {{ $isTransit ? 'true' : 'false' }},
                            watchId: null,
                            startGps() {
                                if ('geolocation' in navigator) {
                                    this.watchId = navigator.geolocation.watchPosition(pos => {
                                        axios.post('/kurir/shipments/{{ $shipment->id }}/gps', {
                                            latitude: pos.coords.latitude,
                                            longitude: pos.coords.longitude,
                                            speed: pos.coords.speed || 0
                                        });
                                    }, err => console.warn(err), { enableHighAccuracy: true });
                                }
                            }
                        }"
                        x-init="if (isTransmitting && $el.offsetParent !== null) startGps();"
                        class="bg-white rounded-3xl p-4 shadow-sm {{ $isTransit ? 'border-2 border-forest/25' : 'border border-line' }} {{ $isDelivered ? 'opacity-90' : '' }}">
                        <div class="flex items-start gap-3">
                            @if ($isTransit)
                                <span class="w-12 h-12 rounded-2xl bg-success/10 text-forest flex items-center justify-center shrink-0 relative">
                                    <span class="absolute inline-flex h-full w-full rounded-2xl bg-forest opacity-20 animate-ping"></span>
                                    <svg class="w-6 h-6 relative" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 17h-2v-11a1 1 0 0 1 1-1h9v12m-8 0h8m4 0h2v-6l-3-4h-3v10"/><circle cx="7.5" cy="17.5" r="1.8"/><circle cx="17.5" cy="17.5" r="1.8"/></svg>
                                </span>
                            @elseif ($isDelivered)
                                <span class="w-12 h-12 rounded-2xl bg-success/10 text-success flex items-center justify-center shrink-0">
                                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 17h-2v-11a1 1 0 0 1 1-1h9v12m-8 0h8m4 0h2v-6l-3-4h-3v10"/><circle cx="7.5" cy="17.5" r="1.8"/><circle cx="17.5" cy="17.5" r="1.8"/></svg>
                                </span>
                            @else
                                <span class="w-12 h-12 rounded-2xl bg-brass-soft text-brass flex items-center justify-center shrink-0">
                                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 17h-2v-11a1 1 0 0 1 1-1h9v12m-8 0h8m4 0h2v-6l-3-4h-3v10"/><circle cx="7.5" cy="17.5" r="1.8"/><circle cx="17.5" cy="17.5" r="1.8"/></svg>
                                </span>
                            @endif
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="font-mono font-bold text-ink text-sm">{{ $shipment->shipment_number }}</p>
                                        <p class="font-mono text-[11px] text-muted mt-0.5">{{ $shipment->order->order_number }}</p>
                                    </div>
                                    @if ($isTransit)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-success/10 text-success shrink-0">
                                            <span class="w-1.5 h-1.5 rounded-full bg-success animate-pulse"></span>GPS Live
                                        </span>
                                    @elseif ($isDelivered)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-forest text-white shrink-0">Selesai</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-brass-soft text-brass shrink-0">{{ $isPending ? 'Menunggu' : $shipment->status }}</span>
                                    @endif
                                </div>
                                <div class="mt-2.5 space-y-1.5 text-[11px]">
                                    @foreach ($shipment->order->items as $item)
                                        <p><strong class="text-ink">{{ $item->umkm->business_name }}</strong> <span class="text-muted">&bull; {{ $item->delivery_address }} ({{ $item->delivery_district }})</span></p>
                                    @endforeach
                                </div>
                                <div class="mt-3">
                                    @if ($isPending)
                                        <form method="POST" action="{{ route('kurir.shipments.start', $shipment) }}">
                                            @csrf
                                            <button type="submit" class="w-full bg-forest text-white text-sm font-bold py-3 rounded-2xl cursor-pointer">
                                                Mulai Pengiriman
                                            </button>
                                        </form>
                                    @elseif ($isTransit)
                                        <form method="POST" action="{{ route('kurir.shipments.complete', $shipment) }}" id="complete-shipment-m-{{ $shipment->id }}">
                                            @csrf
                                            <button type="button"
                                                    @click="kulaanConfirm({
                                                        title: 'Selesaikan Pengantaran?',
                                                        message: 'Konfirmasi seluruh barang telah sampai dan diterima dengan baik di lokasi UMKM?',
                                                        confirmText: 'Ya, Selesai',
                                                        cancelText: 'Kembali',
                                                        isDanger: false,
                                                        onConfirm: () => document.getElementById('complete-shipment-m-{{ $shipment->id }}').submit()
                                                    })"
                                                    class="w-full bg-brass text-white text-sm font-bold py-3 rounded-2xl cursor-pointer">
                                                Selesaikan Pengantaran
                                            </button>
                                        </form>
                                    @else
                                        <p class="text-xs font-bold text-success">Tiba &amp; selesai &mdash; makasih!</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="px-4 pb-6">{{ $shipments->links() }}</div>
        @else
            {{-- empty state --}}
            <div class="px-4 mt-4 pb-6">
                <section class="bg-white rounded-3xl border border-line px-6 py-12 text-center">
                    <span class="w-14 h-14 rounded-2xl bg-success/10 text-forest inline-flex items-center justify-center">
                        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 17h-2v-11a1 1 0 0 1 1-1h9v12m-8 0h8m4 0h2v-6l-3-4h-3v10"/><circle cx="7.5" cy="17.5" r="1.8"/><circle cx="17.5" cy="17.5" r="1.8"/></svg>
                    </span>
                    <p class="font-bold text-ink mt-4">Belum ada tugas pengiriman.</p>
                    <p class="text-[13px] text-muted mt-1.5 max-w-md mx-auto">Tugas baru muncul di sini setelah distributor menugaskanmu ke surat jalan. Santai dulu.</p>
                </section>
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
            <nav class="flex-1 px-4 py-6 space-y-1 text-sm font-semibold">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m2.25 12 8.95-8.95a1.13 1.13 0 0 1 1.6 0l8.95 8.95M4.5 9.75V19.5a.75.75 0 0 0 .75.75h13.5a.75.75 0 0 0 .75-.75V9.75"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('kurir.shipments.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-forest text-white">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 17h-2v-11a1 1 0 0 1 1-1h9v12m-8 0h8m4 0h2v-6l-3-4h-3v10"/><circle cx="7.5" cy="17.5" r="1.8"/><circle cx="17.5" cy="17.5" r="1.8"/></svg>
                    Daftar Tugas
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
                                        <span class="text-xs text-ink"><b>{{ $totalShipments }}</b> tugas pengiriman</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- forest band --}}
            <div class="bg-gradient-to-r from-forest-deep via-forest to-forest-dark text-white">
                <div class="px-8 py-12 max-w-6xl">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-bold bg-white/10 text-white/85 uppercase tracking-[0.14em]">Panel Kurir Armada</span>
                    <h1 class="text-4xl font-extrabold mt-3 tracking-tight">Tugas Pengiriman</h1>
                    <p class="text-sm text-white/70 mt-2 max-w-2xl">Tekan "Mulai Pengiriman" pas berangkat biar GPS-mu live di HP UMKM. Sampai tujuan? Tekan selesai.</p>
                </div>
            </div>

            <main class="px-8 py-8 max-w-6xl">
                {{-- GPS strip --}}
                <div class="rounded-3xl bg-success/10 border border-forest/15 p-5 flex items-start gap-4 mb-6">
                    <span class="w-11 h-11 rounded-full bg-white text-forest flex items-center justify-center shrink-0 border border-forest/15">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                    </span>
                    <p class="text-sm leading-relaxed text-forest"><strong>GPS otomatis aktif</strong> begitu kamu tekan "Mulai Pengiriman". Pastikan izin lokasi di browser/HP-mu nyala ya.</p>
                </div>

                @if ($hasShipments)
                    <div class="grid lg:grid-cols-2 gap-4">
                        @foreach ($shipments as $shipment)
                            @php
                                $isTransit = $shipment->status === 'IN_TRANSIT';
                                $isDelivered = $shipment->status === 'DELIVERED';
                                $isPending = $shipment->status === 'PENDING';
                            @endphp
                            <article
                                x-data="{
                                    isTransmitting: {{ $isTransit ? 'true' : 'false' }},
                                    watchId: null,
                                    startGps() {
                                        if ('geolocation' in navigator) {
                                            this.watchId = navigator.geolocation.watchPosition(pos => {
                                                axios.post('/kurir/shipments/{{ $shipment->id }}/gps', {
                                                    latitude: pos.coords.latitude,
                                                    longitude: pos.coords.longitude,
                                                    speed: pos.coords.speed || 0
                                                });
                                            }, err => console.warn(err), { enableHighAccuracy: true });
                                        }
                                    }
                                }"
                                x-init="if (isTransmitting && $el.offsetParent !== null) startGps();"
                                class="bg-white rounded-3xl p-6 {{ $isTransit ? 'border-2 border-forest/25' : 'border border-line' }} {{ $isDelivered ? 'opacity-90' : '' }}">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-mono font-bold text-ink">{{ $shipment->shipment_number }}</p>
                                        <p class="font-mono text-xs text-muted mt-0.5">{{ $shipment->order->order_number }}</p>
                                    </div>
                                    @if ($isTransit)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-success/10 text-success shrink-0">
                                            <span class="w-1.5 h-1.5 rounded-full bg-success animate-pulse"></span>GPS Live
                                        </span>
                                    @elseif ($isDelivered)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-forest text-white shrink-0">Selesai</span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-brass-soft text-brass shrink-0">{{ $isPending ? 'Menunggu' : $shipment->status }}</span>
                                    @endif
                                </div>
                                <div class="mt-4 space-y-2 text-sm">
                                    @foreach ($shipment->order->items as $item)
                                        <p><strong class="text-ink">{{ $item->umkm->business_name }}</strong> <span class="text-muted">&bull; {{ $item->delivery_address }} ({{ $item->delivery_district }})</span></p>
                                    @endforeach
                                </div>
                                <div class="mt-5">
                                    @if ($isPending)
                                        <form method="POST" action="{{ route('kurir.shipments.start', $shipment) }}">
                                            @csrf
                                            <button type="submit" class="w-full bg-forest text-white text-sm font-bold py-3.5 rounded-2xl cursor-pointer">
                                                Mulai Pengiriman
                                            </button>
                                        </form>
                                    @elseif ($isTransit)
                                        <form method="POST" action="{{ route('kurir.shipments.complete', $shipment) }}" id="complete-shipment-d-{{ $shipment->id }}">
                                            @csrf
                                            <button type="button"
                                                    @click="kulaanConfirm({
                                                        title: 'Selesaikan Pengantaran?',
                                                        message: 'Konfirmasi seluruh barang telah sampai dan diterima dengan baik di lokasi UMKM?',
                                                        confirmText: 'Ya, Selesai',
                                                        cancelText: 'Kembali',
                                                        isDanger: false,
                                                        onConfirm: () => document.getElementById('complete-shipment-d-{{ $shipment->id }}').submit()
                                                    })"
                                                    class="w-full bg-brass text-white text-sm font-bold py-3.5 rounded-2xl cursor-pointer">
                                                Selesaikan Pengantaran
                                            </button>
                                        </form>
                                    @else
                                        <p class="text-sm font-bold text-success">Tiba &amp; selesai &mdash; makasih!</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="pt-6">{{ $shipments->links() }}</div>
                @else
                    <section class="bg-white rounded-3xl border border-line px-6 py-16 text-center max-w-lg mx-auto">
                        <span class="w-14 h-14 rounded-2xl bg-success/10 text-forest inline-flex items-center justify-center">
                            <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 17h-2v-11a1 1 0 0 1 1-1h9v12m-8 0h8m4 0h2v-6l-3-4h-3v10"/><circle cx="7.5" cy="17.5" r="1.8"/><circle cx="17.5" cy="17.5" r="1.8"/></svg>
                        </span>
                        <p class="font-bold text-ink text-lg mt-4">Belum ada tugas pengiriman.</p>
                        <p class="text-sm text-muted mt-2 leading-relaxed max-w-sm mx-auto">Tugas baru muncul di sini setelah distributor menugaskanmu ke surat jalan. Santai dulu.</p>
                    </section>
                @endif
            </main>
        </div>
    </div>

    {{-- MOBILE BOTTOM TAB --}}
    <div class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-white border-t border-line px-2 pt-2 pb-5 flex items-start justify-around">
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 w-14 text-muted">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m2.25 12 8.95-8.95a1.13 1.13 0 0 1 1.6 0l8.95 8.95M4.5 9.75V19.5a.75.75 0 0 0 .75.75h13.5a.75.75 0 0 0 .75-.75V9.75"/></svg>
            <span class="text-[10px] font-semibold">Beranda</span>
        </a>
        <a href="{{ route('kurir.shipments.index') }}" class="flex flex-col items-center gap-1 w-14 text-forest">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 17h-2v-11a1 1 0 0 1 1-1h9v12m-8 0h8m4 0h2v-6l-3-4h-3v10"/><circle cx="7.5" cy="17.5" r="1.8"/><circle cx="17.5" cy="17.5" r="1.8"/></svg>
            <span class="text-[10px] font-bold">Tugas</span>
        </a>
        <a href="{{ route('profile.complete') }}" class="flex flex-col items-center gap-1 w-14 text-muted">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17.98 18.72a6 6 0 0 0-8.46 0M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm-9.75 8.25a9 9 0 1 1 13.5 0"/></svg>
            <span class="text-[10px] font-semibold">Akun</span>
        </a>
    </div>
</x-layouts.app>
