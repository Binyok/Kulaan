<x-layouts.app>
    <x-slot name="title">Invoice {{ $orderItem->order->order_number }}</x-slot>

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
                    <p class="text-lg font-extrabold text-ink">INVOICE</p>
                    <p class="font-mono text-xs text-muted">{{ $orderItem->payment?->payment_reference ?? $orderItem->order->order_number }}</p>
                    <p class="text-[11px] text-muted mt-0.5">{{ $orderItem->created_at->format('d M Y') }}</p>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-5 py-5 border-b border-line text-sm">
                <div>
                    <p class="text-[10px] font-bold text-muted uppercase tracking-wider mb-1.5">Penjual / Distributor</p>
                    <p class="font-bold text-ink">{{ $orderItem->order->distributor->company_name }}</p>
                    <p class="text-xs text-muted mt-0.5">{{ $orderItem->order->distributor->address }}@if ($orderItem->order->distributor->city), {{ $orderItem->order->distributor->city }}@endif</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-muted uppercase tracking-wider mb-1.5">Pembeli / UMKM</p>
                    <p class="font-bold text-ink">{{ $orderItem->umkm->business_name }}</p>
                    <p class="text-xs text-muted mt-0.5">{{ $orderItem->delivery_address }} ({{ $orderItem->delivery_district }})</p>
                </div>
            </div>

            <table class="w-full text-sm mt-5">
                <thead>
                    <tr class="text-left text-[11px] uppercase tracking-wider text-muted border-b border-line">
                        <th class="py-2 pr-3 font-bold">Produk</th>
                        <th class="py-2 px-3 font-bold text-right">Jumlah</th>
                        <th class="py-2 px-3 font-bold text-right">Harga Satuan</th>
                        <th class="py-2 pl-3 font-bold text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-line/60">
                        <td class="py-3 pr-3 font-semibold text-ink">{{ $orderItem->product->name }}</td>
                        <td class="py-3 px-3 text-right tabular-nums">{{ number_format($orderItem->quantity, 0) }} {{ $orderItem->product->unit }}</td>
                        <td class="py-3 px-3 text-right tabular-nums">Rp {{ number_format($orderItem->unit_price, 0, ',', '.') }}</td>
                        <td class="py-3 pl-3 text-right tabular-nums font-semibold">Rp {{ number_format($orderItem->subtotal, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
                <tfoot class="text-sm">
                    <tr>
                        <td colspan="3" class="py-2 pr-3 text-right text-muted">Harga eceran pembanding</td>
                        <td class="py-2 pl-3 text-right tabular-nums text-muted">Rp {{ number_format($orderItem->benchmark_retail_price * $orderItem->quantity, 0, ',', '.') }}</td>
                    </tr>
                    @if ($orderItem->savings_amount > 0)
                        <tr>
                            <td colspan="3" class="py-1 pr-3 text-right font-semibold text-brass">Penghematan kolektif</td>
                            <td class="py-1 pl-3 text-right tabular-nums font-semibold text-brass">- Rp {{ number_format($orderItem->savings_amount, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    <tr class="border-t-2 border-line">
                        <td colspan="3" class="py-3 pr-3 text-right font-extrabold text-ink">Total Bayar</td>
                        <td class="py-3 pl-3 text-right tabular-nums font-extrabold text-forest text-base">Rp {{ number_format($orderItem->subtotal, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>

            <div class="mt-6 bg-cream/60 border border-line rounded-2xl p-4 text-xs space-y-1.5">
                <p><span class="font-bold text-ink">Status pembayaran:</span> {{ $orderItem->payment_status->label() }}@if ($orderItem->payment?->paid_at) &bull; {{ $orderItem->payment->paid_at->format('d M Y, H:i') }}@endif</p>
                @if ($orderItem->payment?->payment_method)
                    <p><span class="font-bold text-ink">Metode:</span> {{ strtoupper($orderItem->payment->payment_method) }} via Midtrans</p>
                @endif
                @if ($orderItem->escrow)
                    <p><span class="font-bold text-ink">Status dana escrow:</span> {{ $orderItem->escrow->status->label() }} — dana ditahan Kulaan dan diteruskan ke distributor setelah barang diterima UMKM.</p>
                @endif
                <p><span class="font-bold text-ink">Nomor pesanan:</span> <span class="font-mono">{{ $orderItem->order->order_number }}</span></p>
            </div>

            <p class="text-[10px] text-muted mt-6 leading-relaxed">Dokumen ini diterbitkan secara elektronik oleh sistem Kulaan dan sah tanpa tanda tangan. Simpan invoice ini sebagai bukti transaksi untuk pembukuan usaha Anda.</p>
        </div>
    </div>
</x-layouts.app>
