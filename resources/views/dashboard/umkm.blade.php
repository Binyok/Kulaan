<x-layouts.app>
    <x-slot name="title">Dashboard UMKM</x-slot>

    <!-- Header Section -->
    <div class="mb-6">
        <div class="bg-gradient-to-r from-brand-forest to-brand-primary text-white rounded-2xl p-6 sm:p-8 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[0.6875rem] font-semibold bg-white/15 text-white">
                        Portal UMKM &bull; {{ $umkm?->district ?? 'Wilayah Anda' }}
                    </span>
                    <h1 class="font-display font-bold text-2xl sm:text-3xl mt-2 tracking-tight">
                        Halo, {{ $umkm?->business_name ?? $user->name }}
                    </h1>
                    <p class="text-xs sm:text-sm text-white/80 mt-1 max-w-xl">
                        Pantau kuota urunan bahan baku, status invoice, dan kurir aktif pengadaan bisnismu di sini.
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('umkm.cart.index') }}" class="btn-ghost bg-white/10 hover:bg-white/20 text-white border-white/20 text-xs font-bold px-4 py-2.5 rounded-xl">
                        Lihat Keranjang
                    </a>
                    <a href="{{ route('umkm.catalog.index') }}" class="btn-accent px-5 py-2.5 text-xs font-bold rounded-xl shadow-sm">
                        Buka Katalog &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Pola F: Angka Statistik BESAR sebagai Hero -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <!-- Stat 1: Total Penghematan (Highlight Emas) -->
        <div class="bg-white rounded-2xl border border-brand-accent/30 p-5 shadow-xs relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-brand-slate uppercase tracking-wider">Total Penghematan</span>
                <span class="w-8 h-8 rounded-lg bg-brand-ochre-soft text-brand-brass flex items-center justify-center font-bold text-xs">
                    Rp
                </span>
            </div>
            <div class="mt-4">
                <div class="font-display font-extrabold text-2xl sm:text-3xl text-brand-brass tabular-nums">
                    Rp {{ number_format($totalSavings, 0, ',', '.') }}
                </div>
                <p class="text-[0.6875rem] text-brand-slate mt-1">
                    Akumulasi hemat vs harga eceran biasa
                </p>
            </div>
        </div>

        <!-- Stat 2: Kebutuhan Sedang Matching -->
        <div class="bg-white rounded-2xl border border-brand-line p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-brand-slate uppercase tracking-wider">Urunan Berjalan</span>
                <span class="w-8 h-8 rounded-lg bg-brand-mint text-brand-primary flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6-4a3 3 0 11-3-3" />
                    </svg>
                </span>
            </div>
            <div class="mt-4">
                <div class="font-display font-extrabold text-2xl sm:text-3xl text-brand-dark tabular-nums">
                    {{ $activeRequestsCount }} <span class="text-sm font-sans font-normal text-brand-slate">kebutuhan</span>
                </div>
                <p class="text-[0.6875rem] text-brand-slate mt-1">
                    Sedang mengumpulkan kuota bersama UMKM lain
                </p>
            </div>
        </div>

        <!-- Stat 3: Tagihan Menunggu Pembayaran -->
        <div class="bg-white rounded-2xl border {{ $unpaidInvoices->count() > 0 ? 'border-brand-brick/40 ring-2 ring-brand-brick/10' : 'border-brand-line' }} p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-brand-slate uppercase tracking-wider">Menunggu Bayar</span>
                <span class="w-8 h-8 rounded-lg {{ $unpaidInvoices->count() > 0 ? 'bg-brand-brick/10 text-brand-brick' : 'bg-brand-mint text-brand-primary' }} flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </span>
            </div>
            <div class="mt-4">
                <div class="font-display font-extrabold text-2xl sm:text-3xl {{ $unpaidInvoices->count() > 0 ? 'text-brand-brick' : 'text-brand-dark' }} tabular-nums">
                    {{ $unpaidInvoices->count() }} <span class="text-sm font-sans font-normal text-brand-slate">tagihan</span>
                </div>
                <p class="text-[0.6875rem] text-brand-slate mt-1">
                    Invoice siap dibayar untuk proses kirim
                </p>
            </div>
        </div>
    </div>

    <!-- Active Shipments Banner -->
    @php
        $activeShipments = $orderItems
            ->flatMap(fn ($item) => $item->order->shipments)
            ->unique('id')
            ->filter(fn ($shipment) => $shipment->status === 'IN_TRANSIT');
        $activeCourierCount = $activeShipments->pluck('courier_id')->filter()->unique()->count();
    @endphp
    @if ($activeShipments->isNotEmpty())
        <div class="rounded-2xl bg-brand-forest text-white p-5 sm:p-6 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="relative flex w-10 h-10 shrink-0">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-emerald opacity-50"></span>
                    <span class="relative inline-flex w-10 h-10 rounded-full bg-brand-emerald text-white items-center justify-center font-bold text-xs">
                        GPS
                    </span>
                </span>
                <div>
                    <h3 class="font-bold text-sm sm:text-base">Pengiriman Sedang Berjalan Menuju Lokasimu</h3>
                    <p class="text-xs text-white/75 mt-0.5">
                        {{ $activeCourierCount }} kurir &bull; {{ $activeShipments->count() }} paket aktif. Pantau pergerakan kurir secara realtime.
                    </p>
                </div>
            </div>
            <a href="{{ route('umkm.tracking.live') }}" class="btn-accent text-xs font-bold px-4 py-2.5 rounded-xl shrink-0 text-center">
                Buka Peta Live &rarr;
            </a>
        </div>
    @endif

    <!-- Action Needed: Fallback Requests (Gagal Kuota) -->
    @if ($fallbackRequests->isNotEmpty())
        <section class="bg-white rounded-2xl border border-brand-accent/40 p-5 sm:p-6 mb-6 shadow-xs">
            <div class="flex items-center gap-2.5 mb-4">
                <span class="w-2.5 h-2.5 rounded-full bg-brand-accent"></span>
                <h2 class="text-sm font-bold text-brand-dark uppercase tracking-wide">
                    Tenggat Berakhir &bull; Butuh Keputusanmu ({{ $fallbackRequests->count() }})
                </h2>
            </div>
            <div class="space-y-3">
                @foreach ($fallbackRequests as $request)
                    <div class="p-4 rounded-xl bg-brand-ochre-soft/80 border border-brand-accent/20 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-brand-dark text-sm">{{ $request->product?->name ?? 'Bahan Baku' }}</p>
                            <p class="text-[0.6875rem] text-brand-slate mt-0.5">
                                Kuantitas: <strong>{{ number_format((float) $request->quantity, 0) }} {{ $request->unit }}</strong>
                                &bull; Mitra: {{ $request->preferredDistributor?->company_name ?? '-' }}
                                &bull; Kuota urunan belum terpenuhi sebelum batas waktu.
                            </p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0 w-full sm:w-auto">
                            <form method="POST" action="{{ route('umkm.requests.direct-buy', $request) }}" class="flex-1 sm:flex-initial">
                                @csrf
                                <button type="submit" class="w-full sm:w-auto px-3.5 py-2 rounded-lg bg-brand-primary hover:bg-brand-primary-hover text-white text-xs font-bold transition-colors">
                                    Bayar Langsung &rarr;
                                </button>
                            </form>
                            <form method="POST" action="{{ route('umkm.requests.retry-matching', $request) }}" class="flex-1 sm:flex-initial">
                                @csrf
                                <button type="submit" class="w-full sm:w-auto px-3.5 py-2 rounded-lg bg-white hover:bg-black/[0.03] text-brand-dark border border-brand-line text-xs font-bold transition-colors">
                                    Ulangi Matching
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <!-- Tagihan Menunggu Pembayaran Section -->
    @if ($unpaidInvoices->isNotEmpty())
        <section class="bg-white rounded-2xl border border-brand-brick/30 p-5 sm:p-6 mb-6 shadow-xs">
            <div class="flex items-center justify-between gap-3 mb-4">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-brand-brick"></span>
                    <h2 class="text-sm font-bold text-brand-dark uppercase tracking-wide">
                        Tagihan Menunggu Pembayaran
                    </h2>
                </div>
                <span class="badge bg-brand-brick/10 text-brand-brick text-xs font-bold">
                    {{ $unpaidInvoices->count() }} invoice
                </span>
            </div>
            <div class="divide-y divide-brand-line/60">
                @foreach ($unpaidInvoices as $item)
                    <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-bold text-brand-dark text-sm">{{ $item->product->name }}</p>
                            <p class="text-xs text-brand-slate mt-0.5">
                                {{ number_format($item->quantity, 0) }} {{ $item->product->unit }} &bull; Distributor: {{ $item->order->distributor->company_name }}
                            </p>
                            @if ($item->savings_amount > 0)
                                <p class="text-[0.6875rem] font-bold text-brand-brass mt-0.5">
                                    Hemat Rp {{ number_format($item->savings_amount, 0, ',', '.') }} vs eceran
                                </p>
                            @endif
                        </div>
                        <div class="flex items-center justify-between sm:justify-end gap-3 shrink-0">
                            <span class="font-extrabold text-brand-primary text-base tabular-nums">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </span>
                            <a href="{{ route('payment.show', $item) }}" class="btn-primary text-xs py-2 px-4 font-bold rounded-xl">
                                Bayar Sekarang &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <!-- Pola F: Aktivitas & Riwayat Terbaru sebagai List Kaya -->
    <section class="bg-white rounded-2xl border border-brand-line p-5 sm:p-7 shadow-xs">
        <div class="flex items-center justify-between gap-3 mb-4">
            <div>
                <h2 class="font-display font-bold text-lg text-brand-dark">Aktivitas Pengadaan Terbaru</h2>
                <p class="text-xs text-brand-slate mt-0.5">Catatan transaksi dan pengadaan bahan baku bisnismu</p>
            </div>
            <a href="{{ route('umkm.orders.index') }}" class="text-xs font-bold text-brand-primary hover:underline">
                Lihat Semua &rarr;
            </a>
        </div>

        @if ($orderItems->isNotEmpty())
            <div class="divide-y divide-brand-line/60">
                @foreach ($orderItems->take(6) as $item)
                    @php $shipment = $item->order->shipments->first(); @endphp
                    <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-brand-cream/40 -mx-2 px-2 rounded-xl transition-colors">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-brand-mint text-brand-primary flex items-center justify-center font-bold text-sm shrink-0">
                                {{ strtoupper(substr($item->product->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="font-bold text-brand-dark text-sm leading-snug">{{ $item->product->name }}</p>
                                <p class="text-[0.6875rem] text-brand-slate mt-0.5">
                                    {{ $item->order->distributor->company_name }} &bull; {{ number_format($item->quantity, 0) }} {{ $item->product->unit }} &bull; {{ $item->created_at->format('d M Y') }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between sm:justify-end gap-3 shrink-0">
                            <div class="text-right">
                                <span class="font-extrabold text-brand-dark text-sm tabular-nums block">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </span>
                                <span class="badge {{ $item->payment_status->badgeClass() }} text-[0.625rem]">
                                    {{ $item->payment_status->label() }}
                                </span>
                            </div>
                            @if ($shipment)
                                <a href="{{ route('umkm.tracking.show', $shipment) }}" class="btn-ghost text-xs py-1.5 px-3 font-semibold rounded-lg text-brand-primary border-brand-primary/20">
                                    Lacak
                                </a>
                            @elseif ($item->payment_status->value === 'UNPAID')
                                <a href="{{ route('payment.show', $item) }}" class="btn-primary text-xs py-1.5 px-3 font-semibold rounded-lg">
                                    Bayar
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-10">
                <div class="w-12 h-12 rounded-2xl bg-brand-mint text-brand-primary flex items-center justify-center mx-auto font-display font-bold text-xl">
                    K
                </div>
                <h3 class="font-display font-bold text-brand-dark text-base mt-3">Belum Ada Riwayat Pesanan</h3>
                <p class="text-xs text-brand-slate mt-1 max-w-sm mx-auto">
                    Kebutuhan yang Anda checkout akan muncul di sini begitu matching kuota selesai.
                </p>
                <a href="{{ route('umkm.catalog.index') }}" class="btn-primary text-xs font-bold px-4 py-2 mt-4 inline-flex">
                    Mulai Belanja di Katalog &rarr;
                </a>
            </div>
        @endif
    </section>
</x-layouts.app>
