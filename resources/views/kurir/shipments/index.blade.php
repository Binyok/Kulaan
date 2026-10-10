<x-layouts.app>
    <x-slot name="title">Tugas Pengiriman Kurir</x-slot>

    <div class="space-y-6 sm:space-y-8">
        <div>
            <span class="eyebrow">Panel Kurir Armada</span>
            <h1 class="font-display font-semibold text-[1.75rem] sm:text-4xl text-brand-dark tracking-tight text-balance mt-2">Daftar Tugas Pengiriman</h1>
            <p class="text-[0.8125rem] text-brand-slate mt-1">Kelola status perjalanan pengiriman dan transmisi koordinat GPS realtime.</p>
        </div>

        <!-- Info GPS Box -->
        <div class="p-4 bg-brand-mint/60 rounded-2xl border border-brand-primary/15 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 text-xs text-brand-primary">
            <p class="font-semibold">Sistem GPS otomatis aktif saat tombol 'Mulai Pengiriman' ditekan di smartphone / browser Anda.</p>
            <span class="font-mono text-[11px] bg-white px-2.5 py-1 rounded-md border border-brand-primary/20 self-start sm:self-auto shrink-0">WITA Banjarmasin</span>
        </div>

        @php $hasShipments = $shipments->isNotEmpty(); @endphp

        <!-- Shipments -->
        @if ($hasShipments)
            <div class="card-shell">
                <div class="card-core p-4 sm:p-7 space-y-5">
                    <!-- Desktop: table -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="table-premium">
                            <thead>
                                <tr>
                                    <th>No. Surat Jalan</th>
                                    <th>No. Pesanan</th>
                                    <th>Tujuan Pengiriman UMKM</th>
                                    <th class="text-center">Status GPS</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($shipments as $shipment)
                                    <tr x-data="{
                                        isTransmitting: {{ $shipment->status === 'IN_TRANSIT' ? 'true' : 'false' }},
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
                                    }" x-init="if (isTransmitting) startGps();">
                                        <td class="font-mono font-bold text-brand-dark">{{ $shipment->shipment_number }}</td>
                                        <td class="font-mono text-xs text-brand-slate">{{ $shipment->order->order_number }}</td>
                                        <td>
                                            <div class="space-y-1">
                                                @foreach ($shipment->order->items as $item)
                                                    <div class="text-xs">
                                                        <span class="font-bold text-brand-dark">{{ $item->umkm->business_name }}</span>
                                                        <div class="text-brand-slate truncate max-w-xs">{{ $item->delivery_address }} ({{ $item->delivery_district }})</div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $shipment->status === 'IN_TRANSIT' ? 'bg-brand-mint text-brand-emerald' : ($shipment->status === 'DELIVERED' ? 'bg-brand-primary text-white' : 'bg-brand-cream text-brand-slate') }}">
                                                {{ $shipment->status === 'IN_TRANSIT' ? 'GPS Live Mengirim' : ($shipment->status === 'DELIVERED' ? 'Selesai' : $shipment->status) }}
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            @if ($shipment->status === 'PENDING')
                                                <form method="POST" action="{{ route('kurir.shipments.start', $shipment) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="btn-primary text-xs py-2 px-4 font-semibold shadow-sm">
                                                        Mulai Pengiriman
                                                    </button>
                                                </form>
                                            @elseif ($shipment->status === 'IN_TRANSIT')
                                                <form method="POST" action="{{ route('kurir.shipments.complete', $shipment) }}" class="inline" onsubmit="return confirm('Konfirmasi seluruh barang telah sampai di lokasi UMKM?');">
                                                    @csrf
                                                    <button type="submit" class="btn-accent text-xs py-2 px-4 font-semibold shadow-sm">
                                                        Selesaikan Pengantaran
                                                    </button>
                                                </form>
                                            @else
                                                <span class="badge bg-brand-mint text-brand-emerald">Tiba &amp; Selesai</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile: task cards -->
                    <div class="sm:hidden space-y-3">
                        @foreach ($shipments as $shipment)
                            <div class="list-card space-y-3" x-data="{
                                isTransmitting: {{ $shipment->status === 'IN_TRANSIT' ? 'true' : 'false' }},
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
                            }" x-init="if (isTransmitting) startGps();">
                                <div class="flex justify-between items-start gap-3">
                                    <div class="min-w-0">
                                        <p class="font-mono font-bold text-brand-dark text-sm">{{ $shipment->shipment_number }}</p>
                                        <p class="font-mono text-[0.6875rem] text-brand-slate mt-0.5">{{ $shipment->order->order_number }}</p>
                                    </div>
                                    <span class="badge {{ $shipment->status === 'IN_TRANSIT' ? 'bg-brand-mint text-brand-emerald' : ($shipment->status === 'DELIVERED' ? 'bg-brand-primary text-white' : 'bg-brand-cream text-brand-slate') }} shrink-0">
                                        {{ $shipment->status === 'IN_TRANSIT' ? 'GPS Live Mengirim' : ($shipment->status === 'DELIVERED' ? 'Selesai' : $shipment->status) }}
                                    </span>
                                </div>

                                <div class="space-y-2 pt-2 border-t border-black/[0.04]">
                                    @foreach ($shipment->order->items as $item)
                                        <div class="text-xs">
                                            <span class="font-bold text-brand-dark">{{ $item->umkm->business_name }}</span>
                                            <div class="text-brand-slate">{{ $item->delivery_address }} ({{ $item->delivery_district }})</div>
                                        </div>
                                    @endforeach
                                </div>

                                @if ($shipment->status === 'PENDING')
                                    <form method="POST" action="{{ route('kurir.shipments.start', $shipment) }}">
                                        @csrf
                                        <button type="submit" class="btn-primary w-full text-sm font-semibold shadow-sm">
                                            Mulai Pengiriman
                                        </button>
                                    </form>
                                @elseif ($shipment->status === 'IN_TRANSIT')
                                    <form method="POST" action="{{ route('kurir.shipments.complete', $shipment) }}" onsubmit="return confirm('Konfirmasi seluruh barang telah sampai di lokasi UMKM?');">
                                        @csrf
                                        <button type="submit" class="btn-accent w-full text-sm font-semibold shadow-sm">
                                            Selesaikan Pengantaran
                                        </button>
                                    </form>
                                @else
                                    <p class="text-center text-xs font-semibold text-brand-emerald pt-1">Tiba &amp; Selesai</p>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-3 border-t border-black/[0.04]">
                        {{ $shipments->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="card-shell">
                <div class="card-core px-6 py-12 text-center">
                    <p class="font-bold text-brand-dark">Belum ada penugasan pengiriman.</p>
                    <p class="text-[0.8125rem] text-brand-slate mt-1.5 max-w-md mx-auto">Tugas baru muncul di sini setelah distributor menugaskan Anda ke surat jalan.</p>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
