<x-layouts.app>
    <x-slot name="title">Pantau Live GPS Kurir</x-slot>

    <!-- Leaflet Map Assets -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    @php
        $displayName = $umkm?->business_name ?? 'UMKM';
        $initials = strtoupper(substr($umkm?->business_name ?? 'K', 0, 2));
        $cartCount = app(\App\Services\CartService::class)->count();
        $courierPhone = $shipment->courier?->user->phone;
        $courierInitial = strtoupper(substr($shipment->courier?->user->name ?? 'K', 0, 1));
        $orderStatus = $shipment->order->status->value;
        $step = match (true) {
            in_array($orderStatus, ['DELIVERED', 'COMPLETED'], true) => 4,
            $orderStatus === 'IN_TRANSIT' || $shipment->status === 'IN_TRANSIT' => 3,
            in_array($orderStatus, ['CONFIRMED', 'PREPARING', 'READY_TO_SHIP', 'ASSIGNED_TO_COURIER'], true) => 2,
            in_array($orderStatus, ['PAID'], true) => 1,
            default => 0,
        };
        $steps = ['Dipesan', 'Dibayar', 'Diproses', 'Dikirim', 'Sampai'];
    @endphp

    <div x-data="{
        shipmentStatus: '{{ $shipment->status }}',
        courierName: '{{ $shipment->courier?->user->name ?? 'Kurir Armada' }}',
        courierVehicle: '{{ $shipment->courier?->vehicle_type ?? 'Motor' }} ({{ $shipment->courier?->vehicle_number ?? '-' }})',
        courierPhone: '{{ $shipment->courier?->user->phone ?? '-' }}',
        lastUpdated: 'Baru saja',
        speed: {{ (float) ($shipment->latestLocation?->speed ?? 0) }},
        courierLat: {{ $shipment->latestLocation?->latitude ?? -3.328000 }},
        courierLng: {{ $shipment->latestLocation?->longitude ?? 114.580000 }},
        destLat: {{ $umkm->latitude ?? -3.298210 }},
        destLng: {{ $umkm->longitude ?? 114.588230 }},
        map: null,
        courierMarker: null,
        destMarker: null,
        routeLine: null,
        pollTimer: null,
        mapReady: false,

        initMap() {
            this.map = L.map('liveGpsMap', {
                zoomControl: true,
                attributionControl: false
            }).setView([this.courierLat, this.courierLng], 14);

            const tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19
            });
            tiles.on('load', () => { this.mapReady = true; });
            tiles.addTo(this.map);
            setTimeout(() => { this.mapReady = true; }, 6000);

            // Custom Courier Icon
            const courierIcon = L.divIcon({
                className: 'custom-courier-pin',
                html: `<div class='w-10 h-10 rounded-full bg-forest text-white border-2 border-white shadow-xl flex items-center justify-center font-bold text-sm ring-4 ring-forest/20'>K</div>`,
                iconSize: [40, 40],
                iconAnchor: [20, 20]
            });

            // Custom Destination Icon
            const destIcon = L.divIcon({
                className: 'custom-dest-pin',
                html: `<div class='w-10 h-10 rounded-full bg-brass text-white border-2 border-white shadow-xl flex items-center justify-center font-bold text-sm ring-4 ring-brass/20'>T</div>`,
                iconSize: [40, 40],
                iconAnchor: [20, 20]
            });

            this.courierMarker = L.marker([this.courierLat, this.courierLng], { icon: courierIcon })
                .addTo(this.map)
                .bindPopup(`<strong>Posisi Kurir:</strong><br>${this.courierName}<br>Kecepatan: ${this.speed} km/jam`)
                .openPopup();

            this.destMarker = L.marker([this.destLat, this.destLng], { icon: destIcon })
                .addTo(this.map)
                .bindPopup(`<strong>Tujuan Pengiriman:</strong><br>{{ $umkm->business_name }}<br>{{ $umkm->address }}`);

            // Fit both points, but clamp: if the pair is absurdly far apart
            // (bad coordinate data), frame the destination instead of a country-wide zoom.
            const courierPos = this.courierMarker.getLatLng();
            const destPos = this.destMarker.getLatLng();
            if (courierPos.distanceTo(destPos) > 100000) {
                this.map.setView([this.destLat, this.destLng], 14);
            } else {
                const group = new L.featureGroup([this.courierMarker, this.destMarker]);
                this.map.fitBounds(group.getBounds().pad(0.2));
            }

            // Start polling location every 4 seconds
            this.pollTimer = setInterval(() => this.fetchLocation(), 4000);
        },

        fetchLocation() {
            axios.get('{{ route('umkm.tracking.location', $shipment) }}').then(res => {
                const data = res.data;
                this.shipmentStatus = data.shipment_status;
                if (data.current_location) {
                    this.courierLat = data.current_location.lat;
                    this.courierLng = data.current_location.lng;
                    this.speed = data.current_location.speed || 0;
                    this.lastUpdated = data.current_location.updated_at || 'Baru saja';

                    this.courierMarker.setLatLng([this.courierLat, this.courierLng]);
                    this.courierMarker.setPopupContent(`<strong>Posisi Kurir:</strong><br>${this.courierName}<br>Kecepatan: ${this.speed} km/jam<br><small>Update: ${this.lastUpdated}</small>`);
                }
            }).catch(err => console.warn('Polling GPS:', err));
        },

        goHome() {
            this.map.setView([this.destLat, this.destLng], 14);
        }
    }"     x-init="initMap()">

        <div class="lg:flex min-h-screen">
            {{-- DESKTOP SIDEBAR --}}
            <aside class="hidden lg:flex w-64 shrink-0 bg-white border-r border-line flex-col sticky top-0 h-screen">
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
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg>
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
                {{-- DESKTOP TOPBAR --}}
                <header class="hidden lg:block bg-white/70 backdrop-blur border-b border-line sticky top-0 z-10">
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
                            <a href="{{ route('umkm.tracking.index') }}" class="w-11 h-11 rounded-2xl bg-white border border-line flex items-center justify-center relative">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14.86 17.08a23.85 23.85 0 0 0 5.45-1.31A8.97 8.97 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.97 8.97 0 0 1-2.31 6.02c1.73.64 3.56 1.09 5.45 1.31m5.72 0a24.26 24.26 0 0 1-5.72 0m5.72 0a3 3 0 1 1-5.72 0"/></svg>
                            </a>
                        </div>
                    </div>
                </header>

                {{-- MOBILE HEADER --}}
                <div class="lg:hidden px-4 pt-6 flex items-center justify-between">
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
                    <a href="{{ route('umkm.tracking.index') }}" class="w-10 h-10 rounded-2xl bg-white border border-line flex items-center justify-center text-ink relative">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14.86 17.08a23.85 23.85 0 0 0 5.45-1.31A8.97 8.97 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.97 8.97 0 0 1-2.31 6.02c1.73.64 3.56 1.09 5.45 1.31m5.72 0a24.26 24.26 0 0 1-5.72 0m5.72 0a3 3 0 1 1-5.72 0"/></svg>
                    </a>
                </div>

                <main class="px-4 lg:px-8 py-4 lg:py-6 lg:max-w-6xl pb-24 lg:pb-6">
                    {{-- HEADER --}}
                    <div class="flex items-start justify-between gap-4 flex-wrap mb-5">
                        <div>
                            <a href="{{ route('umkm.tracking.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted mb-1 lg:mb-2">&larr; Daftar Pengiriman</a>
                            <h1 class="text-xl lg:text-3xl font-extrabold text-ink tracking-tight">Live GPS Kurir</h1>
                            <p class="text-xs lg:text-sm text-muted mt-0.5 lg:mt-1">
                                Surat jalan <span class="font-mono font-bold text-ink">{{ $shipment->shipment_number }}</span>
                                &bull; Pesanan <span class="font-mono">{{ $shipment->order->order_number }}</span>
                            </p>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center gap-2 px-3 py-2 lg:px-4 bg-white rounded-full border border-line text-xs font-bold text-ink lg:shadow-sm">
                                <span class="w-2.5 h-2.5 rounded-full" :class="shipmentStatus === 'IN_TRANSIT' ? 'bg-success' : (shipmentStatus === 'DELIVERED' ? 'bg-forest' : 'bg-brass')"></span>
                                <span x-text="shipmentStatus === 'IN_TRANSIT' ? 'Kurir Sedang Bergerak' : (shipmentStatus === 'DELIVERED' ? 'Pesanan Telah Tiba' : 'Menunggu Pengambilan')"></span>
                            </span>
                            <button type="button" @click="goHome()" class="text-xs font-bold text-muted border border-line bg-white rounded-full px-3 py-2 lg:px-4">Ke Lokasi Saya</button>
                        </div>
                    </div>

                    {{-- TIMELINE STEPPER --}}
                    <ol class="bg-white rounded-2xl border border-line px-3 lg:px-6 py-4 lg:py-5 flex items-start mb-4 lg:mb-6 shadow-sm" aria-label="Status pengiriman">
                        @foreach ($steps as $i => $label)
                            <li class="flex-1 flex flex-col items-center gap-1.5 lg:gap-2 min-w-0 {{ $i < count($steps) - 1 ? 'relative' : '' }}">
                                <span class="w-7 h-7 lg:w-8 lg:h-8 rounded-full flex items-center justify-center text-xs lg:text-sm font-extrabold shrink-0 {{ $i < $step ? 'bg-forest text-white' : ($i === $step ? 'bg-brass text-ink ring-4 ring-brass/20' : 'bg-cream text-muted border border-line') }}">
                                    @if ($i < $step)
                                        <svg class="w-3.5 h-3.5 lg:w-4 lg:h-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    @else
                                        {{ $i + 1 }}
                                    @endif
                                </span>
                                <span class="text-[9px] lg:text-xs font-bold text-center leading-tight {{ $i <= $step ? 'text-ink' : 'text-muted/60' }}">{{ $label }}</span>
                                @if ($i < count($steps) - 1)
                                    <span class="hidden sm:block absolute top-3.5 lg:top-4 left-[calc(50%+1.25rem)] right-[calc(-50%+1.25rem)] h-0.5 rounded {{ $i < $step ? 'bg-forest' : 'bg-line' }}" aria-hidden="true"></span>
                                @endif
                            </li>
                        @endforeach
                    </ol>

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                        {{-- MAP --}}
                        <div class="lg:col-span-8">
                            <div class="relative rounded-2xl overflow-hidden border border-line shadow-sm" style="background:#ECE8DA;">
                                <div id="liveGpsMap" class="w-full h-[240px] lg:h-[480px] rounded-2xl z-10"></div>
                                <div x-show="!mapReady" x-cloak class="absolute inset-0 z-20 rounded-2xl overflow-hidden p-4 bg-cream" aria-hidden="true">
                                    <div class="skeleton absolute inset-0" style="border-radius: 0;"></div>
                                    <div class="absolute top-4 left-4">
                                        <div class="skeleton h-9 w-36"></div>
                                    </div>
                                    <div class="absolute bottom-4 left-4 right-4 space-y-2">
                                        <div class="skeleton h-4 w-1/2"></div>
                                        <div class="skeleton h-4 w-2/3"></div>
                                    </div>
                                </div>
                                <span class="pointer-events-none absolute top-3 left-3 lg:top-4 lg:left-4 inline-flex items-center gap-1.5 text-[10px] lg:text-xs font-bold bg-white/95 border border-line rounded-full px-2.5 lg:px-3 py-1 lg:py-1.5 text-ink shadow-sm z-20">
                                    <span class="w-2 h-2 rounded-full bg-success animate-pulse"></span>
                                    <span class="lg:hidden">Live</span>
                                    <span class="hidden lg:inline">Live GPS &bull; update tiap 4 detik</span>
                                </span>
                                <span class="pointer-events-none absolute bottom-3 right-3 lg:bottom-4 lg:right-4 text-[10px] lg:text-xs font-bold bg-ink/70 text-white rounded-full px-2.5 lg:px-3 py-1 lg:py-1.5 z-20"><span x-text="speed"></span> km/jam</span>
                            </div>
                        </div>

                        {{-- SIDE PANELS --}}
                        <div class="lg:col-span-4 space-y-4">
                            {{-- COURIER CARD --}}
                            <div class="bg-white rounded-2xl border border-line p-4 lg:p-6 shadow-sm">
                                <h2 class="text-xs font-bold text-ink uppercase tracking-wider mb-3 lg:mb-4">Kurir Ditugaskan</h2>
                                <div class="flex items-center gap-3">
                                    <span class="w-12 h-12 lg:w-14 lg:h-14 rounded-2xl bg-forest text-white flex items-center justify-center font-extrabold text-lg lg:text-xl shrink-0">{{ $courierInitial }}</span>
                                    <div class="min-w-0">
                                        <h3 class="font-bold text-sm lg:text-base text-ink" x-text="courierName"></h3>
                                        <p class="text-xs text-muted" x-text="courierVehicle"></p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-2 mt-3 lg:mt-4 text-xs">
                                    <div class="bg-cream/60 rounded-xl p-2.5 lg:p-3 border border-line/60">
                                        <p class="text-[10px] text-muted">No. Telepon</p>
                                        <p class="font-bold text-ink mt-0.5" x-text="courierPhone"></p>
                                    </div>
                                    <div class="bg-cream/60 rounded-xl p-2.5 lg:p-3 border border-line/60">
                                        <p class="text-[10px] text-muted">Kecepatan</p>
                                        <p class="font-bold text-ink mt-0.5"><span x-text="speed"></span> km/jam</p>
                                    </div>
                                </div>
                                <p class="text-[11px] text-muted mt-2 lg:mt-3">Update terakhir: <span class="font-semibold text-ink" x-text="lastUpdated"></span></p>
                                @if ($courierPhone)
                                    <a href="tel:{{ $courierPhone }}" class="mt-3 lg:mt-4 w-full inline-flex items-center justify-center gap-2 bg-forest text-white text-sm font-bold py-3 lg:py-3.5 rounded-xl">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2.25 6.75c0 8.28 6.72 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.37c0-.5-.28-.96-.73-1.19l-2.5-1.25a1.13 1.13 0 0 0-1.28.21l-.82.82a16.5 16.5 0 0 1-7.07-7.07l.82-.82a1.13 1.13 0 0 0 .21-1.28L9.13 4.06a1.13 1.13 0 0 0-1.19-.73H6.75A2.25 2.25 0 0 0 4.5 5.58v1.17Z"/></svg>
                                        Hubungi Kurir
                                    </a>
                                @endif
                            </div>

                            {{-- DAFTAR ISI PENGIRIMAN --}}
                            <div class="hidden lg:block bg-white rounded-2xl border border-line p-6 shadow-sm">
                                <h2 class="text-xs font-bold text-ink uppercase tracking-wider mb-3">Isi Pengiriman</h2>
                                <div class="space-y-2 text-sm">
                                    @forelse ($shipment->order->items->where('umkm_id', $umkm->id) as $item)
                                        <div class="flex justify-between gap-2">
                                            <span class="text-muted">{{ $item->product->name }}</span>
                                            <span class="font-bold text-ink">{{ number_format($item->quantity, 0) }} {{ $item->product->unit }}</span>
                                        </div>
                                    @empty
                                        <p class="text-sm text-muted">Belum ada rincian barang.</p>
                                    @endforelse
                                    <div class="flex justify-between gap-2 pt-2 border-t border-line/60">
                                        <span class="text-muted">Distributor</span>
                                        <span class="font-bold text-ink">{{ $shipment->order->distributor->company_name }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>

        {{-- MOBILE BOTTOM TAB --}}
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
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg>
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
    </div>
</x-layouts.app>
