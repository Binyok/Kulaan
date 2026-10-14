<x-layouts.app>
    <x-slot name="title">Pengajuan Saya</x-slot>

    <!-- Header Marketplace-style -->
    <div class="mb-6">
        <div class="bg-gradient-to-r from-brand-forest to-brand-primary text-white rounded-2xl p-5 sm:p-7 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[0.6875rem] font-semibold bg-white/15 text-white">
                        Pengadaan Kolektif
                    </span>
                    <h1 class="font-display font-bold text-2xl sm:text-3xl mt-2 tracking-tight">Pengajuan Kebutuhanku</h1>
                    <p class="text-xs sm:text-sm text-white/80 mt-1">
                        Semua pengajuan ke distributor pilihanmu. Sistem akan menggabungkan kuota UMKM lain secara otomatis.
                    </p>
                </div>
                <a href="{{ route('umkm.catalog.index') }}" class="btn-accent text-xs font-bold px-5 py-2.5 rounded-xl shrink-0 text-center shadow-sm">
                    + Ajukan Lewat Katalog
                </a>
            </div>
        </div>
    </div>

    @if ($requests->isNotEmpty())
        <!-- Group by status untuk UX lebih jelas -->
        @php
            $fallbackItems = $requests->filter(fn($r) => $r->status->value === 'FAILED_MATCH_FALLBACK');
            $pendingItems  = $requests->filter(fn($r) => $r->status->value === 'PENDING_MATCH');
            $otherItems    = $requests->filter(fn($r) => !in_array($r->status->value, ['FAILED_MATCH_FALLBACK', 'PENDING_MATCH']));
        @endphp

        <!-- Fallback: Butuh Tindakan (Paling Atas karena Urgent) -->
        @if ($fallbackItems->isNotEmpty())
            <div class="mb-4">
                <h2 class="text-xs font-bold text-brand-brick uppercase tracking-wider mb-3 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-brand-brick"></span>
                    Butuh Keputusanmu ({{ $fallbackItems->count() }})
                </h2>
                <div class="space-y-3">
                    @foreach ($fallbackItems as $req)
                        <article class="bg-white rounded-2xl border border-brand-brick/30 shadow-xs overflow-hidden">
                            <div class="p-4 sm:p-5">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2 mb-1">
                                            <span class="badge {{ $req->status->badgeClass() }}">{{ $req->status->label() }}</span>
                                            <span class="badge bg-brand-cream text-brand-slate">{{ $req->product->category->name ?? 'Bahan Baku' }}</span>
                                        </div>
                                        <h3 class="font-bold text-brand-dark text-base sm:text-lg">{{ $req->product->name }}</h3>
                                    </div>
                                    <span class="text-sm font-extrabold text-brand-primary tabular-nums shrink-0">
                                        {{ number_format($req->quantity, 0) }} <span class="text-xs font-normal text-brand-slate">{{ $req->unit }}</span>
                                    </span>
                                </div>
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-[0.6875rem] text-brand-slate">
                                    <span>Distributor: <strong class="text-brand-dark">{{ $req->preferredDistributor?->company_name ?? '-' }}</strong></span>
                                    <span>Tenggat: {{ $req->deadline?->format('d M Y, H:i') }}</span>
                                </div>
                            </div>
                            <div class="px-4 sm:px-5 pb-4 pt-3 border-t border-brand-line/60 bg-brand-ochre-soft/40 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                                <p class="text-[0.6875rem] text-brand-slate">
                                    Kuota urunan belum tercapai. Pilih langkah selanjutnya.
                                </p>
                                <div class="flex items-center gap-2 w-full sm:w-auto">
                                    <form method="POST" action="{{ route('umkm.requests.direct-buy', $req) }}" class="flex-1 sm:flex-initial">
                                        @csrf
                                        <button type="submit" class="w-full sm:w-auto px-4 py-2 rounded-xl bg-brand-primary hover:bg-brand-primary-hover text-white text-xs font-bold transition-colors">
                                            Bayar Langsung &rarr;
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('umkm.requests.retry-matching', $req) }}" class="flex-1 sm:flex-initial">
                                        @csrf
                                        <button type="submit" class="w-full sm:w-auto px-4 py-2 rounded-xl bg-white hover:bg-black/[0.03] border border-brand-line text-brand-dark text-xs font-bold transition-colors">
                                            Ulangi Matching
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Pending Match: Aktif Menunggu Kuota -->
        @if ($pendingItems->isNotEmpty())
            <div class="mb-4">
                <h2 class="text-xs font-bold text-brand-slate uppercase tracking-wider mb-3 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-brand-accent"></span>
                    Sedang Mengumpulkan Kuota ({{ $pendingItems->count() }})
                </h2>
                <div class="space-y-3">
                    @foreach ($pendingItems as $req)
                        <article class="bg-white rounded-2xl border border-brand-line shadow-xs overflow-hidden">
                            <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2 mb-1">
                                        <span class="badge {{ $req->status->badgeClass() }}">{{ $req->status->label() }}</span>
                                        <span class="badge bg-brand-cream text-brand-slate">{{ $req->product->category->name ?? 'Bahan Baku' }}</span>
                                    </div>
                                    <h3 class="font-bold text-brand-dark text-base">{{ $req->product->name }}</h3>
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1.5 text-[0.6875rem] text-brand-slate">
                                        <span><strong class="text-brand-dark">{{ number_format($req->quantity, 0) }} {{ $req->unit }}</strong></span>
                                        <span>Distributor: <strong class="text-brand-dark">{{ $req->preferredDistributor?->company_name ?? '-' }}</strong></span>
                                        <span class="{{ $req->isExpired() ? 'text-brand-brick font-bold' : 'text-brand-slate' }}">
                                            @if ($req->isExpired())
                                                Batas waktu berakhir
                                            @else
                                                Sisa {{ $req->deadline->diffForHumans() }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('umkm.requests.cancel', $req) }}" onsubmit="return confirm('Batalkan pengajuan ini?');" class="shrink-0">
                                    @csrf
                                    <button type="submit" class="text-xs text-brand-brick hover:underline font-semibold min-h-9">
                                        Batalkan
                                    </button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Riwayat Lainnya (ORDERED, CANCELLED, MATCHED, dll.) -->
        @if ($otherItems->isNotEmpty())
            <div class="mb-4">
                <h2 class="text-xs font-bold text-brand-slate uppercase tracking-wider mb-3 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-brand-slate/50"></span>
                    Riwayat Pengajuan
                </h2>
                <div class="space-y-2">
                    @foreach ($otherItems as $req)
                        <div class="bg-white rounded-2xl border border-brand-line/60 p-4 flex flex-wrap items-center justify-between gap-3 shadow-xs">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-0.5">
                                    <span class="badge {{ $req->status->badgeClass() }}">{{ $req->status->label() }}</span>
                                </div>
                                <p class="font-bold text-brand-dark text-sm">{{ $req->product->name }}</p>
                                <p class="text-[0.6875rem] text-brand-slate mt-0.5">
                                    {{ number_format($req->quantity, 0) }} {{ $req->unit }}
                                    &bull; {{ $req->preferredDistributor?->company_name ?? '-' }}
                                    &bull; {{ $req->deadline?->format('d M Y') }}
                                </p>
                            </div>
                            @if ($req->matchingGroup)
                                <span class="text-[0.6875rem] text-brand-primary font-bold">
                                    {{ $req->matchingGroup->group_code }}
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="pt-2">{{ $requests->links() }}</div>
    @else
        <section class="bg-white rounded-2xl border border-brand-line p-10 sm:p-16 text-center max-w-lg mx-auto shadow-xs">
            <div class="w-14 h-14 rounded-2xl bg-brand-mint text-brand-primary flex items-center justify-center mx-auto font-display font-bold text-2xl">K</div>
            <h2 class="font-display font-bold text-brand-dark text-lg sm:text-xl mt-4">Belum Ada Pengajuan</h2>
            <p class="text-xs sm:text-sm text-brand-slate mt-2 leading-relaxed">
                Pilih bahan baku dari katalog, tentukan distributor, dan sistem akan mengurunkan kuota bersama UMKM lain.
            </p>
            <a href="{{ route('umkm.catalog.index') }}" class="btn-primary inline-flex text-sm font-bold px-5 py-2.5 mt-5">
                Buka Katalog &rarr;
            </a>
        </section>
    @endif
</x-layouts.app>
