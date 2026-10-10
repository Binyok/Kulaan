<x-layouts.app>
    <x-slot name="title">Dashboard UMKM</x-slot>

    {{-- Forest band: page header --}}
    <div class="-mx-3 sm:-mx-6 lg:-mx-8 -mt-6 sm:-mt-8 mb-6 sm:mb-8">
        <div class="texture-grain bg-brand-forest text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
                <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
                    <div class="max-w-2xl" data-reveal>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[0.625rem] font-semibold bg-white/10 text-[#D8E8DD] uppercase tracking-[0.14em]">
                            Portal Pengadaan Mandiri
                        </span>
                        <h1 class="font-display font-semibold tracking-tight text-balance text-4xl sm:text-5xl mt-4">
                            Halo, {{ $umkm?->business_name ?? $user->name }}
                        </h1>
                        <p class="text-sm text-white/70 leading-relaxed mt-3">
                            Wilayah {{ $umkm?->district ?? 'Banjarmasin' }} &bull; Sektor {{ $umkm?->business_type ?? 'Kuliner' }}.
                            Ajukan kebutuhan, gabung kuota grosir, dan pantau semuanya dari sini.
                        </p>
                    </div>
                    <a href="{{ route('umkm.requests.index') }}" class="btn-accent text-sm py-3.5 px-7 justify-center shadow-lg shadow-black/20 shrink-0 w-full lg:w-auto">
                        Ajukan Kebutuhan Baru
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6 sm:space-y-8">
        {{-- Stat strip --}}
        <dl class="bg-white rounded-[20px] border border-brand-line grid grid-cols-1 sm:grid-cols-3 sm:divide-x divide-brand-line/70 overflow-hidden" data-reveal>
            <div class="p-5 sm:p-6 flex items-center gap-4 border-b sm:border-b-0 border-brand-line/60">
                <span class="w-11 h-11 rounded-full bg-brand-mint text-brand-primary flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M17 20h5v-2a4 4 0 0 0-3-3.87M9 20H4v-2a4 4 0 0 1 3-3.87m6-1.13a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm6-4a3 3 0 1 1-3-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <div class="min-w-0">
                    <dd class="font-display font-extrabold text-2xl sm:text-3xl text-brand-dark tabular-nums leading-none">{{ $activeRequestsCount }}</dd>
                    <dt class="text-[0.6875rem] text-brand-slate font-medium mt-1.5">Kebutuhan sedang matching</dt>
                </div>
            </div>
            <div class="p-5 sm:p-6 flex items-center gap-4 border-b sm:border-b-0 border-brand-line/60">
                <span class="w-11 h-11 rounded-full {{ $unpaidInvoices->count() > 0 ? 'bg-red-50 text-brand-brick' : 'bg-brand-mint text-brand-primary' }} flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4m0 5v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-5m18 0H9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <div class="min-w-0">
                    <dd class="font-display font-extrabold text-2xl sm:text-3xl tabular-nums leading-none {{ $unpaidInvoices->count() > 0 ? 'text-brand-brick' : 'text-brand-primary' }}">{{ $unpaidInvoices->count() }}</dd>
                    <dt class="text-[0.6875rem] text-brand-slate font-medium mt-1.5">Tagihan menunggu pembayaran</dt>
                </div>
            </div>
            <div class="p-5 sm:p-6 flex items-center gap-4">
                <span class="w-11 h-11 rounded-full bg-brand-ochre-soft text-brand-brass flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 1v22m5-18H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <div class="min-w-0">
                    <dd class="font-display font-extrabold text-2xl sm:text-3xl text-brand-brass tabular-nums leading-none">Rp {{ number_format($totalSavings, 0, ',', '.') }}</dd>
                    <dt class="text-[0.6875rem] text-brand-slate font-medium mt-1.5">Total penghematan kamu</dt>
                </div>
            </div>
        </dl>

        {{-- Live GPS banner --}}
        @php
            $activeShipments = $orderItems
                ->flatMap(fn ($item) => $item->order->shipments)
                ->unique('id')
                ->filter(fn ($shipment) => $shipment->status === 'IN_TRANSIT');
            $activeCourierCount = $activeShipments->pluck('courier_id')->filter()->unique()->count();
        @endphp
        @if ($activeShipments->isNotEmpty())
            <div class="rounded-[20px] bg-brand-forest text-white p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-6" data-reveal>
                <span class="relative flex w-11 h-11 shrink-0">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-emerald opacity-40"></span>
                    <span class="relative inline-flex w-11 h-11 rounded-full bg-brand-emerald/20 border border-brand-emerald/50 items-center justify-center">
                        <svg class="w-5 h-5 text-brand-emerald" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11Z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="10" r="2.5" stroke="currentColor" stroke-width="1.8"/></svg>
                    </span>
                </span>
                <div class="min-w-0 flex-1">
                    <h3 class="font-extrabold text-base">Pengiriman sedang berjalan</h3>
                    <p class="text-xs text-white/70 mt-1">{{ $activeCourierCount }} kurir &bull; {{ $activeShipments->count() }} paket menuju areamu. Semua posisi kurir tampil di satu peta.</p>
                </div>
                <a href="{{ route('umkm.tracking.live') }}" class="btn-accent text-xs py-2.5 px-5 font-bold shrink-0 w-full sm:w-auto justify-center">
                    Buka Peta Live
                </a>
            </div>
        @endif

        {{-- Unpaid invoices: sand section, rich rows --}}
        @if ($unpaidInvoices->count() > 0)
            <section class="bg-brand-sand/70 rounded-[20px] p-5 sm:p-7" data-reveal>
                <div class="flex items-center gap-2.5 mb-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-brand-accent"></span>
                    <h2 class="text-sm font-bold text-brand-dark uppercase tracking-wide">Tagihan perlu dibayar</h2>
                    <span class="ml-auto text-[0.6875rem] font-bold text-brand-brass bg-white px-2.5 py-1 rounded-full border border-brand-accent/30">{{ $unpaidInvoices->count() }} tagihan</span>
                </div>
                <div class="divide-y divide-brand-line/60">
                    @foreach ($unpaidInvoices as $item)
                        <div class="py-4 flex gap-4 items-start">
                            <span class="w-11 h-11 rounded-full bg-white text-brand-brass flex items-center justify-center shrink-0 border border-brand-accent/25 mt-0.5">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4m0 5v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-5m18 0H9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-bold text-brand-dark text-sm">{{ $item->product->name }}</p>
                                        <p class="text-[0.6875rem] text-brand-slate mt-0.5">{{ number_format($item->quantity, 0) }} {{ $item->product->unit }} &bull; {{ $item->order->distributor->company_name }}</p>
                                    </div>
                                    <p class="font-extrabold text-brand-primary tabular-nums shrink-0">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                                </div>
                                <div class="flex items-center justify-between gap-3 mt-2.5">
                                    <p class="text-[0.6875rem] font-semibold text-brand-brass">Hemat Rp {{ number_format($item->savings_amount, 0, ',', '.') }}</p>
                                    <a href="{{ route('payment.show', $item) }}" class="btn-primary text-xs py-2 px-4 font-bold shrink-0">Bayar Sekarang</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Fallback requests --}}
        @if ($fallbackRequests->isNotEmpty())
            <section class="bg-white rounded-[20px] border border-brand-line p-5 sm:p-7" data-reveal>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-2">
                    <div>
                        <h2 class="text-sm font-bold text-brand-dark uppercase tracking-wide">Jalur pembelian cepat</h2>
                        <p class="text-[0.75rem] text-brand-slate mt-1">Kuota kolektif belum tercapai sebelum tenggat. Ajukan ulang dengan deadline baru biar kembali di-matching.</p>
                    </div>
                    <a href="{{ route('umkm.requests.index') }}" class="btn-ghost text-xs py-2 px-4 font-bold shrink-0 self-start sm:self-auto">Ajukan Ulang</a>
                </div>
                <div class="divide-y divide-black/[0.05]">
                    @foreach ($fallbackRequests as $request)
                        <div class="py-3.5 flex items-center gap-4">
                            <span class="w-10 h-10 rounded-full bg-red-50 text-brand-brick flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/><path d="M12 7v5l3 3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-brand-dark text-sm">{{ $request->product?->name ?? 'Produk' }}</p>
                                <p class="text-[0.6875rem] text-brand-slate mt-0.5">{{ number_format((float) $request->quantity, 0) }} {{ $request->unit }} &bull; Tenggat {{ $request->deadline?->format('d M Y, H:i') }}</p>
                            </div>
                            <span class="badge {{ $request->status->badgeClass() }} shrink-0">{{ $request->status->label() }}</span>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Order history: rich rows --}}
        <section class="bg-white rounded-[20px] border border-brand-line p-5 sm:p-7" data-reveal>
            <div class="mb-2">
                <h2 class="text-base font-bold text-brand-dark tracking-tight">Riwayat pengadaan kolektif</h2>
                <p class="text-[0.75rem] text-brand-slate mt-0.5">Semua transaksi pengadaan bahan baku bareng</p>
            </div>

            @php $hasOrders = $orderItems->isNotEmpty(); @endphp

            @if ($hasOrders)
                <div class="divide-y divide-black/[0.05]">
                    @foreach ($orderItems as $item)
                        @php $shipment = $item->order->shipments->first(); @endphp
                        <div class="py-4 flex gap-4 items-start hover:bg-brand-cream/60 -mx-2 px-2 sm:-mx-4 sm:px-4 rounded-xl transition-colors">
                            <span class="w-11 h-11 rounded-full bg-brand-mint text-brand-primary flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="m3.3 7 8.7 5 8.7-5M12 22V12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-bold text-brand-dark text-sm">{{ $item->product->name }}</p>
                                        <p class="text-[0.6875rem] text-brand-slate mt-0.5">{{ $item->order->distributor->company_name }} &bull; {{ number_format($item->quantity, 0) }} {{ $item->product->unit }}</p>
                                    </div>
                                    <span class="badge {{ $item->payment_status->badgeClass() }} shrink-0">{{ $item->payment_status->label() }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-3 mt-2.5">
                                    <div class="text-[0.75rem]">
                                        <span class="font-extrabold text-brand-primary tabular-nums">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                        <span class="text-brand-brass font-semibold ml-2">Hemat Rp {{ number_format($item->savings_amount, 0, ',', '.') }}</span>
                                    </div>
                                    @if ($shipment)
                                        <a href="{{ route('umkm.tracking.show', $shipment) }}" class="text-xs font-bold text-brand-primary hover:underline shrink-0">Lacak kurir &rarr;</a>
                                    @else
                                        <span class="text-[0.6875rem] text-brand-slate shrink-0">Menunggu armada</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-10 text-center">
                    <span class="w-14 h-14 rounded-full bg-brand-mint text-brand-primary inline-flex items-center justify-center">
                        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="m3.3 7 8.7 5 8.7-5M12 22V12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <p class="font-bold text-brand-dark mt-4">Belum ada riwayat pesanan kolektif.</p>
                    <p class="text-[0.8125rem] text-brand-slate mt-1.5 max-w-md mx-auto">Ajukan kebutuhan pertamamu dan hemat bareng UMKM lain.</p>
                    <a href="{{ route('umkm.requests.index') }}" class="btn-primary text-xs mt-5">Ajukan Kebutuhan Pertama</a>
                </div>
            @endif
        </section>
    </div>
</x-layouts.app>
