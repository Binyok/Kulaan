<x-layouts.app>
    <x-slot name="title">Peta Semua Kurir Aktif</x-slot>

    <!-- Leaflet Map Assets -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    @php
        $displayName = $umkm?->business_name ?? 'UMKM Anda';
        $initials = strtoupper(substr($displayName, 0, 2));
        $cartCount = app(\App\Services\CartService::class)->count();
    @endphp

    <div class="lg:flex min-h-screen" x-data="{
        colors: ['#2D6A4F', '#D4A017', '#2563EB', '#7C3AED', '#DC2626', '#0891B2'],
        map: null,
        markers: {},
        couriers: [],
        activeCount: 0,
        packageCount: 0,
        loading: true,
        fittedOnce: false,
        destLat: {{ $umkm->latitude ?? -3.298210 }},
        destLng: {{ $umkm->longitude ?? 114.588230 }},

        init() {
            this.map = L.map('allCouriersMap', {
                zoomControl: true,
                attributionControl: false
            }).setView([this.destLat, this.destLng], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19
            }).addTo(this.map);

            const destIcon = L.divIcon({
                className: 'custom-dest-pin',
                html: `<div class='w-10 h-10 rounded-full bg-brass text-white border-2 border-white shadow-xl flex items-center justify-center font-bold text-sm ring-4 ring-brass/20'>T</div>`,
                iconSize: [40, 40],
                iconAnchor: [20, 20]
            });

            L.marker([this.destLat, this.destLng], { icon: destIcon })
                .addTo(this.map)
                .bindPopup(`<strong>Tujuan Pengiriman:</strong><br>{{ $umkm->business_name }}<br>{{ $umkm->address }}`);

            this.fetchActive();
            setInterval(() => this.fetchActive(), 4000);
        },

        colorFor(slot) {
            return this.colors[slot % this.colors.length];
        },

        courierIcon(color) {
            return L.divIcon({
                className: 'custom-courier-pin',
                html: `<div style='background:${color}' class='w-10 h-10 rounded-full text-white border-2 border-white shadow-xl flex items-center justify-center font-bold text-sm'>K</div>`,
                iconSize: [40, 40],
                iconAnchor: [20, 20]
            });
        },

        popupHtml(entry, item) {
            const loc = item.current_location;
            const packages = item.deliveries.map(d => d.shipment_number).join(', ');
            return `<strong>${entry.name}</strong><br>Membawa ${item.deliveries.length} paket (${packages})<br>` +
                (loc ? `Kecepatan: ${loc.speed} km/jam<br><small>Update: ${loc.updated_at}</small>` : `<small>Menunggu titik GPS pertama…</small>`);
        },

        fetchActive() {
            axios.get('{{ route('umkm.tracking.active-locations') }}').then(res => {
                const list = res.data.couriers || [];
                this.loading = false;
                this.activeCount = list.length;
                this.packageCount = list.reduce((sum, c) => sum + (c.deliveries ? c.deliveries.length : 0), 0);

                const seenIds = new Set();
                const panel = [];

                list.forEach((item) => {
                    const key = String(item.courier_id);
                    seenIds.add(key);

                    if (!this.markers[key]) {
                        const slot = Object.keys(this.markers).length;
                        const color = this.colorFor(slot);
                        const startAt = item.current_location
                            ? [item.current_location.lat, item.current_location.lng]
                            : [this.destLat, this.destLng];

                        this.markers[key] = {
                            color: color,
                            marker: L.marker(startAt, { icon: this.courierIcon(color) }).addTo(this.map)
                        };
                    }

                    const entry = this.markers[key];
                    entry.name = item.courier.name;
                    if (item.current_location) {
                        entry.marker.setLatLng([item.current_location.lat, item.current_location.lng]);
                    }
                    entry.marker.setPopupContent(this.popupHtml(entry, item));

                    panel.push({
                        id: key,
                        color: entry.color,
                        name: item.courier.name,
                        vehicle: item.courier.vehicle,
                        phone: item.courier.phone,
                        speed: item.current_location ? item.current_location.speed : 0,
                        updated: item.current_location ? item.current_location.updated_at : 'Belum ada sinyal GPS',
                        packageCount: item.deliveries.length,
                        deliveries: item.deliveries.map(d => ({
                            shipment_number: d.shipment_number,
                            distributor: d.distributor,
                            products: (d.products || []).join(', '),
                            detail_url: d.detail_url
                        }))
                    });
                });

                Object.keys(this.markers).forEach((id) => {
                    if (!seenIds.has(id)) {
                        this.map.removeLayer(this.markers[id].marker);
                        delete this.markers[id];
                    }
                });

                this.couriers = panel;

                if (!this.fittedOnce && list.length > 0) {
                    this.fittedOnce = true;
                    this.fitAll();
                }
            }).catch(err => console.warn('Polling GPS gabungan:', err));
        },

        fitAll() {
            const layers = Object.values(this.markers).map(e => e.marker);
            if (layers.length === 0) {
                return;
            }
            const bounds = new L.featureGroup(layers).getBounds().pad(0.25);
            // Clamp: if markers are absurdly spread out (bad coordinate data),
            // frame the destination instead of zooming out to a country view.
            if (bounds.getSouthWest().distanceTo(bounds.getNorthEast()) > 100000) {
                this.map.setView([this.destLat, this.destLng], 13);
            } else {
                this.map.fitBounds(bounds);
            }
        },

        goHome() {
            this.map.setView([this.destLat, this.destLng], 14);
        }
    }">

        {{-- SIDEBAR (DESKTOP) --}}
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
            {{-- MOBILE GREETING HEADER --}}
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
                                <span class="text-xs text-ink"><b>{{ $activeShipments->count() }}</b> pengiriman sedang berjalan</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DESKTOP TOPBAR --}}
            <header class="hidden lg:flex items-center gap-4 px-8 h-[72px] bg-white/70 backdrop-blur border-b border-line sticky top-0 z-10">
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
                                    <span class="text-xs text-ink"><b>{{ $activeShipments->count() }}</b> pengiriman sedang berjalan</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="px-4 pt-4 pb-24 lg:px-8 lg:py-8">
                {{-- HEADER --}}
                <div class="flex items-start justify-between gap-4 flex-wrap mb-6">
                    <div>
                        <a href="{{ route('umkm.tracking.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted hover:text-ink mb-2">&larr; Kembali ke Daftar Pengiriman</a>
                        <h1 class="text-2xl lg:text-3xl font-extrabold text-ink tracking-tight">Peta Semua Kurir Aktif</h1>
                        <p class="text-xs lg:text-sm text-muted mt-0.5 lg:mt-1">Satu peta untuk seluruh pengiriman yang sedang berjalan menuju usaha Anda.</p>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="inline-flex items-center gap-2 px-3 lg:px-4 py-2 bg-white rounded-full border border-line text-xs font-bold text-ink shadow-sm">
                            <span class="w-2.5 h-2.5 rounded-full bg-success"></span>
                            <span x-text="activeCount"></span> Kurir &bull; <span x-text="packageCount"></span> Paket
                        </span>
                        <button type="button" @click="goHome()" class="text-xs font-bold text-muted border border-line bg-white rounded-full px-3 lg:px-4 py-2 cursor-pointer">Lokasi Saya</button>
                        <button type="button" @click="fitAll()" class="text-xs font-bold text-muted border border-line bg-white rounded-full px-3 lg:px-4 py-2 cursor-pointer">Pusatkan Peta</button>
                    </div>
                </div>

                {{-- EMPTY STATE --}}
                <div x-show="!loading && activeCount === 0" x-cloak class="bg-white rounded-2xl border border-line p-10 text-center shadow-sm">
                    <h2 class="font-bold text-ink">Tidak ada pengiriman yang sedang berjalan</h2>
                    <p class="text-[0.8125rem] text-muted mt-1">Pengiriman akan muncul di peta ini otomatis setelah kurir memulai perjalanan.</p>
                    <a href="{{ route('umkm.tracking.index') }}" class="inline-flex mt-4 bg-forest text-white text-xs font-bold px-5 py-2.5 rounded-xl">Lihat Daftar Pengiriman</a>
                </div>

                {{-- MAP & COURIER PANEL --}}
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start" x-show="loading || activeCount > 0">
                    {{-- MAP --}}
                    <div class="lg:col-span-8">
                        <div class="relative rounded-2xl overflow-hidden border border-line shadow-sm">
                            <div id="allCouriersMap" class="w-full h-[260px] lg:h-[520px] rounded-xl z-10 border border-black/[0.04]"></div>
                            <span class="absolute top-3 lg:top-4 right-3 lg:right-4 z-20 inline-flex items-center gap-1.5 text-[10px] lg:text-xs font-bold bg-white/95 border border-line rounded-full px-2.5 lg:px-3 py-1 lg:py-1.5 text-ink shadow-sm pointer-events-none">
                                <span class="w-2 h-2 rounded-full bg-success animate-pulse"></span> Live GPS<span class="hidden lg:inline"> &bull; update tiap 4 detik</span>
                            </span>
                            <div x-show="loading" x-cloak class="absolute inset-0 z-30 rounded-2xl overflow-hidden p-4" style="background-color: var(--color-cream);" aria-hidden="true">
                                <div class="skeleton absolute inset-0" style="border-radius: 0;"></div>
                                <div class="absolute top-4 left-4 right-4 flex justify-between gap-3">
                                    <div class="skeleton h-9 w-40"></div>
                                    <div class="skeleton h-9 w-24"></div>
                                </div>
                                <div class="absolute bottom-4 left-4 right-4 space-y-2">
                                    <div class="skeleton h-4 w-2/3"></div>
                                    <div class="skeleton h-4 w-1/2"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- COURIER PANEL --}}
                    <div class="lg:col-span-4 bg-white rounded-2xl border border-line p-4 lg:p-6 shadow-sm">
                        <div class="flex items-center justify-between pb-3 border-b border-line/60 mb-4">
                            <h2 class="text-xs lg:text-sm font-bold text-ink uppercase tracking-wider">Kurir di Jalan</h2>
                            <span class="text-[10px] font-bold bg-success/10 text-success px-2 py-0.5 rounded-full">Live GPS</span>
                        </div>

                        <div class="space-y-3 lg:space-y-4">
                            <template x-for="c in couriers" :key="c.id">
                                <div class="p-3.5 lg:p-4 rounded-2xl border border-line bg-cream/40">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-3 h-3 rounded-full shrink-0" :style="`background:${c.color}`"></span>
                                        <div class="min-w-0">
                                            <p class="font-bold text-ink text-sm truncate" x-text="c.name"></p>
                                            <p class="text-[11px] text-muted truncate">
                                                <span x-text="c.vehicle"></span><span class="hidden lg:inline"> &bull; <span x-text="c.phone"></span></span>
                                            </p>
                                        </div>
                                        <span class="ml-auto text-[10px] font-bold bg-brass-soft text-brass px-2 py-0.5 rounded-full whitespace-nowrap" x-text="`Membawa ${c.packageCount} paket`"></span>
                                    </div>
                                    <div class="mt-2 lg:mt-2.5 space-y-1.5">
                                        <template x-for="d in c.deliveries" :key="d.shipment_number">
                                            <div class="p-2 lg:p-2.5 rounded-xl bg-white border border-line/60 flex justify-between items-center gap-2">
                                                <div class="min-w-0">
                                                    <p class="font-mono text-[11px] lg:text-xs font-bold text-ink truncate" x-text="d.shipment_number"></p>
                                                    <p class="text-[11px] text-muted truncate" x-text="d.products"></p>
                                                </div>
                                                <a :href="d.detail_url" class="min-h-11 inline-flex items-center text-[11px] lg:text-xs text-forest font-bold whitespace-nowrap hover:underline">Detail</a>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="flex justify-between mt-2 lg:mt-2.5 text-[11px] text-muted">
                                        <span>Kecepatan: <strong class="text-forest"><span x-text="c.speed"></span> km/jam</strong></span>
                                        <span>Update: <span x-text="c.updated"></span></span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div x-show="loading && couriers.length === 0" x-cloak class="space-y-3 lg:space-y-4" aria-hidden="true">
                            <div class="p-3.5 rounded-2xl border border-line space-y-2.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="skeleton w-8 h-8 shrink-0" style="border-radius: 9999px;"></div>
                                    <div class="flex-1 space-y-1.5">
                                        <div class="skeleton h-3.5 w-2/3"></div>
                                        <div class="skeleton h-3 w-1/2"></div>
                                    </div>
                                </div>
                                <div class="skeleton h-12 w-full"></div>
                            </div>
                            <div class="p-3.5 rounded-2xl border border-line space-y-2.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="skeleton w-8 h-8 shrink-0" style="border-radius: 9999px;"></div>
                                    <div class="flex-1 space-y-1.5">
                                        <div class="skeleton h-3.5 w-1/2"></div>
                                        <div class="skeleton h-3 w-2/3"></div>
                                    </div>
                                </div>
                                <div class="skeleton h-12 w-full"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    {{-- BOTTOM TAB (MOBILE) --}}
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
