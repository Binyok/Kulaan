<x-layouts.app>
    <x-slot name="title">Pantau Pengiriman & Posisi Kurir</x-slot>

    <div class="space-y-6 sm:space-y-8 animate-fade-up">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <span class="eyebrow">Tracking Live GPS</span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-brand-dark tracking-tight mt-1.5">Pantau Pengiriman &amp; Kurir Armada</h1>
                <p class="text-[0.8125rem] text-brand-slate mt-1">Pantau pergerakan kurir pengantar bahan baku secara realtime menggunakan sensor GPS.</p>
            </div>
        </div>

        <!-- Table of Shipments (Double-Bezel) -->
        <div class="card-shell">
            <div class="card-core p-4 sm:p-7 space-y-5">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-base font-bold text-brand-dark">Daftar Surat Jalan Pengiriman</h2>
                        <p class="text-xs text-brand-slate">Klik "Buka Live Peta GPS" pada pesanan yang sedang berjalan untuk memantau posisi kurir.</p>
                    </div>
                </div>

                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <table class="table-premium min-w-[700px]">
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
                            @forelse ($shipments as $shipment)
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
                                            <span class="w-1.5 h-1.5 rounded-full {{ $shipment->status === 'IN_TRANSIT' ? 'bg-brand-emerald animate-ping' : '' }}"></span>
                                            {{ $shipment->status === 'IN_TRANSIT' ? 'Sedang Diantar (Live GPS)' : ($shipment->status === 'DELIVERED' ? 'Telah Tiba' : 'Menunggu Pengambilan') }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('umkm.tracking.show', $shipment) }}" 
                                            class="btn-primary text-xs py-2 px-4 inline-flex items-center gap-1.5 font-bold shadow-sm">
                                            <span>Buka Live Peta GPS</span>
                                            <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-brand-slate text-[0.8125rem]">
                                        Belum ada surat jalan pengiriman untuk UMKM Anda. Pengiriman akan otomatis muncul setelah pembayaran dikonfirmasi dan kurir ditugaskan.
                                    </td>
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
