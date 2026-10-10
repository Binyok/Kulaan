<x-layouts.app>
    <x-slot name="title">Dashboard Distributor</x-slot>

    <div class="space-y-8">
        <div data-reveal>
            <span class="eyebrow">Distributor Ekosistem</span>
            <h1 class="font-display font-semibold text-[1.75rem] sm:text-4xl text-brand-dark tracking-tight text-balance mt-2">{{ $distributor?->company_name ?? $user->name }}</h1>
            <p class="text-[0.8125rem] text-brand-slate mt-1">Area Operasional: {{ $distributor?->district ?? 'Banjarmasin' }} &bull; Waktu Kirim Rata-rata: {{ $distributor?->delivery_lead_time_days ?? 1 }} Hari</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div class="card-shell">
                <div class="card-core p-6">
                    <p class="metric-label">Katalog Produk Baku</p>
                    <p class="metric-value text-brand-dark mt-2.5">{{ $catalogCount }}</p>
                    <p class="text-[0.6875rem] text-brand-slate mt-2">Bahan baku aktif dijual</p>
                </div>
            </div>

            <div class="card-shell">
                <div class="card-core p-6">
                    <p class="metric-label">Pesanan Perlu Diproses</p>
                    <p class="metric-value {{ $pendingOrdersCount > 0 ? 'text-brand-brick' : 'text-brand-primary' }} mt-2.5">
                        {{ $pendingOrdersCount }}
                    </p>
                    <p class="text-[0.6875rem] text-brand-slate mt-2">Menunggu konfirmasi / kurir</p>
                </div>
            </div>

            <!-- Rating is the average of real UMKM reviews, not a TOPSIS score. -->
            <div class="card-shell">
                <div class="card-core p-6">
                    <p class="metric-label">Rating dari UMKM</p>
                    @if ($reviewCount > 0)
                        <p class="metric-value text-brand-dark mt-2.5">
                            {{ number_format($distributor?->rating ?? 0, 1, ',', '') }}
                            <span class="text-amber-500 text-lg align-middle">&#9733;</span>
                            <span class="text-xs font-normal text-brand-slate">/ 5</span>
                        </p>
                        <p class="text-[0.6875rem] text-brand-slate mt-2">Rata-rata dari {{ $reviewCount }} ulasan UMKM</p>
                    @else
                        <p class="metric-value text-brand-slate mt-2.5">&ndash;</p>
                        <p class="text-[0.6875rem] text-brand-slate mt-2">Belum ada ulasan dari UMKM</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="card-shell">
            <div class="card-core p-6 sm:p-7 space-y-5">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-base font-bold text-brand-dark tracking-tight">Pesanan Kolektif Masuk</h2>
                        <p class="text-[0.75rem] text-brand-slate mt-0.5">Daftar pesanan terbaru dari grup auto-matching UMKM</p>
                    </div>
                    <a href="{{ route('distributor.orders.index') }}" class="btn-ghost text-xs py-1.5 px-4 font-semibold text-brand-primary">
                        Lihat Semua
                    </a>
                </div>

                @php $hasOrders = $orders->isNotEmpty(); @endphp

                @if ($hasOrders)
                    <!-- Desktop: table -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="table-premium">
                            <thead>
                                <tr>
                                    <th>No. Pesanan</th>
                                    <th>Anggota UMKM</th>
                                    <th class="text-center">Kuantitas</th>
                                    <th class="text-right">Nilai Transaksi</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr>
                                        <td class="font-mono font-bold text-brand-dark">{{ $order->order_number }}</td>
                                        <td class="text-xs text-brand-slate">
                                            {{ $order->items->pluck('umkm.business_name')->join(', ') }}
                                        </td>
                                        <td class="text-center font-bold text-brand-dark">{{ number_format($order->total_quantity, 0) }} unit</td>
                                        <td class="text-right font-extrabold text-brand-primary">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $order->status->badgeClass() }}">
                                                {{ $order->status->label() }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile: cards -->
                    <div class="sm:hidden space-y-3">
                        @foreach ($orders as $order)
                            <div class="list-card space-y-2">
                                <div class="flex justify-between items-start gap-3">
                                    <div class="min-w-0">
                                        <p class="font-mono font-bold text-brand-dark text-sm">{{ $order->order_number }}</p>
                                        <p class="text-[0.6875rem] text-brand-slate mt-0.5 truncate">{{ $order->items->pluck('umkm.business_name')->join(', ') }}</p>
                                    </div>
                                    <span class="badge {{ $order->status->badgeClass() }} shrink-0">{{ $order->status->label() }}</span>
                                </div>
                                <div class="flex justify-between items-center text-[0.75rem] pt-2 border-t border-black/[0.04]">
                                    <span class="text-brand-slate">{{ number_format($order->total_quantity, 0) }} unit</span>
                                    <span class="font-extrabold text-brand-primary">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center">
                        <p class="text-brand-slate text-[0.8125rem]">Belum ada pesanan kolektif masuk saat ini.</p>
                        <a href="{{ route('distributor.products.index') }}" class="btn-primary text-xs mt-4">Kelola Katalog Produk</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
