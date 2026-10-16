<x-layouts.app>
    <x-slot name="title">Tugas Pengiriman Kurir</x-slot>

    {{-- Forest band: page header --}}
    <div class="-mx-3 sm:-mx-6 lg:-mx-8 -mt-6 sm:-mt-8 mb-6 sm:mb-8">
        <div class="texture-grain bg-brand-forest text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
                <div class="max-w-2xl" data-reveal>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[0.625rem] font-semibold bg-white/10 text-[#D8E8DD] uppercase tracking-[0.14em]">
                        Panel Kurir Armada
                    </span>
                    <h1 class="font-display font-semibold tracking-tight text-balance text-4xl sm:text-5xl mt-4">
                        Tugas Pengiriman
                    </h1>
                    <p class="text-sm text-white/70 leading-relaxed mt-3">
                        Tekan "Mulai Pengiriman" pas berangkat biar GPS-mu live di HP UMKM. Sampai tujuan? Tekan selesai.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6 sm:space-y-8">
        {{-- GPS info strip --}}
        <div class="rounded-[20px] bg-brand-mint/70 border border-brand-primary/15 p-4 flex items-start gap-3 text-xs text-brand-primary" data-reveal>
            <span class="w-8 h-8 rounded-full bg-white text-brand-primary flex items-center justify-center shrink-0 border border-brand-primary/15">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11Z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="10" r="2.5" stroke="currentColor" stroke-width="1.8"/></svg>
            </span>
            <p class="leading-relaxed"><strong>GPS otomatis aktif</strong> begitu kamu tekan "Mulai Pengiriman". Pastikan izin lokasi di browser/HP-mu nyala ya.</p>
        </div>

        @php $hasShipments = $shipments->isNotEmpty(); @endphp

        @if ($hasShipments)
            <section class="bg-white rounded-[20px] border border-brand-line p-5 sm:p-7" data-reveal>
                <div class="divide-y divide-black/[0.05]">
                    @foreach ($shipments as $shipment)
                        <div class="py-4 flex gap-4 items-start hover:bg-brand-cream/60 -mx-2 px-2 sm:-mx-4 sm:px-4 rounded-xl transition-colors" x-data="{
                            isTransmitting: {{ $shipment->status === 'IN_TRANSIT' ? 'true' : 'false' }},
                            watchId: null,
                            startGps() {
                                if ('geolocation' in navigator) {
                                    this.watchId = navigator.geolocation.watchPosition(pos => {
                                        axios.post('/kurir/shipments/{{ $shipment->id }}/gps', {
                                            latitude: pos.coords.latitude,
                                            longitude: pos.coords.longitude,
                                            speed: pos.coords.speed || 0
                                        });
                                    }, err => console.warn(err), { enableHighAccuracy: true });
                                }
                            }
                        }" x-init="if (isTransmitting) startGps();">
                            <span class="w-11 h-11 rounded-full {{ $shipment->status === 'IN_TRANSIT' ? 'bg-brand-mint text-brand-primary' : ($shipment->status === 'DELIVERED' ? 'bg-brand-mint text-brand-emerald' : 'bg-brand-ochre-soft text-brand-brass') }} flex items-center justify-center shrink-0 mt-0.5">
                                @if ($shipment->status === 'IN_TRANSIT')
                                    <span class="relative flex w-5 h-5">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-primary opacity-40"></span>
                                        <svg class="relative w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 17h-2v-11a1 1 0 0 1 1-1h9v12m-8 0h8m4 0h2v-6l-3-4h-3v10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="7.5" cy="17.5" r="1.8" stroke="currentColor" stroke-width="1.8"/><circle cx="17.5" cy="17.5" r="1.8" stroke="currentColor" stroke-width="1.8"/></svg>
                                    </span>
                                @else
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 17h-2v-11a1 1 0 0 1 1-1h9v12m-8 0h8m4 0h2v-6l-3-4h-3v10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="7.5" cy="17.5" r="1.8" stroke="currentColor" stroke-width="1.8"/><circle cx="17.5" cy="17.5" r="1.8" stroke="currentColor" stroke-width="1.8"/></svg>
                                @endif
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-mono font-bold text-brand-dark text-sm">{{ $shipment->shipment_number }}</p>
                                        <p class="font-mono text-[0.6875rem] text-brand-slate mt-0.5">{{ $shipment->order->order_number }}</p>
                                    </div>
                                    <span class="badge {{ $shipment->status === 'IN_TRANSIT' ? 'bg-brand-mint text-brand-emerald' : ($shipment->status === 'DELIVERED' ? 'bg-brand-primary text-white' : 'bg-brand-ochre-soft text-brand-brass') }} shrink-0">
                                        {{ $shipment->status === 'IN_TRANSIT' ? 'GPS Live' : ($shipment->status === 'DELIVERED' ? 'Selesai' : $shipment->status) }}
                                    </span>
                                </div>
                                <div class="mt-2 space-y-1">
                                    @foreach ($shipment->order->items as $item)
                                        <p class="text-[0.75rem]"><span class="font-bold text-brand-dark">{{ $item->umkm->business_name }}</span> <span class="text-brand-slate">&bull; {{ $item->delivery_address }} ({{ $item->delivery_district }})</span></p>
                                    @endforeach
                                </div>
                                <div class="mt-3">
                                    @if ($shipment->status === 'PENDING')
                                        <form method="POST" action="{{ route('kurir.shipments.start', $shipment) }}">
                                            @csrf
                                            <button type="submit" class="btn-primary text-sm font-bold shadow-sm w-full sm:w-auto justify-center py-3 px-6">
                                                Mulai Pengiriman
                                            </button>
                                        </form>
                                    @elseif ($shipment->status === 'IN_TRANSIT')
                                        <form method="POST" action="{{ route('kurir.shipments.complete', $shipment) }}" id="complete-shipment-{{ $shipment->id }}">
                                            @csrf
                                            <button type="button"
                                                    @click="kulaanConfirm({
                                                        title: 'Selesaikan Pengantaran?',
                                                        message: 'Konfirmasi seluruh barang telah sampai dan diterima dengan baik di lokasi UMKM?',
                                                        confirmText: 'Ya, Selesai',
                                                        cancelText: 'Kembali',
                                                        isDanger: false,
                                                        onConfirm: () => document.getElementById('complete-shipment-{{ $shipment->id }}').submit()
                                                    })"
                                                    class="btn-accent text-sm font-bold shadow-sm w-full sm:w-auto justify-center py-3 px-6 cursor-pointer">
                                                Selesaikan Pengantaran
                                            </button>
                                        </form>
                                    @else
                                        <p class="text-xs font-bold text-brand-emerald">Tiba &amp; selesai — makasih!</p>
                                    @endif
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
                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 17h-2v-11a1 1 0 0 1 1-1h9v12m-8 0h8m4 0h2v-6l-3-4h-3v10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="7.5" cy="17.5" r="1.8" stroke="currentColor" stroke-width="1.8"/><circle cx="17.5" cy="17.5" r="1.8" stroke="currentColor" stroke-width="1.8"/></svg>
                </span>
                <p class="font-bold text-brand-dark mt-4">Belum ada tugas pengiriman.</p>
                <p class="text-[0.8125rem] text-brand-slate mt-1.5 max-w-md mx-auto">Tugas baru muncul di sini setelah distributor menugaskanmu ke surat jalan. Santai dulu.</p>
            </section>
        @endif
    </div>
</x-layouts.app>
