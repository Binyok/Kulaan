<x-layouts.app>
    <x-slot name="title">Pantau Pengiriman & Posisi Kurir</x-slot>

    <div class="space-y-6 sm:space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <span class="eyebrow">Tracking Live GPS</span>
                <h1 class="font-display font-semibold text-[1.75rem] sm:text-4xl text-brand-dark tracking-tight text-balance mt-2">Pantau Pengiriman &amp; Kurir Armada</h1>
                <p class="text-[0.8125rem] text-brand-slate mt-1">Pantau pergerakan kurir pengantar bahan baku secara realtime menggunakan sensor GPS.</p>
            </div>
            <a href="{{ route('umkm.tracking.live') }}" class="btn-primary text-xs py-2.5 px-5 font-bold shadow-sm flex-shrink-0 w-full sm:w-auto justify-center">
                Peta Semua Kurir Aktif
            </a>
        </div>

        @php $hasShipments = $shipments->isNotEmpty(); @endphp

        <!-- List of Shipments -->
        @if ($hasShipments)
            <div class="card-shell">
                <div class="card-core p-4 sm:p-7 space-y-5">
                    <div>
                        <h2 class="text-base font-bold text-brand-dark">Daftar Surat Jalan Pengiriman</h2>
                        <p class="text-xs text-brand-slate">Buka peta pada pengiriman yang sedang berjalan untuk memantau posisi kurir realtime.</p>
                    </div>

                    <!-- Desktop: table -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="table-premium">
                            <thead>
                                <tr>
                                    <th>No. Surat Jalan</th>
                                    <th>No. Pesanan</th>
                                    <th>Distributor Pengirim</th>
                                    <th>Kurir Ditugaskan</th>
                                    <th class="text-center">Status GPS</th>
                                    <th class="text-right">Aksi Pantau</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($shipments as $shipment)
                                    <tr>
                                        <td>
                                            <div class="font-mono font-bold text-brand-dark">{{ $shipment->shipment_number }}</div>
                                            <div class="text-[11px] text-brand-slate">{{ $shipment->created_at->format('d M Y, H:i') }} WITA</div>
                                        </td>
                                        <td>
                                            <div class="font-mono text-xs font-semibold text-brand-dark">{{ $shipment->order->order_number }}</div>
                                            <div class="text-[11px] text-brand-slate">{{ $shipment->order->items->pluck('product.name')->join(', ') }}</div>
                                        </td>
                                        <td>
                                            <div class="font-bold text-brand-dark text-xs">{{ $shipment->order->distributor->company_name }}</div>
                                            <div class="text-[11px] text-brand-slate">{{ $shipment->order->distributor->district }}</div>
                                        </td>
                                        <td>
                                            @if ($shipment->courier)
                                                <div class="font-semibold text-brand-dark text-xs">{{ $shipment->courier->user->name }}</div>
                                                <div class="text-[11px] text-brand-slate">{{ $shipment->courier->vehicle_type }} ({{ $shipment->courier->vehicle_number }})</div>
                                            @else
                                                <span class="text-xs text-brand-slate">Belum ditugaskan</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $shipment->status === 'IN_TRANSIT' ? 'bg-brand-mint text-brand-emerald' : ($shipment->status === 'DELIVERED' ? 'bg-brand-primary text-white' : 'bg-brand-cream text-brand-slate') }}">
                                                {{ $shipment->status === 'IN_TRANSIT' ? 'Sedang Diantar (Live GPS)' : ($shipment->status === 'DELIVERED' ? 'Telah Tiba' : 'Menunggu Pengambilan') }}
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <a href="{{ route('umkm.tracking.show', $shipment) }}"
                                                class="btn-primary text-xs py-2 px-4 font-bold shadow-sm">
                                                {{ $shipment->status === 'IN_TRANSIT' ? 'Buka Live GPS' : 'Lihat Peta' }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile: cards -->
                    <div class="sm:hidden space-y-3">
                        @foreach ($shipments as $shipment)
                            <div class="list-card space-y-3">
                                <div class="flex justify-between items-start gap-3">
                                    <div class="min-w-0">
                                        <p class="font-mono font-bold text-brand-dark text-sm">{{ $shipment->shipment_number }}</p>
                                        <p class="text-[0.6875rem] text-brand-slate mt-0.5">{{ $shipment->order->items->pluck('product.name')->join(', ') }}</p>
                                    </div>
                                    <span class="badge {{ $shipment->status === 'IN_TRANSIT' ? 'bg-brand-mint text-brand-emerald' : ($shipment->status === 'DELIVERED' ? 'bg-brand-primary text-white' : 'bg-brand-cream text-brand-slate') }} shrink-0">
                                        {{ $shipment->status === 'IN_TRANSIT' ? 'Diantar (Live GPS)' : ($shipment->status === 'DELIVERED' ? 'Telah Tiba' : 'Menunggu Pengambilan') }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 gap-x-3 gap-y-2 text-[0.75rem]">
                                    <div>
                                        <p class="text-brand-slate">Distributor</p>
                                        <p class="font-semibold text-brand-dark">{{ $shipment->order->distributor->company_name }}</p>
                                    </div>
                                    <div>
                                        <p class="text-brand-slate">Kurir</p>
                                        @if ($shipment->courier)
                                            <p class="font-semibold text-brand-dark">{{ $shipment->courier->user->name }}</p>
                                            <p class="text-brand-slate">{{ $shipment->courier->vehicle_type }} ({{ $shipment->courier->vehicle_number }})</p>
                                        @else
                                            <p class="text-brand-slate">Belum ditugaskan</p>
                                        @endif
                                    </div>
                                </div>
                                <a href="{{ route('umkm.tracking.show', $shipment) }}" class="btn-primary w-full text-xs font-bold shadow-sm">
                                    {{ $shipment->status === 'IN_TRANSIT' ? 'Buka Live GPS' : 'Lihat Peta' }}
                                </a>
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
                    <p class="font-bold text-brand-dark">Belum ada surat jalan pengiriman untuk UMKM Anda.</p>
                    <p class="text-[0.8125rem] text-brand-slate mt-1.5 max-w-md mx-auto">Pengiriman muncul otomatis setelah pembayaran dikonfirmasi dan kurir ditugaskan distributor.</p>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
