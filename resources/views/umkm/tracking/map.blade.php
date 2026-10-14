<x-layouts.app>
    <x-slot name="title">Pantau Live GPS Kurir</x-slot>

    <!-- Leaflet Map Assets -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <div class="space-y-6 sm:space-y-8" x-data="{
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
                html: `<div class='w-10 h-10 rounded-full bg-brand-primary text-white border-2 border-white shadow-xl flex items-center justify-center font-bold text-sm ring-4 ring-brand-primary/20'>K</div>`,
                iconSize: [40, 40],
                iconAnchor: [20, 20]
            });

            // Custom Destination Icon
            const destIcon = L.divIcon({
                className: 'custom-dest-pin',
                html: `<div class='w-10 h-10 rounded-full bg-brand-accent text-white border-2 border-white shadow-xl flex items-center justify-center font-bold text-sm ring-4 ring-brand-accent/20'>T</div>`,
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

        <!-- Header Bar -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
            <div>
                <a href="{{ route('umkm.tracking.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-slate hover:text-brand-primary transition-colors">
                    &larr; Daftar Pengiriman
                </a>
                <h1 class="font-display font-bold text-xl sm:text-2xl text-brand-dark tracking-tight mt-1">Live GPS Kurir</h1>
                <p class="text-xs text-brand-slate mt-0.5">
                    Surat jalan <span class="font-mono font-bold text-brand-dark">{{ $shipment->shipment_number }}</span>
                    &bull; Pesanan <span class="font-mono">{{ $shipment->order->order_number }}</span>
                </p>
            </div>

            <div class="flex items-center gap-2">
                <div class="flex items-center gap-2 px-3 py-2 bg-white rounded-full border border-brand-line shadow-xs text-xs font-bold text-brand-dark">
                    <span class="w-2.5 h-2.5 rounded-full" :class="shipmentStatus === 'IN_TRANSIT' ? 'bg-brand-emerald' : (shipmentStatus === 'DELIVERED' ? 'bg-brand-primary' : 'bg-brand-accent')"></span>
                    <span x-text="shipmentStatus === 'IN_TRANSIT' ? 'Kurir Sedang Bergerak' : (shipmentStatus === 'DELIVERED' ? 'Pesanan Telah Tiba' : 'Menunggu Pengambilan')"></span>
                </div>
                <button type="button" @click="goHome()" class="btn-ghost text-xs py-2 px-3 font-bold rounded-full">
                    Ke Lokasi Saya
                </button>
            </div>
        </div>

        <!-- Timeline Progress Stepper (Horizontal, Bersih) -->
        @php
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
        <ol class="bg-white rounded-2xl border border-brand-line px-4 sm:px-6 py-4 flex items-start mb-5 shadow-xs" aria-label="Status pengiriman">
            @foreach ($steps as $i => $label)
                <li class="flex-1 flex flex-col items-center gap-1.5 min-w-0 {{ $i < count($steps) - 1 ? 'relative' : '' }}">
                    <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-extrabold shrink-0 {{ $i < $step ? 'bg-brand-primary text-white' : ($i === $step ? 'bg-brand-accent text-brand-dark ring-4 ring-brand-accent/20' : 'bg-brand-cream text-brand-slate border border-brand-line') }}">
                        @if ($i < $step)
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        @else
                            {{ $i + 1 }}
                        @endif
                    </span>
                    <span class="text-[0.5625rem] sm:text-[0.625rem] font-bold text-center leading-tight {{ $i <= $step ? 'text-brand-dark' : 'text-brand-slate/60' }}">{{ $label }}</span>
                    @if ($i < count($steps) - 1)
                        <span class="hidden sm:block absolute top-3.5 left-[calc(50%+1.25rem)] right-[calc(-50%+1.25rem)] h-0.5 rounded {{ $i < $step ? 'bg-brand-primary' : 'bg-brand-line' }}" aria-hidden="true"></span>
                    @endif
                </li>
            @endforeach
        </ol>

        <!-- Pola E: Peta Dominan (70%) + Panel Info Kurir (30%) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
            <!-- Peta (Dominan 8 kolom / 65%) -->
            <div class="lg:col-span-8 card-shell">
                <div class="card-core p-2 sm:p-3 overflow-hidden relative">
                    <div id="liveGpsMap" class="w-full h-[55vh] sm:h-[65vh] rounded-xl z-10 border border-black/[0.04]"></div>
                    <div x-show="!mapReady" x-cloak class="absolute inset-2 sm:inset-3 z-20 rounded-xl overflow-hidden p-4" style="background-color: var(--color-brand-cream);" aria-hidden="true">
                        <div class="skeleton absolute inset-0" style="border-radius: 0;"></div>
                        <div class="absolute top-4 left-4">
                            <div class="skeleton h-9 w-36"></div>
                        </div>
                        <div class="absolute bottom-4 left-4 right-4 space-y-2">
                            <div class="skeleton h-4 w-1/2"></div>
                            <div class="skeleton h-4 w-2/3"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Kurir & Pesanan (4 kolom / 35%) -->
            <div class="lg:col-span-4 space-y-4">
                <!-- Kartu Kurir -->
                <div class="bg-white rounded-2xl border border-brand-line p-5 shadow-xs">
                    <div class="flex items-center justify-between pb-3 border-b border-brand-line mb-4">
                        <h2 class="text-xs font-bold text-brand-dark uppercase tracking-wider">Kurir Ditugaskan</h2>
                    </div>

                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-brand-primary text-white font-extrabold flex items-center justify-center text-lg shadow-sm">
                            {{ strtoupper(substr($shipment->courier?->user->name ?? 'K', 0, 1)) }}
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-brand-dark" x-text="courierName"></h3>
                            <p class="text-xs text-brand-slate" x-text="courierVehicle"></p>
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-brand-cream/60 rounded-xl text-xs space-y-2 border border-brand-line/60">
                        <div class="flex justify-between">
                            <span class="text-brand-slate">No. WhatsApp</span>
                            <span class="font-bold text-brand-dark" x-text="courierPhone"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-brand-slate">Kecepatan</span>
                            <span class="font-bold text-brand-primary"><span x-text="speed"></span> km/jam</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-brand-slate">Update Terakhir</span>
                            <span class="font-medium text-brand-dark" x-text="lastUpdated"></span>
                        </div>
                    </div>
                </div>

                <!-- Bahan Baku yang Diantar -->
                <div class="bg-white rounded-2xl border border-brand-line p-5 shadow-xs space-y-3">
                    <span class="text-[0.6875rem] font-bold text-brand-slate uppercase tracking-wider block">Bahan Baku Diantar</span>
                    @foreach ($shipment->order->items->where('umkm_id', $umkm->id) as $item)
                        <div class="p-3 bg-brand-mint/40 rounded-xl flex justify-between items-center text-xs">
                            <div>
                                <p class="font-bold text-brand-dark">{{ $item->product->name }}</p>
                                <p class="text-[11px] text-brand-slate">{{ $shipment->order->distributor->company_name }}</p>
                            </div>
                            <span class="font-extrabold text-brand-primary">{{ number_format($item->quantity, 0) }} {{ $item->product->unit }}</span>
                        </div>
                    @endforeach

                    <div class="pt-3 border-t border-brand-line/60 text-xs space-y-1">
                        <span class="text-[0.6875rem] font-bold text-brand-slate uppercase tracking-wider block">Tujuan Pengiriman</span>
                        <p class="font-bold text-brand-dark">{{ $umkm->business_name }}</p>
                        <p class="text-brand-slate text-[0.6875rem] leading-relaxed">{{ $umkm->address }}, {{ $umkm->district }}</p>
                    </div>
                </div>

                <div class="p-4 bg-brand-mint/60 rounded-2xl text-xs text-brand-primary border border-brand-primary/15 flex items-start gap-2">
                    <span class="w-2 h-2 rounded-full bg-brand-emerald mt-0.5 shrink-0"></span>
                    <p class="leading-relaxed">Koordinat dikirimkan langsung dari perangkat kurir untuk transparansi penuh.</p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
