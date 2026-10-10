<x-layouts.app>
    <x-slot name="title">Dashboard UMKM</x-slot>

    <div class="space-y-6 sm:space-y-8 animate-fade-up">
        <!-- Hero Banner with Soft Depth -->
        <div class="hero-gradient text-white p-6 sm:p-9 shadow-[0_12px_40px_-12px_rgba(45,106,79,0.35)]">
            <div class="relative z-10 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[0.625rem] font-semibold bg-white/10 text-white/90 backdrop-blur-md uppercase tracking-wider mb-3">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        Portal Pengadaan Mandiri
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Selamat datang, {{ $umkm?->business_name ?? $user->name }}</h1>
                    <p class="text-xs sm:text-sm text-white/75 mt-1.5">
                        Wilayah {{ $umkm?->district ?? 'Banjarmasin' }} &bull; Sektor {{ $umkm?->business_type ?? 'Kuliner' }}
                    </p>
                </div>
                <a href="{{ route('umkm.requests.index') }}" class="btn-accent text-sm py-3 px-6 shadow-lg shadow-black/10 flex-shrink-0 w-full sm:w-auto justify-center">
                    + Ajukan Kebutuhan Baru
                </a>
            </div>
        </div>

        <!-- Metric Cards: Asymmetrical & Double-Bezel -->
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
                    <p class="metric-label text-brand-accent">Total Penghematan Anda</p>
                    <p class="metric-value text-brand-accent mt-2">Rp {{ number_format($totalSavings, 0, ',', '.') }}</p>
                    <p class="text-[0.6875rem] text-brand-slate mt-2">Dibandingkan harga eceran pasar</p>
                </div>
            </div>
        </div>

        <!-- 🚚 ACTIVE LIVE GPS COURIER BANNER (Jika ada pengiriman aktif sedang jalan) -->
        @php 
            $activeDeliveryItem = $orderItems->first(fn($item) => $item->order->status->value === 'IN_TRANSIT' && $item->order->shipments->isNotEmpty());
        @endphp
        @if ($activeDeliveryItem && $activeDeliveryItem->order->shipments->isNotEmpty())
            @php $activeShipment = $activeDeliveryItem->order->shipments->first(); @endphp
            <div class="card-shell">
                <div class="card-core p-5 sm:p-6 border-2 border-brand-primary/20 bg-gradient-to-r from-brand-mint/60 via-white to-brand-mint/30 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-brand-primary text-white flex items-center justify-center text-2xl shadow-md shadow-brand-primary/25 animate-bounce">
                            🛵
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-brand-emerald animate-ping"></span>
                                <h3 class="font-extrabold text-brand-dark text-base">Kurir Sedang Menuju Lokasi Anda!</h3>
                            </div>
                            <p class="text-xs text-brand-slate mt-0.5">
                                Pengiriman: <strong class="text-brand-dark">{{ $activeDeliveryItem->product->name }} ({{ number_format($activeDeliveryItem->quantity, 0) }} {{ $activeDeliveryItem->product->unit }})</strong> &bull;
                                Kurir: {{ $activeShipment->courier?->user->name ?? 'Armada Kurir' }}
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('umkm.tracking.show', $activeShipment) }}" class="btn-primary text-xs py-2.5 px-5 font-bold shadow-lg shadow-brand-primary/25 w-full sm:w-auto justify-center">
                        Buka Live Peta GPS &rarr;
                    </a>
                </div>
            </div>
        @endif

        <!-- Unpaid Invoices Alert Banner -->
        @if ($unpaidInvoices->count() > 0)
            <div class="card-shell">
                <div class="card-core p-5 sm:p-6 border-l-4 border-brand-accent space-y-4">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-brand-accent animate-ping"></span>
                        <h2 class="text-sm font-bold text-brand-dark uppercase tracking-wide">Tagihan Grosir Perlu Pembayaran</h2>
                    </div>

                    <div class="divide-y divide-black/[0.04]">
                        @foreach ($unpaidInvoices as $item)
                            <div class="py-3.5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                                <div>
                                    <p class="font-bold text-brand-dark text-sm">{{ $item->product->name }}</p>
                                    <p class="text-[0.75rem] text-brand-slate mt-0.5">
                                        {{ number_format($item->quantity, 0) }} {{ $item->product->unit }} &bull; Distributor: {{ $item->order->distributor->company_name }}
                                        &bull; <span class="text-brand-accent font-semibold">Hemat Rp {{ number_format($item->savings_amount, 0, ',', '.') }}</span>
                                    </p>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="font-extrabold text-brand-primary text-base">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                    <a href="{{ route('payment.show', $item) }}" class="btn-primary text-xs py-2 px-4">
                                        Bayar Sekarang &rarr;
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

        <!-- Orders & Shipments Table -->
        <div class="card-shell">
            <div class="card-core p-4 sm:p-7 space-y-5">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-base font-bold text-brand-dark tracking-tight">Riwayat Pengadaan Kolektif</h2>
                        <p class="text-[0.75rem] text-brand-slate mt-0.5">Semua transaksi pengadaan bahan baku bersama</p>
                    </div>
                </div>

                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <table class="table-premium min-w-[700px]">
                        <thead>
                            <tr>
                                <th>Bahan Baku</th>
                                <th class="text-center">Kuantitas</th>
                                <th class="text-right">Subtotal Grosir</th>
                                <th class="text-right">Penghematan</th>
                                <th class="text-center">Status Bayar</th>
                                <th class="text-center">Pengiriman & Live GPS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($orderItems as $item)
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
                                    <td class="text-right font-bold text-brand-accent">
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
                                            <a href="{{ route('umkm.tracking.show', $shipment) }}" class="btn-ghost text-xs py-1.5 px-3.5 inline-flex items-center gap-1.5 font-bold text-brand-primary hover:bg-brand-mint/60 border-brand-primary/20">
                                                <span>Lacak Posisi Kurir</span>
                                                <span class="w-2 h-2 rounded-full {{ $shipment->status === 'IN_TRANSIT' ? 'bg-brand-emerald animate-ping' : 'bg-brand-primary' }}"></span>
                                            </a>
                                        @else
                                            <span class="text-[0.6875rem] text-brand-slate">Menunggu Penugasan Armada</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-brand-slate text-[0.8125rem]">
                                        Belum ada riwayat pesanan kolektif. Ajukan kebutuhan pertama Anda untuk mulai berhemat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
