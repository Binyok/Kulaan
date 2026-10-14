<x-layouts.app>
    <x-slot name="title">Pengajuan Saya</x-slot>

    {{-- Forest band: page header --}}
    <div class="-mx-3 sm:-mx-6 lg:-mx-8 -mt-6 sm:-mt-8 mb-6 sm:mb-8">
        <div class="texture-grain bg-brand-forest text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
                <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
                    <div class="max-w-2xl" data-reveal>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[0.625rem] font-semibold bg-white/10 text-[#D8E8DD] uppercase tracking-[0.14em]">
                            Pengadaan Kolektif
                        </span>
                        <h1 class="font-display font-semibold tracking-tight text-balance text-4xl sm:text-5xl mt-4">
                            Pengajuan Saya
                        </h1>
                        <p class="text-sm text-white/70 leading-relaxed mt-3">
                            Semua pengajuanmu ke distributor pilihan. Pengajuan baru dibuat lewat katalog — kamu yang pilih distributornya.
                        </p>
                    </div>
                    <a href="{{ route('umkm.catalog.index') }}" class="btn-accent text-sm py-3.5 px-7 justify-center shadow-lg shadow-black/20 shrink-0 w-full lg:w-auto">
                        Pilih di Katalog
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6 sm:space-y-8">
        @php $hasRequests = $requests->isNotEmpty(); @endphp

        @if ($hasRequests)
            <section class="bg-white rounded-[20px] border border-brand-line p-5 sm:p-7" data-reveal>
                <div class="mb-2">
                    <h2 class="text-base font-bold text-brand-dark tracking-tight">Daftar pengajuanmu</h2>
                    <p class="text-[0.75rem] text-brand-slate mt-0.5">Pantau progres pengumpulan kuota dan status grup pembelian kolektifmu.</p>
                </div>

                <div class="divide-y divide-black/[0.05]">
                    @foreach ($requests as $req)
                        <div class="py-4 flex gap-4 items-start hover:bg-brand-cream/60 -mx-2 px-2 sm:-mx-4 sm:px-4 rounded-xl transition-colors">
                            <span class="w-11 h-11 rounded-full {{ $req->isExpired() ? 'bg-brand-brick/10 text-brand-brick' : 'bg-brand-mint text-brand-primary' }} flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="m3.3 7 8.7 5 8.7-5M12 22V12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-bold text-brand-dark text-sm">{{ $req->product->name }}</p>
                                        <p class="text-[0.6875rem] text-brand-slate mt-0.5">{{ $req->product->category->name }} &bull; {{ number_format($req->quantity, 0) }} {{ $req->unit }}</p>
                                        <p class="text-[0.6875rem] text-brand-slate mt-0.5">Distributor pilihan: <strong class="text-brand-dark">{{ $req->preferredDistributor->company_name ?? '-' }}</strong></p>
                                    </div>
                                    <span class="badge {{ $req->status->badgeClass() }} shrink-0">{{ $req->status->label() }}</span>
                                </div>
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5 mt-2.5">
                                    <span class="badge {{ $req->urgency->value === 'tinggi' ? 'bg-brand-brick/10 text-brand-brick' : ($req->urgency->value === 'sedang' ? 'bg-brand-ochre-soft text-brand-brass' : 'bg-brand-mint text-brand-emerald') }}">
                                        {{ $req->urgency->label() }}
                                    </span>
                                    <span class="text-[0.6875rem] text-brand-slate">
                                        @if ($req->isExpired())
                                            <span class="text-brand-brick font-semibold">Batas waktu tercapai</span>
                                        @else
                                            Sisa {{ $req->deadline->diffForHumans() }}
                                        @endif
                                        &bull; {{ $req->delivery_district }}
                                    </span>
                                    @if ($req->status->value === 'PENDING_MATCH')
                                        <form method="POST" action="{{ route('umkm.requests.cancel', $req) }}" onsubmit="return confirm('Yakin ingin membatalkan pengajuan kebutuhan ini?');" class="ml-auto">
                                            @csrf
                                            <button type="submit" class="min-h-11 px-3 text-xs text-brand-brick font-semibold hover:underline cursor-pointer">
                                                Batalkan
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="pt-4 mt-2 border-t border-black/[0.04]">
                    {{ $requests->links() }}
                </div>
            </section>
        @else
            <section class="bg-white rounded-[20px] border border-brand-line px-6 py-12 text-center" data-reveal>
                <span class="w-14 h-14 rounded-full bg-brand-mint text-brand-primary inline-flex items-center justify-center">
                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 5v14m-7-7h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                </span>
                <p class="font-bold text-brand-dark mt-4">Belum ada pengajuan kebutuhan.</p>
                <p class="text-[0.8125rem] text-brand-slate mt-1.5 max-w-md mx-auto">Buka katalog, bandingkan distributor, dan ajukan ke pilihanmu.</p>
                <a href="{{ route('umkm.catalog.index') }}" class="btn-primary text-sm mt-5">
                    Buka Katalog
                </a>
            </section>
        @endif
    </div>
</x-layouts.app>
