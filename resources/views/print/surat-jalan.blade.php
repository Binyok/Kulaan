<x-layouts.app>
    <x-slot name="title">Surat Jalan {{ $shipment->shipment_number }}</x-slot>

    <div class="max-w-3xl mx-auto px-4 py-6">
        <div class="flex items-center justify-between mb-4 print:hidden">
            <button type="button" onclick="history.back()" class="text-xs font-bold text-muted border border-line rounded-xl px-4 py-2.5 bg-white cursor-pointer">&larr; Kembali</button>
            <button type="button" onclick="window.print()" class="text-xs font-bold text-white bg-forest rounded-xl px-4 py-2.5 cursor-pointer">Cetak / Simpan PDF</button>
        </div>

        <div class="bg-white border border-line rounded-3xl p-6 sm:p-8 print:border-0 print:shadow-none print:p-0">
            <div class="flex items-start justify-between gap-4 border-b border-line pb-5">
                <div>
                    <p class="text-2xl font-extrabold tracking-tight text-forest">Kulaan</p>
                    <p class="text-[11px] text-muted mt-0.5">Platform Pengadaan Kolektif UMKM</p>
                </div>
                <div class="text-right">
                    <p class="text-lg font-extrabold text-ink">SURAT JALAN</p>
                    <p class="font-mono text-xs text-muted">{{ $shipment->shipment_number }}</p>
                    <p class="text-[11px] text-muted mt-0.5">Pesanan {{ $shipment->order->order_number }}</p>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-5 py-5 border-b border-line text-sm">
                <div>
                    <p class="text-[10px] font-bold text-muted uppercase tracking-wider mb-1.5">Pengirim / Distributor</p>
                    <p class="font-bold text-ink">{{ $shipment->order->distributor->company_name }}</p>
                    <p class="text-xs text-muted mt-0.5">{{ $shipment->order->distributor->address }}@if ($shipment->order->distributor->city), {{ $shipment->order->distributor->city }}@endif</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-muted uppercase tracking-wider mb-1.5">Kurir Pengantar</p>
                    <p class="font-bold text-ink">{{ $shipment->courier?->user?->name ?? '-' }}</p>
                    <p class="text-xs text-muted mt-0.5">{{ $shipment->courier?->vehicle_type ?? '-' }} &bull; {{ $shipment->courier?->vehicle_number ?? '-' }} &bull; {{ $shipment->courier?->user?->phone ?? '-' }}</p>
                </div>
            </div>

            <table class="w-full text-sm mt-5">
                <thead>
                    <tr class="text-left text-[11px] uppercase tracking-wider text-muted border-b border-line">
                        <th class="py-2 pr-3 font-bold">Penerima / UMKM</th>
                        <th class="py-2 px-3 font-bold">Alamat Tujuan</th>
                        <th class="py-2 px-3 font-bold">Muatan</th>
                        <th class="py-2 pl-3 font-bold text-right">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($shipment->order->items as $item)
                        <tr class="border-b border-line/60 align-top">
                            <td class="py-3 pr-3 font-semibold text-ink">{{ $item->umkm->business_name }}</td>
                            <td class="py-3 px-3 text-xs text-muted">{{ $item->delivery_address }} ({{ $item->delivery_district }})</td>
                            <td class="py-3 px-3">{{ $item->product->name }}</td>
                            <td class="py-3 pl-3 text-right tabular-nums">{{ number_format($item->quantity, 0) }} {{ $item->product->unit }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-6 bg-cream/60 border border-line rounded-2xl p-4 text-xs space-y-1.5">
                <p><span class="font-bold text-ink">Status pengiriman:</span> {{ $shipment->status }}@if ($shipment->delivered_at) &bull; Terkirim {{ $shipment->delivered_at->format('d M Y, H:i') }}@endif</p>
                @if ($shipment->notes)
                    <p><span class="font-bold text-ink">Catatan:</span> {{ $shipment->notes }}</p>
                @endif
            </div>

            <div class="grid grid-cols-3 gap-4 mt-10 text-center text-xs">
                <div>
                    <p class="text-muted">Pengirim</p>
                    <div class="h-16"></div>
                    <p class="border-t border-line pt-1.5 font-semibold text-ink">{{ $shipment->order->distributor->company_name }}</p>
                </div>
                <div>
                    <p class="text-muted">Kurir</p>
                    <div class="h-16"></div>
                    <p class="border-t border-line pt-1.5 font-semibold text-ink">{{ $shipment->courier?->user?->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-muted">Penerima</p>
                    <div class="h-16"></div>
                    <p class="border-t border-line pt-1.5 font-semibold text-ink">&nbsp;</p>
                </div>
            </div>

            <p class="text-[10px] text-muted mt-8 leading-relaxed">Surat jalan ini diterbitkan secara elektronik oleh sistem Kulaan. Penerima wajib memeriksa kondisi dan jumlah barang saat serah terima; ketidaksesuaian dapat diajukan sebagai komplain melalui aplikasi.</p>
        </div>
    </div>
</x-layouts.app>
