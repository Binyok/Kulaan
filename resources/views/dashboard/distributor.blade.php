<x-layouts.app>
    <x-slot name="title">Dashboard Distributor</x-slot>

    {{-- Forest band: page header --}}
    <div class="-mx-3 sm:-mx-6 lg:-mx-8 -mt-6 sm:-mt-8 mb-6 sm:mb-8">
        <div class="texture-grain bg-brand-forest text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
                <div class="max-w-2xl" data-reveal>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[0.625rem] font-semibold bg-white/10 text-[#D8E8DD] uppercase tracking-[0.14em]">
                        Distributor Ekosistem
                    </span>
                    <h1 class="font-display font-semibold tracking-tight text-balance text-4xl sm:text-5xl mt-4">
                        {{ $distributor?->company_name ?? $user->name }}
                    </h1>
                    <p class="text-sm text-white/70 leading-relaxed mt-3">
                        Area operasional: {{ $distributor?->district ?? 'Sidoarjo' }} &bull; Rata-rata kirim {{ $distributor?->delivery_lead_time_days ?? 1 }} hari.
                        Kelola katalog, konfirmasi pesanan, dan tugaskan kurirmu dari sini.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6 sm:space-y-8">
        {{-- Stat strip --}}
        <dl class="bg-white rounded-[20px] border border-brand-line grid grid-cols-1 sm:grid-cols-3 sm:divide-x divide-brand-line/70 overflow-hidden" data-reveal>
            <div class="p-5 sm:p-6 flex items-center gap-4 border-b sm:border-b-0 border-brand-line/60">
                <span class="w-11 h-11 rounded-full bg-brand-mint text-brand-primary flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="m3.3 7 8.7 5 8.7-5M12 22V12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <div class="min-w-0">
                    <dd class="font-display font-extrabold text-2xl sm:text-3xl text-brand-dark tabular-nums leading-none">{{ $catalogCount }}</dd>
                    <dt class="text-[0.6875rem] text-brand-slate font-medium mt-1.5">Produk aktif di katalog</dt>
                </div>
            </div>
            <div class="p-5 sm:p-6 flex items-center gap-4 border-b sm:border-b-0 border-brand-line/60">
                <span class="w-11 h-11 rounded-full {{ $pendingOrdersCount > 0 ? 'bg-brand-ochre-soft text-brand-brass' : 'bg-brand-mint text-brand-primary' }} flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 17h6m-5 4h4M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 8h6M9 12h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                </span>
                <div class="min-w-0">
                    <dd class="font-display font-extrabold text-2xl sm:text-3xl tabular-nums leading-none {{ $pendingOrdersCount > 0 ? 'text-brand-brass' : 'text-brand-primary' }}">{{ $pendingOrdersCount }}</dd>
                    <dt class="text-[0.6875rem] text-brand-slate font-medium mt-1.5">Pesanan perlu diproses</dt>
                </div>
            </div>
            <div class="p-5 sm:p-6 flex items-center gap-4">
                <span class="w-11 h-11 rounded-full bg-brand-ochre-soft text-brand-brass flex items-center justify-center shrink-0 border border-brand-accent/25">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.9 6.26 6.6.56-5 4.36 1.5 6.45L12 16.9 5.99 19.63l1.5-6.45-5-4.36 6.6-.56L12 2z"/></svg>
                </span>
                <div class="min-w-0">
                    @if ($reviewCount > 0)
                        <dd class="font-display font-extrabold text-2xl sm:text-3xl text-brand-dark tabular-nums leading-none">{{ number_format($distributor?->rating ?? 0, 1, ',', '') }}<span class="text-base text-brand-slate font-semibold">/5</span></dd>
                        <dt class="text-[0.6875rem] text-brand-slate font-medium mt-1.5">Rating dari {{ $reviewCount }} ulasan UMKM</dt>
                    @else
                        <dd class="font-display font-extrabold text-2xl sm:text-3xl text-brand-slate leading-none">&ndash;</dd>
                        <dt class="text-[0.6875rem] text-brand-slate font-medium mt-1.5">Belum ada ulasan UMKM</dt>
                    @endif
                </div>
            </div>
        </dl>

        {{-- Recent orders: rich rows --}}
        <section class="bg-white rounded-[20px] border border-brand-line p-5 sm:p-7" data-reveal>
            <div class="flex items-center justify-between gap-3 mb-2">
                <div>
                    <h2 class="text-base font-bold text-brand-dark tracking-tight">Pesanan kolektif masuk</h2>
                    <p class="text-[0.75rem] text-brand-slate mt-0.5">Pesanan terbaru dari grup pilihan UMKM</p>
                </div>
                <a href="{{ route('distributor.orders.index') }}" class="btn-ghost text-xs py-2 px-4 font-bold text-brand-primary shrink-0">
                    Lihat Semua
                </a>
            </div>

            @php $hasOrders = $orders->isNotEmpty(); @endphp

            @if ($hasOrders)
                <div class="divide-y divide-black/[0.05]">
                    @foreach ($orders as $order)
                        <a href="{{ route('distributor.orders.index') }}" class="py-4 flex gap-4 items-center hover:bg-brand-cream/60 -mx-2 px-2 sm:-mx-4 sm:px-4 rounded-xl transition-colors group">
                            <span class="w-11 h-11 rounded-full bg-brand-mint text-brand-primary flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M17 20h5v-2a4 4 0 0 0-3-3.87M9 20H4v-2a4 4 0 0 1 3-3.87m6-1.13a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm6-4a3 3 0 1 1-3-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="font-mono font-bold text-brand-dark text-sm group-hover:text-brand-primary transition-colors">{{ $order->order_number }}</p>
                                <p class="text-[0.6875rem] text-brand-slate mt-0.5 truncate">{{ $order->items->pluck('umkm.business_name')->join(', ') }} &bull; {{ number_format($order->total_quantity, 0) }} unit</p>
                            </div>
                            <p class="font-extrabold text-brand-primary tabular-nums text-sm shrink-0 hidden sm:block">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                            <span class="badge {{ $order->status->badgeClass() }} shrink-0">{{ $order->status->label() }}</span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="py-10 text-center">
                    <span class="w-14 h-14 rounded-full bg-brand-mint text-brand-primary inline-flex items-center justify-center">
                        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 17h6m-5 4h4M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <p class="font-bold text-brand-dark mt-4">Belum ada pesanan kolektif masuk.</p>
                    <p class="text-[0.8125rem] text-brand-slate mt-1.5 max-w-md mx-auto">Pastikan katalogmu lengkap biar gampang kepilih sistem.</p>
                    <a href="{{ route('distributor.products.index') }}" class="btn-primary text-xs mt-5">Kelola Katalog Produk</a>
                </div>
            @endif
        </section>
    </div>
</x-layouts.app>
