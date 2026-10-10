<x-layouts.app>
    <x-slot name="title">Dashboard UMKM</x-slot>

    <div class="space-y-6 sm:space-y-8">
        <!-- Hero Banner with Soft Depth -->
        <div class="hero-gradient texture-grain text-white p-6 sm:p-9 shadow-[0_12px_40px_-12px_rgba(45,106,79,0.35)]" data-reveal>
            <div class="relative z-10 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6">
                <div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[0.625rem] font-semibold bg-white/10 text-white/90 uppercase tracking-wider mb-3">
                        Portal Pengadaan Mandiri
                    </span>
                    <h1 class="font-display font-semibold text-3xl sm:text-4xl tracking-tight text-balance">Selamat datang, {{ $umkm?->business_name ?? $user->name }}</h1>
                    <p class="text-xs sm:text-sm text-white/75 mt-1.5">
                        Wilayah {{ $umkm?->district ?? 'Banjarmasin' }} &bull; Sektor {{ $umkm?->business_type ?? 'Kuliner' }}
                    </p>
                </div>
                <a href="{{ route('umkm.requests.index') }}" class="btn-accent text-sm py-3 px-6 shadow-lg shadow-black/10 flex-shrink-0 w-full sm:w-auto justify-center">
                    Ajukan Kebutuhan Baru
                </a>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5">
            <div class="card-shell">
                <div class="card-core p-5 sm:p-6">
                    <p class="metric-label">Kebutuhan Sedang Matching</p>
                    <p class="metric-value text-brand-dark mt-2">{{ $activeRequestsCount }}</p>
                    <p class="text-[0.6875rem] text-brand-slate mt-2">Menunggu kuota grosir UMKM sekitar</p>
                </div>
            </div>

            <div class="card-shell">
                <div class="card-core p-5 sm:p-6">
                    <p class="metric-label">Tagihan Menunggu Pembayaran</p>
                    <p class="metric-value {{ $unpaidInvoices->count() > 0 ? 'text-brand-brick' : 'text-brand-primary' }} mt-2">
                        {{ $unpaidInvoices->count() }}
                    </p>
                    <p class="text-[0.6875rem] text-brand-slate mt-2">Kuota kolektif terpenuhi & siap diproses</p>
                </div>
            </div>

            <div class="card-shell">
                <div class="card-core p-5 sm:p-6 savings-card">
                    <p class="metric-label text-brand-brass">Total Penghematan Anda</p>
                    <p class="metric-value text-brand-brass mt-2">Rp {{ number_format($totalSavings, 0, ',', '.') }}</p>
                    <p class="text-[0.6875rem] text-brand-slate mt-2">Dibandingkan harga eceran pasar</p>
                </div>
            </div>
        </div>

        <!-- Live GPS Banner: all active shipments, grouped honestly by courier. -->
        @php
            $activeShipments = $orderItems
                ->flatMap(fn ($item) => $item->order->shipments)
                ->unique('id')
                ->filter(fn ($shipment) => $shipment->status === 'IN_TRANSIT');
            $activeCourierCount = $activeShipments->pluck('courier_id')->filter()->unique()->count();
        @endphp
        @if ($activeShipments->isNotEmpty())
            <div class="card-shell">
                <div class="card-core p-5 sm:p-6 border-2 border-brand-primary/20 bg-gradient-to-r from-brand-mint/60 via-white to-brand-mint/30 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div class="min-w-0">
                        <h3 class="font-extrabold text-brand-dark text-base">Pengiriman sedang berjalan</h3>
                        <p class="text-xs text-brand-slate mt-1">
                            {{ $activeCourierCount }} kurir &bull; {{ $activeShipments->count() }} paket menuju area Anda.
                            Semua posisi kurir tampil di satu peta.
                        </p>
                    </div>
                    <a href="{{ route('umkm.tracking.live') }}" class="btn-primary text-xs py-2.5 px-5 font-bold shadow-lg shadow-brand-primary/25 w-full sm:w-auto justify-center">
                        Buka Peta Live Semua Kurir
                    </a>
                </div>
            </div>
        @endif

        <!-- Unpaid Invoices Alert Banner -->
        @if ($unpaidInvoices->count() > 0)
            <div class="card-shell">
                <div class="card-core p-5 sm:p-6 border-l-4 border-brand-accent space-y-4">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-brand-accent"></span>
                        <h2 class="text-sm font-bold text-brand-dark uppercase tracking-wide">Tagihan Grosir Perlu Pembayaran</h2>
                    </div>

                    <div class="divide-y divide-black/[0.04]">
                        @foreach ($unpaidInvoices as $item)
                            <div class="py-3.5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                                <div class="min-w-0">
                                    <p class="font-bold text-brand-dark text-sm">{{ $item->product->name }}</p>
                                    <p class="text-[0.75rem] text-brand-slate mt-0.5">
                                        {{ number_format($item->quantity, 0) }} {{ $item->product->unit }} &bull; Distributor: {{ $item->order->distributor->company_name }}
                                        &bull; <span class="text-brand-brass font-semibold">Hemat Rp {{ number_format($item->savings_amount, 0, ',', '.') }}</span>
                                    </p>
                                </div>
                                <div class="flex items-center gap-4 w-full sm:w-auto justify-between sm:justify-start">
                                    <span class="font-extrabold text-brand-primary text-base">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                    <a href="{{ route('payment.show', $item) }}" class="btn-primary text-xs py-2 px-4">
                                        Bayar Sekarang
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Fast-Purchase Fallback Requests -->
        @if ($fallbackRequests->isNotEmpty())
            <div class="card-shell">
                <div class="card-core p-5 sm:p-6 border-l-4 border-brand-brick space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <h2 class="text-sm font-bold text-brand-dark uppercase tracking-wide">Jalur Pembelian Cepat</h2>
                            <p class="text-[0.75rem] text-brand-slate mt-0.5">
                                Kuota kolektif belum tercapai sebelum tenggat. Ajukan ulang dengan deadline baru agar kembali di-matching.
                            </p>
                        </div>
                        <a href="{{ route('umkm.requests.index') }}" class="btn-ghost text-xs py-2 px-4 font-bold self-start sm:self-auto">Ajukan Ulang</a>
                    </div>

                    <div class="divide-y divide-black/[0.04]">
                        @foreach ($fallbackRequests as $request)
                            <div class="py-3.5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                                <div>
                                    <p class="font-bold text-brand-dark text-sm">{{ $request->product?->name ?? 'Produk' }}</p>
                                    <p class="text-[0.75rem] text-brand-slate mt-0.5">
                                        {{ number_format((float) $request->quantity, 0) }} {{ $request->unit }}
                                        &bull; Tenggat {{ $request->deadline?->format('d M Y, H:i') }}
                                    </p>
                                </div>
                                <span class="badge {{ $request->status->badgeClass() }}">{{ $request->status->label() }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Orders & Shipments -->
        <div class="card-shell">
            <div class="card-core p-4 sm:p-7 space-y-5">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-base font-bold text-brand-dark tracking-tight">Riwayat Pengadaan Kolektif</h2>
                        <p class="text-[0.75rem] text-brand-slate mt-0.5">Semua transaksi pengadaan bahan baku bersama</p>
                    </div>
                </div>

                @php $hasOrders = $orderItems->isNotEmpty(); @endphp

                @if ($hasOrders)
                    <!-- Desktop: table -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="table-premium">
                            <thead>
                                <tr>
                                    <th>Bahan Baku</th>
                                    <th class="text-center">Kuantitas</th>
                                    <th class="text-right">Subtotal Grosir</th>
                                    <th class="text-right">Penghematan</th>
                                    <th class="text-center">Status Bayar</th>
                                    <th class="text-center">Pengiriman &amp; Live GPS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orderItems as $item)
                                    <tr>
                                        <td>
                                            <div class="font-bold text-brand-dark">{{ $item->product->name }}</div>
                                            <div class="text-[0.6875rem] text-brand-slate">{{ $item->order->distributor->company_name }}</div>
                                        </td>
                                        <td class="text-center font-bold text-brand-dark">
                                            {{ number_format($item->quantity, 0) }} {{ $item->product->unit }}
                                        </td>
                                        <td class="text-right font-extrabold text-brand-primary">
                                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                        </td>
                                        <td class="text-right font-bold text-brand-brass">
                                            Hemat Rp {{ number_format($item->savings_amount, 0, ',', '.') }}
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $item->payment_status->badgeClass() }}">
                                                {{ $item->payment_status->label() }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @php $shipment = $item->order->shipments->first(); @endphp
                                            @if ($shipment)
                                                <a href="{{ route('umkm.tracking.show', $shipment) }}" class="btn-ghost text-xs py-1.5 px-3.5 font-bold text-brand-primary hover:bg-brand-mint/60 border-brand-primary/20">
                                                    Lacak Posisi Kurir
                                                </a>
                                            @else
                                                <span class="text-[0.6875rem] text-brand-slate">Menunggu Penugasan Armada</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile: cards -->
                    <div class="sm:hidden space-y-3">
                        @foreach ($orderItems as $item)
                            @php $shipment = $item->order->shipments->first(); @endphp
                            <div class="list-card space-y-3">
                                <div class="flex justify-between items-start gap-3">
                                    <div class="min-w-0">
                                        <p class="font-bold text-brand-dark text-sm">{{ $item->product->name }}</p>
                                        <p class="text-[0.6875rem] text-brand-slate mt-0.5">{{ $item->order->distributor->company_name }}</p>
                                    </div>
                                    <span class="badge {{ $item->payment_status->badgeClass() }} shrink-0">{{ $item->payment_status->label() }}</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-[0.75rem]">
                                    <div>
                                        <p class="text-brand-slate">Kuantitas</p>
                                        <p class="font-bold text-brand-dark">{{ number_format($item->quantity, 0) }} {{ $item->product->unit }}</p>
                                    </div>
                                    <div>
                                        <p class="text-brand-slate">Subtotal</p>
                                        <p class="font-extrabold text-brand-primary">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="col-span-2">
                                        <p class="text-brand-slate">Penghematan</p>
                                        <p class="font-bold text-brand-brass">Hemat Rp {{ number_format($item->savings_amount, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                                @if ($shipment)
                                    <a href="{{ route('umkm.tracking.show', $shipment) }}" class="btn-ghost w-full text-xs font-bold text-brand-primary border-brand-primary/20">
                                        Lacak Posisi Kurir
                                    </a>
                                @else
                                    <p class="text-[0.6875rem] text-brand-slate text-center pt-1 border-t border-black/[0.04]">Menunggu Penugasan Armada</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-10 text-center">
                        <p class="text-brand-slate text-[0.8125rem]">Belum ada riwayat pesanan kolektif.</p>
                        <a href="{{ route('umkm.requests.index') }}" class="btn-primary text-xs mt-4">Ajukan Kebutuhan Pertama</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
