<x-layouts.app>
    <x-slot name="title">Pantau Pengiriman & Posisi Kurir</x-slot>

    <!-- Header Section -->
    <div class="mb-6">
        <div class="bg-gradient-to-r from-brand-forest to-brand-primary text-white rounded-2xl p-5 sm:p-7 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[0.6875rem] font-semibold bg-white/15 text-white">
                        Live Tracking GPS
                    </span>
                    <h1 class="font-display font-bold text-2xl sm:text-3xl mt-2 tracking-tight">Pantau Pengiriman Bahan Baku</h1>
                    <p class="text-xs sm:text-sm text-white/80 mt-1">
                        Pantau posisi armada kurir pengantar bahan bakumu secara realtime di peta.
                    </p>
                </div>
                <a href="{{ route('umkm.tracking.live') }}" class="btn-accent text-xs font-bold px-4 py-2.5 rounded-xl shrink-0 text-center shadow-sm">
                    Peta Semua Kurir Aktif &rarr;
                </a>
            </div>
        </div>
    </div>

    @php
        $hasShipments = $shipments->isNotEmpty();
        $isFiltering = $tab !== 'all' || $q !== '' || $from || $to;
    @endphp

    <!-- Filter & Tabs -->
    <section class="bg-white rounded-2xl border border-brand-line p-4 sm:p-5 mb-6 space-y-4 shadow-xs">
        <div class="flex flex-wrap gap-2" role="tablist">
            @foreach (['all' => 'Semua', 'active' => 'Sedang Jalan', 'history' => 'Riwayat Tiba'] as $key => $label)
                <a href="{{ route('umkm.tracking.index', array_filter(['tab' => $key === 'all' ? null : $key, 'q' => $q ?: null, 'from' => $from, 'to' => $to])) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition-colors {{ $tab === $key ? 'bg-brand-primary text-white shadow-xs' : 'bg-brand-cream text-brand-slate hover:text-brand-dark' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('umkm.tracking.index') }}" class="grid grid-cols-1 sm:grid-cols-[1fr_auto_auto_auto] gap-2">
            @if ($tab !== 'all')
                <input type="hidden" name="tab" value="{{ $tab }}">
            @endif
            <input type="search" name="q" value="{{ $q }}" placeholder="Cari surat jalan, produk, distributor..." class="input-field text-xs">
            <input type="date" name="from" value="{{ $from }}" class="input-field text-xs">
            <input type="date" name="to" value="{{ $to }}" class="input-field text-xs">
            <div class="flex gap-2">
                <button type="submit" class="btn-primary text-xs px-4 py-2 font-bold rounded-xl justify-center">Cari</button>
                @if ($isFiltering)
                    <a href="{{ route('umkm.tracking.index') }}" class="btn-ghost text-xs px-3 py-2 font-bold rounded-xl">Reset</a>
                @endif
            </div>
        </form>
    </section>

    <!-- List Pengiriman (Pola C Vertikal) -->
    @if ($hasShipments)
        <div class="space-y-3">
            @foreach ($shipments as $shipment)
                @php $inTransit = $shipment->status === 'IN_TRANSIT'; @endphp
                <article class="bg-white rounded-2xl border {{ $inTransit ? 'border-brand-primary/40 ring-2 ring-brand-primary/10' : 'border-brand-line' }} p-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-3.5 min-w-0">
                        <span class="w-11 h-11 rounded-2xl {{ $inTransit ? 'bg-brand-forest text-white' : 'bg-brand-cream text-brand-slate' }} flex items-center justify-center shrink-0">
                            @if ($inTransit)
                                <span class="relative flex w-4 h-4">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-accent opacity-75"></span>
                                    <span class="relative inline-flex w-4 h-4 rounded-full bg-brand-accent"></span>
                                </span>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                </svg>
                            @endif
                        </span>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span class="font-mono text-xs font-bold text-brand-dark">{{ $shipment->shipment_number }}</span>
                                <span class="badge {{ $inTransit ? 'bg-brand-mint text-brand-emerald font-bold' : ($shipment->status === 'DELIVERED' ? 'bg-brand-primary text-white' : 'bg-brand-cream text-brand-slate') }}">
                                    {{ $inTransit ? 'Sedang Diantar' : ($shipment->status === 'DELIVERED' ? 'Telah Sampai' : 'Diproses') }}
                                </span>
                            </div>
                            <p class="font-bold text-brand-dark text-sm">{{ $shipment->order->items->pluck('product.name')->join(', ') }}</p>
                            <p class="text-xs text-brand-slate mt-0.5">
                                Mitra {{ $shipment->order->distributor->company_name }}
                                @if ($shipment->courier)
                                    &bull; Kurir: <strong class="text-brand-dark">{{ $shipment->courier->user->name }}</strong> ({{ $shipment->courier->vehicle_type }})
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between sm:justify-end gap-3 shrink-0 pt-3 sm:pt-0 border-t sm:border-t-0 border-brand-line/60">
                        <a href="{{ route('umkm.tracking.show', $shipment) }}"
                           class="{{ $inTransit ? 'btn-primary' : 'btn-ghost text-brand-primary border border-brand-primary/20' }} text-xs font-bold py-2.5 px-4 rounded-xl">
                            {{ $inTransit ? 'Buka Live GPS &rarr;' : 'Lihat Rute Peta' }}
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="pt-4">{{ $shipments->links() }}</div>
    @else
        <section class="bg-white rounded-2xl border border-brand-line p-10 sm:p-14 text-center max-w-md mx-auto shadow-xs">
            <div class="w-12 h-12 rounded-2xl bg-brand-mint text-brand-primary flex items-center justify-center mx-auto font-display font-bold text-xl">K</div>
            <h2 class="font-display font-bold text-brand-dark text-base mt-3">Tidak Ada Pengiriman</h2>
            <p class="text-xs text-brand-slate mt-1 max-w-sm mx-auto">
                Pengiriman aktif akan tampil di sini saat distributor menugaskan armada kurir.
            </p>
        </section>
    @endif
</x-layouts.app>
