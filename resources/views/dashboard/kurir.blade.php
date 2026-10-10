<x-layouts.app>
    <x-slot name="title">Dashboard Kurir</x-slot>

    {{-- Forest band: page header --}}
    <div class="-mx-3 sm:-mx-6 lg:-mx-8 -mt-6 sm:-mt-8 mb-6 sm:mb-8">
        <div class="texture-grain bg-brand-forest text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
                <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
                    <div class="max-w-2xl" data-reveal>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[0.625rem] font-semibold bg-white/10 text-[#D8E8DD] uppercase tracking-[0.14em]">
                            Kurir Armada
                        </span>
                        <h1 class="font-display font-semibold tracking-tight text-balance text-4xl sm:text-5xl mt-4">
                            Halo, {{ $user->name }}
                        </h1>
                        <p class="text-sm text-white/70 leading-relaxed mt-3">
                            Armada: {{ $courier?->vehicle_type ?? 'Motor' }} &bull; Plat: {{ $courier?->vehicle_number ?? '-' }}.
                            Tugas baru muncul di bawah — tekan mulai biar GPS-mu live.
                        </p>
                    </div>
                    <a href="{{ route('kurir.shipments.index') }}" class="btn-accent text-sm py-3.5 px-7 justify-center shadow-lg shadow-black/20 shrink-0 w-full lg:w-auto">
                        Buka Daftar Tugas
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6 sm:space-y-8">
        {{-- Stat strip --}}
        <dl class="bg-white rounded-[20px] border border-brand-line grid grid-cols-1 sm:grid-cols-3 sm:divide-x divide-brand-line/70 overflow-hidden" data-reveal>
            <div class="p-5 sm:p-6 flex items-center gap-4 border-b sm:border-b-0 border-brand-line/60">
                <span class="w-11 h-11 rounded-full bg-brand-ochre-soft text-brand-brass flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 17h6m-5 4h4M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 8h6M9 12h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                </span>
                <div class="min-w-0">
                    <dd class="font-display font-extrabold text-2xl sm:text-3xl text-brand-dark tabular-nums leading-none">{{ $assignedShipmentsCount }}</dd>
                    <dt class="text-[0.6875rem] text-brand-slate font-medium mt-1.5">Tugas baru menunggu</dt>
                </div>
            </div>
            <div class="p-5 sm:p-6 flex items-center gap-4 border-b sm:border-b-0 border-brand-line/60">
                <span class="w-11 h-11 rounded-full {{ $activeDelivery ? 'bg-brand-mint text-brand-primary' : 'bg-brand-cream text-brand-slate' }} flex items-center justify-center shrink-0">
                    @if ($activeDelivery)
                        <span class="relative flex w-5 h-5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-primary opacity-40"></span>
                            <svg class="relative w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11Z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="10" r="2.5" stroke="currentColor" stroke-width="1.8"/></svg>
                        </span>
                    @else
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11Z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="10" r="2.5" stroke="currentColor" stroke-width="1.8"/></svg>
                    @endif
                </span>
                <div class="min-w-0">
                    <dd class="font-display font-extrabold text-xl sm:text-2xl leading-none {{ $activeDelivery ? 'text-brand-emerald' : 'text-brand-slate' }}">{{ $activeDelivery ? 'GPS Aktif' : 'Standby' }}</dd>
                    <dt class="text-[0.6875rem] text-brand-slate font-medium mt-1.5">{{ $activeDelivery ? 'Sedang dalam perjalanan' : 'Menunggu tugas' }}</dt>
                </div>
            </div>
            <div class="p-5 sm:p-6 flex items-center gap-4">
                <span class="w-11 h-11 rounded-full bg-brand-mint text-brand-primary flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <div class="min-w-0">
                    <dd class="font-display font-extrabold text-2xl sm:text-3xl text-brand-primary tabular-nums leading-none">{{ $completedCount }}</dd>
                    <dt class="text-[0.6875rem] text-brand-slate font-medium mt-1.5">Pengiriman selesai</dt>
                </div>
            </div>
        </dl>

        {{-- Active delivery --}}
        @if ($activeDelivery)
            <section class="rounded-[20px] bg-brand-forest text-white p-5 sm:p-6" data-reveal>
                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    <span class="relative flex w-11 h-11 shrink-0">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-emerald opacity-40"></span>
                        <span class="relative inline-flex w-11 h-11 rounded-full bg-brand-emerald/20 border border-brand-emerald/50 items-center justify-center">
                            <svg class="w-5 h-5 text-brand-emerald" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 17h-2v-11a1 1 0 0 1 1-1h9v12m-8 0h8m4 0h2v-6l-3-4h-3v10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="7.5" cy="17.5" r="1.8" stroke="currentColor" stroke-width="1.8"/><circle cx="17.5" cy="17.5" r="1.8" stroke="currentColor" stroke-width="1.8"/></svg>
                        </span>
                    </span>
                    <div class="min-w-0 flex-1">
                        <h2 class="font-extrabold text-base">Lagi ngantar sekarang</h2>
                        <p class="font-mono text-xs text-white/70 mt-1">{{ $activeDelivery->shipment_number }} &bull; Pesanan {{ $activeDelivery->order->order_number }}</p>
                        <p class="text-xs text-white/70 mt-0.5">Tujuan: {{ $activeDelivery->order->items->pluck('umkm.business_name')->join(', ') }}</p>
                    </div>
                    <a href="{{ route('kurir.shipments.index') }}" class="btn-accent text-xs py-2.5 px-5 font-bold shrink-0 w-full sm:w-auto justify-center">
                        Buka Panel Kurir
                    </a>
                </div>
            </section>
        @endif

        {{-- Task panel CTA --}}
        <section class="bg-brand-sand/70 rounded-[20px] p-5 sm:p-7 flex flex-col sm:flex-row sm:items-center gap-4" data-reveal>
            <span class="w-11 h-11 rounded-full bg-white text-brand-primary flex items-center justify-center shrink-0 border border-brand-line">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 17h6m-5 4h4M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
            <div class="min-w-0 flex-1">
                <h3 class="font-bold text-brand-dark text-base">Panel tugas pengiriman</h3>
                <p class="text-xs text-brand-slate mt-0.5">Mulai perjalanan biar GPS live, atau konfirmasi kalau barang sudah sampai.</p>
            </div>
            <a href="{{ route('kurir.shipments.index') }}" class="btn-primary text-xs py-3 px-6 font-bold w-full sm:w-auto justify-center shrink-0">
                Buka Daftar Tugas
            </a>
        </section>
    </div>
</x-layouts.app>
