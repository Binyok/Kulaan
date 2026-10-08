<x-layouts.app>
    <x-slot name="title">Tugas Pengiriman Kurir</x-slot>

    <div class="space-y-6 sm:space-y-8 animate-fade-up">
        <div>
            <span class="eyebrow">Panel Kurir Armada</span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-brand-dark tracking-tight mt-1.5">Daftar Tugas Pengiriman</h1>
            <p class="text-[0.8125rem] text-brand-slate mt-1">Kelola status perjalanan pengiriman dan transmisi koordinat GPS realtime.</p>
        </div>

        <!-- Info GPS Box -->
        <div class="p-4 bg-brand-mint/60 rounded-2xl border border-brand-primary/15 flex items-center justify-between text-xs text-brand-primary">
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 rounded-full bg-brand-emerald animate-ping"></span>
                <p class="font-semibold">Sistem GPS Otomatis Aktif saat tombol 'Mulai Pengiriman' ditekan pada smartphone / browser Anda.</p>
            </div>
            <span class="font-mono text-[11px] bg-white px-2.5 py-1 rounded-md border border-brand-primary/20">WITA Banjarmasin</span>
        </div>

        <!-- Table (Double-Bezel) -->
        <div class="card-shell">
            <div class="card-core p-4 sm:p-7 space-y-5">
                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <table class="table-premium min-w-[700px]">
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
                            @forelse ($shipments as $shipment)
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
                                        <span class="badge {{ $shipment->status === 'IN_TRANSIT' ? 'bg-brand-mint text-brand-emerald' : 'bg-brand-cream text-brand-slate' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $shipment->status === 'IN_TRANSIT' ? 'bg-brand-emerald animate-pulse' : 'bg-gray-400' }}"></span>
                                            {{ $shipment->status === 'IN_TRANSIT' ? 'GPS Live Mengirim' : $shipment->status }}
                                        </span>
                                    </td>
                                    <td class="text-right space-x-2">
                                        @if ($shipment->status === 'PENDING')
                                            <form method="POST" action="{{ route('kurir.shipments.start', $shipment) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="btn-primary text-xs py-2 px-4 font-semibold shadow-sm">
                                                    Mulai Pengiriman &rarr;
                                                </button>
                                            </form>
                                        @elseif ($shipment->status === 'IN_TRANSIT')
                                            <div class="inline-flex items-center gap-2">
                                                <form method="POST" action="{{ route('kurir.shipments.complete', $shipment) }}" class="inline" onsubmit="return confirm('Konfirmasi seluruh barang telah sampai di lokasi UMKM?');">
                                                    @csrf
                                                    <button type="submit" class="btn-accent text-xs py-2 px-4 font-semibold shadow-sm">
                                                        Selesaikan Pengantaran &check;
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="badge bg-brand-mint text-brand-emerald">Tiba &amp; Selesai</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-brand-slate text-[0.8125rem]">Belum ada penugasan pengiriman.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="pt-3 border-t border-black/[0.04]">
                    {{ $shipments->links() }}
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
