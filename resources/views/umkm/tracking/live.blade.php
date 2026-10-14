<x-layouts.app>
    <x-slot name="title">Peta Semua Kurir Aktif</x-slot>

    <!-- Leaflet Map Assets -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <div class="space-y-6 sm:space-y-8" x-data="{
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
                html: `<div class='w-10 h-10 rounded-full bg-brand-accent text-white border-2 border-white shadow-xl flex items-center justify-center font-bold text-sm ring-4 ring-brand-accent/20'>T</div>`,
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

        <!-- Header Bar -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <a href="{{ route('umkm.tracking.index') }}" class="btn-back mb-3">
                    &larr; Kembali ke Daftar Pengiriman
                </a>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-brand-dark tracking-tight">Peta Semua Kurir Aktif</h1>
                <p class="text-[0.8125rem] text-brand-slate mt-0.5">
                    Satu peta untuk seluruh pengiriman yang sedang berjalan menuju usaha Anda.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <div class="flex items-center gap-2.5 px-4 py-2 bg-white rounded-full border border-black/[0.06] shadow-sm">
                    <span class="w-2.5 h-2.5 rounded-full bg-brand-emerald"></span>
                    <span class="text-xs font-bold text-brand-dark"><span x-text="activeCount"></span> Kurir &bull; <span x-text="packageCount"></span> Paket</span>
                </div>
                <button type="button" @click="goHome()" class="btn-ghost text-xs py-2 px-4 font-bold">
                    Lokasi Saya
                </button>
                <button type="button" @click="fitAll()" class="btn-ghost text-xs py-2 px-4 font-bold">
                    Pusatkan Peta
                </button>
            </div>
        </div>

        <!-- Empty State -->
        <div x-show="!loading && activeCount === 0" x-cloak class="card-shell">
            <div class="card-core p-10 text-center space-y-2">
                <h2 class="font-bold text-brand-dark">Tidak ada pengiriman yang sedang berjalan</h2>
                <p class="text-[0.8125rem] text-brand-slate">Pengiriman akan muncul di peta ini otomatis setelah kurir memulai perjalanan.</p>
                <a href="{{ route('umkm.tracking.index') }}" class="btn-primary text-xs py-2 px-5 inline-flex font-bold mt-2">Lihat Daftar Pengiriman</a>
            </div>
        </div>

        <!-- Main Map & Courier List Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start" x-show="loading || activeCount > 0">
            <!-- Map Container (Double-Bezel) -->
            <div class="lg:col-span-8 card-shell">
                <div class="card-core p-2 sm:p-3 overflow-hidden relative">
                    <div id="allCouriersMap" class="w-full h-[420px] sm:h-[500px] rounded-xl z-10 border border-black/[0.04]"></div>
                    <div x-show="loading" x-cloak class="absolute inset-2 sm:inset-3 z-20 rounded-xl overflow-hidden p-4" style="background-color: var(--color-brand-cream);" aria-hidden="true">
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

            <!-- Active Couriers Panel (Double-Bezel) -->
            <div class="lg:col-span-4 card-shell">
                <div class="card-core p-5 sm:p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-black/[0.04]">
                        <h2 class="text-sm font-bold text-brand-dark uppercase tracking-wider">Kurir di Jalan</h2>
                        <span class="badge bg-brand-mint text-brand-primary text-[10px]">Live GPS</span>
                    </div>

                    <div class="space-y-3">
                        <template x-for="c in couriers" :key="c.id">
                            <div class="p-3.5 rounded-2xl border border-black/[0.05] bg-white space-y-2">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-3 h-3 rounded-full flex-shrink-0" :style="`background:${c.color}`"></span>
                                    <div class="min-w-0">
                                        <p class="font-bold text-brand-dark text-sm truncate" x-text="c.name"></p>
                                        <p class="text-[11px] text-brand-slate truncate" x-text="c.vehicle"></p>
                                    </div>
                                    <span class="ml-auto badge bg-brand-ochre-soft text-brand-brass text-[10px] whitespace-nowrap" x-text="`Membawa ${c.packageCount} paket`"></span>
                                </div>
                                <div class="text-[11px] text-brand-slate space-y-1.5">
                                    <template x-for="d in c.deliveries" :key="d.shipment_number">
                                        <div class="p-2 rounded-xl bg-brand-cream/60 border border-black/[0.03] flex justify-between items-center gap-2">
                                            <div class="min-w-0">
                                                <p class="font-mono font-bold text-brand-dark truncate" x-text="d.shipment_number"></p>
                                                <p class="truncate" x-text="d.products"></p>
                                            </div>
                                            <a :href="d.detail_url" class="min-h-11 inline-flex items-center text-brand-primary font-bold whitespace-nowrap hover:underline">Detail</a>
                                        </div>
                                    </template>
                                    <div class="flex justify-between pt-1">
                                        <span>Kecepatan: <strong class="text-brand-primary"><span x-text="c.speed"></span> km/jam</strong></span>
                                    </div>
                                    <p>Update: <span x-text="c.updated"></span></p>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div x-show="loading && couriers.length === 0" x-cloak class="space-y-3" aria-hidden="true">
                        <div class="p-3.5 rounded-2xl border border-black/[0.05] space-y-2.5">
                            <div class="flex items-center gap-2.5">
                                <div class="skeleton w-8 h-8 shrink-0" style="border-radius: 9999px;"></div>
                                <div class="flex-1 space-y-1.5">
                                    <div class="skeleton h-3.5 w-2/3"></div>
                                    <div class="skeleton h-3 w-1/2"></div>
                                </div>
                            </div>
                            <div class="skeleton h-12 w-full"></div>
                        </div>
                        <div class="p-3.5 rounded-2xl border border-black/[0.05] space-y-2.5">
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
        </div>
    </div>
</x-layouts.app>
