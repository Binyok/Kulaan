<x-layouts.app>
    <x-slot name="title">Pantau Pengiriman & Posisi Kurir</x-slot>

    {{-- Forest band: page header --}}
    <div class="-mx-3 sm:-mx-6 lg:-mx-8 -mt-6 sm:-mt-8 mb-6 sm:mb-8">
        <div class="texture-grain bg-brand-forest text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
                <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
                    <div class="max-w-2xl" data-reveal>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[0.625rem] font-semibold bg-white/10 text-[#D8E8DD] uppercase tracking-[0.14em]">
                            Tracking Live GPS
                        </span>
                        <h1 class="font-display font-semibold tracking-tight text-balance text-4xl sm:text-5xl mt-4">
                            Pantau Pengirimanmu
                        </h1>
                        <p class="text-sm text-white/70 leading-relaxed mt-3">
                            Lihat pergerakan kurir pengantar bahan bakumu secara realtime. Kalau lagi diantar, posisinya live di peta.
                        </p>
                    </div>
                    <a href="{{ route('umkm.tracking.live') }}" class="btn-accent text-xs py-3 px-6 font-bold shadow-lg shadow-black/20 shrink-0 w-full lg:w-auto justify-center">
                        Peta Semua Kurir Aktif
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6 sm:space-y-8">
        @php $hasShipments = $shipments->isNotEmpty(); @endphp

        @if ($hasShipments)
            <section class="bg-white rounded-[20px] border border-brand-line p-5 sm:p-7" data-reveal>
                <div class="mb-2">
                    <h2 class="text-base font-bold text-brand-dark tracking-tight">Surat jalan pengiriman</h2>
                    <p class="text-[0.75rem] text-brand-slate mt-0.5">Buka peta pada pengiriman yang sedang berjalan buat memantau posisi kurir realtime.</p>
                </div>

                <div class="divide-y divide-black/[0.05]">
                    @foreach ($shipments as $shipment)
                        <div class="py-4 flex gap-4 items-start hover:bg-brand-cream/60 -mx-2 px-2 sm:-mx-4 sm:px-4 rounded-xl transition-colors">
                            <span class="w-11 h-11 rounded-full {{ $shipment->status === 'IN_TRANSIT' ? 'bg-brand-mint text-brand-primary' : 'bg-brand-cream text-brand-slate' }} flex items-center justify-center shrink-0 mt-0.5">
                                @if ($shipment->status === 'IN_TRANSIT')
                                    <span class="relative flex w-5 h-5">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-primary opacity-40"></span>
                                        <svg class="relative w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11Z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="10" r="2.5" stroke="currentColor" stroke-width="1.8"/></svg>
                                    </span>
                                @else
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 17h-2v-11a1 1 0 0 1 1-1h9v12m-8 0h8m4 0h2v-6l-3-4h-3v10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="7.5" cy="17.5" r="1.8" stroke="currentColor" stroke-width="1.8"/><circle cx="17.5" cy="17.5" r="1.8" stroke="currentColor" stroke-width="1.8"/></svg>
                                @endif
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-mono font-bold text-brand-dark text-sm">{{ $shipment->shipment_number }}</p>
                                        <p class="text-[0.6875rem] text-brand-slate mt-0.5">{{ $shipment->order->items->pluck('product.name')->join(', ') }}</p>
                                        <p class="text-[0.6875rem] text-brand-slate mt-0.5">
                                            {{ $shipment->order->distributor->company_name }}
                                            @if ($shipment->courier)
                                                &bull; {{ $shipment->courier->user->name }} ({{ $shipment->courier->vehicle_type }})
                                            @else
                                                &bull; Belum ditugaskan
                                            @endif
                                        </p>
                                    </div>
                                    <span class="badge {{ $shipment->status === 'IN_TRANSIT' ? 'bg-brand-mint text-brand-emerald' : ($shipment->status === 'DELIVERED' ? 'bg-brand-primary text-white' : 'bg-brand-cream text-brand-slate') }} shrink-0">
                                        {{ $shipment->status === 'IN_TRANSIT' ? 'Live GPS' : ($shipment->status === 'DELIVERED' ? 'Telah Tiba' : 'Menunggu') }}
                                    </span>
                                </div>
                                <div class="mt-2.5">
                                    <a href="{{ route('umkm.tracking.show', $shipment) }}"
                                        class="{{ $shipment->status === 'IN_TRANSIT' ? 'btn-primary' : 'btn-ghost text-brand-primary font-bold' }} text-xs py-2 px-4">
                                        {{ $shipment->status === 'IN_TRANSIT' ? 'Buka Live GPS' : 'Lihat Peta' }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="pt-4 mt-2 border-t border-black/[0.04]">
                    {{ $shipments->links() }}
                </div>
            </section>
        @else
            <section class="bg-white rounded-[20px] border border-brand-line px-6 py-12 text-center" data-reveal>
                <span class="w-14 h-14 rounded-full bg-brand-mint text-brand-primary inline-flex items-center justify-center">
                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11Z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="10" r="2.5" stroke="currentColor" stroke-width="1.8"/></svg>
                </span>
                <p class="font-bold text-brand-dark mt-4">Belum ada surat jalan pengiriman.</p>
                <p class="text-[0.8125rem] text-brand-slate mt-1.5 max-w-md mx-auto">Pengiriman muncul otomatis setelah pembayaran dikonfirmasi dan kurir ditugaskan distributor.</p>
            </section>
        @endif
    </div>
</x-layouts.app>
