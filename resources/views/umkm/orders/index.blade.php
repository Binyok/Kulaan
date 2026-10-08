<x-layouts.app>
    <x-slot name="title">Riwayat Pesanan &amp; Tagihan</x-slot>

    <div class="space-y-6 sm:space-y-8 animate-fade-up">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <span class="eyebrow">Pesanan Kolektif</span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-brand-dark tracking-tight mt-1.5">Riwayat Pesanan &amp; Tagihan</h1>
                <p class="text-[0.8125rem] text-brand-slate mt-1">Daftar lengkap invoice individual dan status pembayaran pengadaan kolektif Anda.</p>
            </div>
        </div>

        <!-- Table of Order Items & Invoices (Double-Bezel) -->
        <div class="card-shell">
            <div class="card-core p-4 sm:p-7 space-y-5">
                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <table class="table-premium min-w-[700px]">
                        <thead>
                            <tr>
                                <th>No. Pesanan / Tanggal</th>
                                <th>Bahan Baku</th>
                                <th class="text-center">Kuantitas</th>
                                <th class="text-right">Harga Grosir</th>
                                <th class="text-right">Total Hemat</th>
                                <th class="text-center">Status Pembayaran</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($orderItems as $item)
                                <tr>
                                    <td>
                                        <div class="font-mono font-bold text-brand-dark">{{ $item->order->order_number }}</div>
                                        <div class="text-[11px] text-brand-slate">{{ $item->created_at->format('d M Y, H:i') }} WITA</div>
                                    </td>
                                    <td>
                                        <div class="font-bold text-brand-dark">{{ $item->product->name }}</div>
                                        <div class="text-[11px] text-brand-slate">Distributor: {{ $item->order->distributor->company_name }}</div>
                                    </td>
                                    <td class="text-center font-bold text-brand-dark">
                                        {{ number_format($item->quantity, 0) }} {{ $item->product->unit }}
                                    </td>
                                    <td class="text-right font-extrabold text-brand-primary">
                                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                        <div class="text-[10px] text-brand-slate font-normal">@ Rp {{ number_format($item->unit_price, 0, ',', '.') }}/{{ $item->product->unit }}</div>
                                    </td>
                                    <td class="text-right font-bold text-brand-accent">
                                        Hemat Rp {{ number_format($item->savings_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $item->payment_status->badgeClass() }}">
                                            {{ $item->payment_status->label() }}
                                        </span>
                                    </td>
                                    <td class="text-right whitespace-nowrap">
                                        <a href="{{ route('payment.show', $item) }}" 
                                            class="{{ $item->payment_status->value === 'UNPAID' ? 'btn-primary' : 'btn-ghost text-brand-primary font-bold' }} text-xs py-1.5 px-3.5 inline-flex items-center gap-1 shadow-sm">
                                            <span>{{ $item->payment_status->value === 'UNPAID' ? 'Bayar Invoice' : 'Lihat Invoice' }}</span>
                                            &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-brand-slate text-[0.8125rem]">
                                        Belum ada riwayat pesanan kolektif.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pt-3 border-t border-black/[0.04]">
                    {{ $orderItems->links() }}
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
