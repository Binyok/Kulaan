<x-layouts.app>
    <x-slot name="title">Pengajuan Saya</x-slot>

    @php
        $displayName = $umkm->business_name ?: 'UMKM';
        $initials = strtoupper(substr($displayName, 0, 2));
        $cartCount = app(\App\Services\CartService::class)->count();

        $fallbackItems = $requests->filter(fn ($r) => $r->status->value === 'FAILED_MATCH_FALLBACK');
        $pendingItems = $requests->filter(fn ($r) => $r->status->value === 'PENDING_MATCH');
    @endphp

    {{-- MOBILE --}}
    <div class="lg:hidden pb-24">
        {{-- greeting header --}}
        <div class="px-4 pt-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="relative" x-data="{ menuOpen: false }">
                    <button type="button" @click="menuOpen = !menuOpen" class="w-11 h-11 rounded-2xl bg-forest text-white flex items-center justify-center font-extrabold text-sm cursor-pointer">{{ $initials }}</button>
                    <div x-show="menuOpen" @click.away="menuOpen = false" x-cloak class="absolute left-0 mt-2 w-48 bg-white border border-line rounded-2xl shadow-xl p-2 z-40 text-xs">
                        <a href="{{ route('profile.complete') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-ink hover:bg-cream font-medium">Profil Saya</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2 px-3 py-2.5 rounded-xl text-danger hover:bg-danger/10 font-bold transition text-left cursor-pointer">Keluar</button>
                        </form>
                    </div>
                </div>
                <div>
                    <p class="text-[11px] text-muted">Selamat pagi,</p>
                    <p class="text-[15px] font-extrabold leading-tight">{{ $displayName }}</p>
                </div>
            </div>
            <x-notification-bell />
        </div>

        {{-- page header --}}
        <div class="px-4 mt-5">
            <h1 class="text-xl font-extrabold tracking-tight">Pengajuan Saya</h1>
            <p class="text-xs text-muted mt-0.5">Pantau status pengajuan kolektifmu</p>
        </div>

        <div class="px-4 mt-4 mb-5">
            <div class="bg-white border border-line rounded-2xl p-1.5 flex gap-1">
                <a href="{{ route('umkm.requests.index', ['tab' => 'aktif']) }}" @if ($tab === 'aktif') aria-current="page" @endif class="flex-1 text-center text-xs font-bold py-2.5 rounded-xl transition {{ $tab === 'aktif' ? 'bg-forest text-white' : 'text-muted hover:bg-cream' }}">Aktif</a>
                <a href="{{ route('umkm.requests.index', ['tab' => 'riwayat']) }}" @if ($tab === 'riwayat') aria-current="page" @endif class="flex-1 text-center text-xs font-bold py-2.5 rounded-xl transition {{ $tab === 'riwayat' ? 'bg-forest text-white' : 'text-muted hover:bg-cream' }}">Selesai</a>
            </div>
        </div>

        @if ($requests->isEmpty())
            <div class="px-4 mt-5">
                <section class="bg-white rounded-3xl border border-line p-10 text-center shadow-sm">
                    <div class="w-14 h-14 rounded-2xl bg-forest/10 text-forest flex items-center justify-center mx-auto font-extrabold text-2xl">K</div>
                    @if ($tab === 'riwayat')
                        <h2 class="font-extrabold text-ink text-lg mt-4">Belum Ada Riwayat Pengajuan</h2>
                        <p class="text-xs text-muted mt-2 leading-relaxed">
                            Pengajuan yang selesai, dibatalkan, atau gagal matching akan tampil di sini.
                        </p>
                    @else
                        <h2 class="font-extrabold text-ink text-lg mt-4">Belum Ada Pengajuan Aktif</h2>
                        <p class="text-xs text-muted mt-2 leading-relaxed">
                            Pilih bahan baku dari katalog, tentukan distributor, dan sistem akan mengurunkan kuota bersama UMKM lain.
                        </p>
                    @endif
                    <a href="{{ route('umkm.catalog.index') }}" class="bg-forest text-white inline-flex text-sm font-bold px-5 py-3 rounded-2xl mt-5">Buka Katalog</a>
                </section>
            </div>
        @elseif ($tab === 'riwayat')
            <div class="px-4 mt-5 space-y-3">
                @foreach ($requests as $req)
                    <div class="bg-white border border-line rounded-3xl p-4">
                        <span class="inline-flex items-center text-[10px] font-bold uppercase tracking-wide px-2.5 py-1.5 rounded-full {{ $req->status->badgeClass() }}">
                            {{ $req->status->label() }}
                        </span>
                        <p class="font-extrabold text-sm mt-2.5">{{ $req->product->name }}</p>
                        <p class="text-xs text-muted mt-0.5">
                            {{ number_format($req->quantity, 0) }} {{ $req->unit }} &middot; {{ $req->preferredDistributor?->company_name ?? '-' }}
                            @if ($req->matchingGroup && (float) $req->matchingGroup->target_moq > 0)
                                &middot; kuota {{ number_format((float) $req->matchingGroup->total_quantity, 0) }}/{{ number_format((float) $req->matchingGroup->target_moq, 0) }} {{ $req->unit }}
                            @elseif ($req->deadline)
                                &middot; {{ $req->deadline->format('d M Y') }}
                            @endif
                        </p>
                    </div>
                @endforeach
            </div>
        @else
            {{-- sedang mengumpulkan kuota --}}
            @if ($pendingItems->isNotEmpty())
                <div class="px-4 mt-5">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-extrabold">Sedang Mengumpulkan Kuota</h2>
                        <span class="text-[11px] font-bold bg-brass-soft text-brass px-2.5 py-1 rounded-full">{{ $pendingItems->count() }} aktif</span>
                    </div>

                    @foreach ($pendingItems as $req)
                        <div class="mt-3 bg-white border border-line rounded-3xl p-4 shadow-sm">
                            <span class="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wide bg-brass-soft text-brass px-2.5 py-1.5 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-brass animate-pulse"></span>
                                {{ $req->status->label() }}
                            </span>
                            <div class="flex gap-3 mt-3">
                                <div class="w-14 h-14 rounded-2xl flex items-center justify-center font-extrabold text-xl shrink-0 {{ $req->isExpired() ? 'bg-brass/10 text-brass' : 'bg-forest/10 text-forest' }}">{{ strtoupper(substr($req->product->name, 0, 1)) }}</div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-extrabold text-sm">{{ $req->product->name }}</p>
                                    <p class="text-xs text-muted mt-0.5">{{ number_format($req->quantity, 0) }} {{ $req->unit }} &middot; Distributor: <span class="font-semibold text-ink">{{ $req->preferredDistributor?->company_name ?? '-' }}</span></p>
                                    <p class="text-[11px] font-bold mt-1.5 flex items-center gap-1 {{ $req->isExpired() ? 'text-danger' : 'text-brass' }}">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                                        @if ($req->isExpired())
                                            Batas waktu berakhir
                                        @else
                                            Sisa {{ $req->deadline->diffForHumans() }}
                                        @endif
                                    </p>
                                </div>
                            </div>

                            @if ($req->matchingGroup && (float) $req->matchingGroup->target_moq > 0)
                                @php
                                    $gathered = (float) $req->matchingGroup->total_quantity;
                                    $target = (float) $req->matchingGroup->target_moq;
                                    $progressPercent = $target > 0 ? min(100, (int) round($gathered / $target * 100)) : 0;
                                    $remaining = max(0, $target - $gathered);
                                @endphp
                                <div class="mt-3">
                                    <div class="flex items-center justify-between text-[11px]">
                                        <span class="font-semibold text-muted">Terkumpul {{ number_format($gathered, 0) }} dari {{ number_format($target, 0) }} {{ $req->unit }}</span>
                                        <span class="font-bold {{ $req->isExpired() ? 'text-danger' : 'text-brass' }}">kurang {{ number_format($remaining, 0) }} {{ $req->unit }} lagi</span>
                                    </div>
                                    <div class="h-2.5 bg-cream border border-line rounded-full mt-1.5 overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-brass to-forest rounded-full" style="width: {{ $progressPercent }}%"></div>
                                    </div>
                                    <p class="text-[11px] text-muted mt-1.5">menuju minimum pemesanan</p>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('umkm.requests.cancel', $req) }}" id="cancel-form-m-{{ $req->id }}" class="shrink-0">
                                @csrf
                                <button type="button"
                                        onclick="window.kulaanConfirm({
                                            title: 'Batalkan Pengajuan?',
                                            message: 'Apakah Anda yakin ingin membatalkan pengajuan {{ addslashes($req->product->name) }} ini? Kuota Anda akan ditarik dari grup pengadaan.',
                                            confirmText: 'Ya, Batalkan',
                                            cancelText: 'Kembali',
                                            isDanger: true,
                                            onConfirm: () => document.getElementById('cancel-form-m-{{ $req->id }}').submit()
                                        })"
                                        class="mt-1 text-xs font-bold text-danger min-h-11 px-2 cursor-pointer">
                                    Batalkan
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- kuota belum tercapai --}}
            @if ($fallbackItems->isNotEmpty())
                <div class="px-4 mt-6">
                    <h2 class="text-sm font-extrabold">Kuota Belum Tercapai</h2>
                    @foreach ($fallbackItems as $req)
                        <div class="mt-3 bg-white border-2 border-dashed border-brass/60 rounded-3xl p-4">
                            <div class="flex gap-3">
                                <div class="w-12 h-12 rounded-2xl bg-brass/10 text-brass flex items-center justify-center font-extrabold text-lg shrink-0">{{ strtoupper(substr($req->product->name, 0, 1)) }}</div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-extrabold text-sm">{{ $req->product->name }}</p>
                                    <p class="text-xs text-muted mt-0.5">{{ number_format($req->quantity, 0) }} {{ $req->unit }} &middot; Distributor: <span class="font-semibold text-ink">{{ $req->preferredDistributor?->company_name ?? '-' }}</span></p>
                                </div>
                            </div>
                            <p class="text-xs text-muted mt-3 leading-relaxed">Kuota urunan belum tercapai. Pilih langkah selanjutnya.</p>
                            <div class="flex gap-2 mt-3">
                                <form method="POST" action="{{ route('umkm.requests.direct-buy', $req) }}" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full bg-forest text-white text-xs font-bold py-3 rounded-2xl shadow-sm cursor-pointer">Bayar Langsung</button>
                                </form>
                                <form method="POST" action="{{ route('umkm.requests.retry-matching', $req) }}" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full bg-white border-2 border-forest text-forest text-xs font-bold py-3 rounded-2xl cursor-pointer">Ulangi Matching</button>
                                </form>
                            </div>
                            <form method="POST" action="{{ route('umkm.requests.cancel', $req) }}" id="cancel-form-fb-m-{{ $req->id }}" class="mt-2 text-center">
                                @csrf
                                <button type="button"
                                        onclick="window.kulaanConfirm({
                                            title: 'Batalkan Pengajuan?',
                                            message: 'Apakah Anda yakin ingin membatalkan pengajuan {{ addslashes($req->product->name) }} ini?',
                                            confirmText: 'Ya, Batalkan',
                                            cancelText: 'Kembali',
                                            isDanger: true,
                                            onConfirm: () => document.getElementById('cancel-form-fb-m-{{ $req->id }}').submit()
                                        })"
                                        class="text-xs font-bold text-danger py-1 px-2 cursor-pointer">
                                    Batalkan Pengajuan
                                </button>
                            </form>
                            <p class="text-[11px] text-muted mt-2.5 leading-relaxed"><span class="font-bold text-ink">Bayar Langsung:</span> bayar di harga saat ini tanpa menunggu kuota terkumpul.</p>
                        </div>
                    @endforeach
                </div>
            @endif

            <p class="text-[11px] text-muted text-center mt-5 px-4 leading-relaxed">Semua harga bersifat estimasi &mdash;<br>harga final mengikuti total quantity kelompok kulaan.</p>
        @endif

        <div class="px-4 mt-4">{{ $requests->links() }}</div>
    </div>

    {{-- DESKTOP --}}
    <div class="hidden lg:flex min-h-screen">
        <aside class="w-64 shrink-0 bg-white border-r border-line flex flex-col sticky top-0 h-screen">
            <div class="flex items-center gap-2.5 px-6 h-[72px] border-b border-line">
                <span class="w-10 h-10 rounded-2xl bg-forest flex items-center justify-center text-white">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.25 7.5l-.63 10.63a2.25 2.25 0 0 1-2.25 2.12H6.63a2.25 2.25 0 0 1-2.25-2.12L3.75 7.5M10 11.25h4M3.38 7.5h17.25c.62 0 1.12-.5 1.12-1.12v-1.5C21.75 4.26 21.25 3.75 20.63 3.75H3.38c-.62 0-1.13.5-1.13 1.12v1.5c0 .63.5 1.13 1.12 1.13Z"/></svg>
                </span>
                <span class="text-xl font-extrabold tracking-tight">Kulaan</span>
            </div>
            <x-umkm.sidebar-nav active="requests" />
            <div class="p-4 border-t border-line">
                <a href="{{ route('profile.complete') }}" class="flex items-center gap-3 px-2">
                    <span class="w-10 h-10 rounded-2xl bg-forest text-white flex items-center justify-center font-extrabold text-xs">{{ $initials }}</span>
                    <div class="min-w-0">
                        <p class="text-sm font-bold truncate">{{ $displayName }}</p>
                        <p class="text-[11px] text-muted">{{ $umkm->city ?? 'Sidoarjo' }}, {{ $umkm->province ?? 'Jawa Timur' }}</p>
                    </div>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2.5 text-xs font-bold text-danger bg-danger/10 hover:bg-danger/15 rounded-xl transition cursor-pointer">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3h4a3 3 0 0 1 3 3v1"/></svg>
                        Keluar
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex-1 min-w-0">
            <header class="bg-white/70 backdrop-blur border-b border-line sticky top-0 z-10">
                <div class="px-8 h-[72px] flex items-center gap-4">
                    <div>
                        <h1 class="text-lg font-extrabold leading-tight">Selamat pagi, {{ $displayName }}</h1>
                        <p class="text-xs text-muted">{{ now()->translatedFormat('l, d F Y') }}</p>
                    </div>
                    <div class="ml-auto flex items-center gap-3">
                        <form method="GET" action="{{ route('umkm.catalog.index') }}" class="hidden lg:flex items-center gap-2 bg-white border border-line rounded-2xl pl-4 pr-2 py-2 w-72">
                            <svg class="w-4 h-4 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21 21-5.2-5.2m0 0A7.5 7.5 0 1 0 5.2 5.2a7.5 7.5 0 0 0 10.6 10.6Z"/></svg>
                            <input name="q" class="flex-1 bg-transparent text-sm outline-none placeholder:text-muted/70" placeholder="Cari pesanan, produk...">
                        </form>
                        <x-notification-bell />
                    </div>
                </div>
            </header>

            <main class="px-8 py-6 max-w-6xl">
                <div class="mt-2 flex items-end justify-between">
                    <div>
                        <h1 class="text-3xl font-extrabold tracking-tight">Pengajuan Saya</h1>
                        <p class="text-sm text-muted mt-1">Pantau status pengajuan kolektifmu &mdash; setelah checkout, setiap item menunggu matching di sini.</p>
                    </div>
                    @if ($tab === 'aktif')
                        <span class="text-xs font-bold bg-brass-soft text-brass px-3 py-1.5 rounded-full">{{ $pendingItems->count() }} pengajuan aktif</span>
                    @endif
                </div>

                <div class="bg-white border border-line rounded-2xl p-1.5 flex gap-1 w-full max-w-md mt-5">
                    <a href="{{ route('umkm.requests.index', ['tab' => 'aktif']) }}" @if ($tab === 'aktif') aria-current="page" @endif class="flex-1 text-center text-sm font-bold py-2.5 rounded-xl transition {{ $tab === 'aktif' ? 'bg-forest text-white' : 'text-muted hover:bg-cream' }}">Aktif</a>
                    <a href="{{ route('umkm.requests.index', ['tab' => 'riwayat']) }}" @if ($tab === 'riwayat') aria-current="page" @endif class="flex-1 text-center text-sm font-bold py-2.5 rounded-xl transition {{ $tab === 'riwayat' ? 'bg-forest text-white' : 'text-muted hover:bg-cream' }}">Selesai</a>
                </div>

                @if ($requests->isEmpty())
                    <section class="bg-white rounded-3xl border border-line p-10 sm:p-16 text-center max-w-lg mx-auto shadow-sm mt-6">
                        <div class="w-14 h-14 rounded-2xl bg-forest/10 text-forest flex items-center justify-center mx-auto font-extrabold text-2xl">K</div>
                        @if ($tab === 'riwayat')
                            <h2 class="font-extrabold text-ink text-lg sm:text-xl mt-4">Belum Ada Riwayat Pengajuan</h2>
                            <p class="text-xs sm:text-sm text-muted mt-2 leading-relaxed">
                                Pengajuan yang selesai, dibatalkan, atau gagal matching akan tampil di sini.
                            </p>
                        @else
                            <h2 class="font-extrabold text-ink text-lg sm:text-xl mt-4">Belum Ada Pengajuan Aktif</h2>
                            <p class="text-xs sm:text-sm text-muted mt-2 leading-relaxed">
                                Pilih bahan baku dari katalog, tentukan distributor, dan sistem akan mengurunkan kuota bersama UMKM lain.
                            </p>
                        @endif
                        <a href="{{ route('umkm.catalog.index') }}" class="bg-forest text-white inline-flex text-sm font-bold px-5 py-3 rounded-2xl mt-5">Buka Katalog</a>
                    </section>
                @elseif ($tab === 'riwayat')
                    <div class="mt-6 max-w-3xl space-y-4">
                        @foreach ($requests as $req)
                            <div class="bg-white border border-line rounded-3xl p-5">
                                <span class="inline-flex items-center text-[11px] font-bold uppercase tracking-wide px-3 py-1.5 rounded-full {{ $req->status->badgeClass() }}">
                                    {{ $req->status->label() }}
                                </span>
                                <p class="font-extrabold mt-2.5">{{ $req->product->name }}</p>
                                <p class="text-xs text-muted mt-1">
                                    {{ number_format($req->quantity, 0) }} {{ $req->unit }} &middot; {{ $req->preferredDistributor?->company_name ?? '-' }}
                                    @if ($req->matchingGroup && (float) $req->matchingGroup->target_moq > 0)
                                        &middot; kuota {{ number_format((float) $req->matchingGroup->total_quantity, 0) }}/{{ number_format((float) $req->matchingGroup->target_moq, 0) }} {{ $req->unit }}
                                    @elseif ($req->deadline)
                                        &middot; {{ $req->deadline->format('d M Y') }}
                                    @endif
                                </p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="grid lg:grid-cols-3 gap-6 mt-6">
                        <div class="lg:col-span-2">
                            @if ($pendingItems->isNotEmpty())
                                <h2 class="text-base font-extrabold">Sedang Mengumpulkan Kuota</h2>

                                @foreach ($pendingItems as $req)
                                    <div class="mt-4 bg-white border border-line rounded-3xl p-6 shadow-sm">
                                        <div class="flex items-start justify-between gap-4">
                                            <span class="inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wide bg-brass-soft text-brass px-3 py-1.5 rounded-full">
                                                <span class="w-1.5 h-1.5 rounded-full bg-brass animate-pulse"></span>
                                                {{ $req->status->label() }}
                                            </span>
                                            <form method="POST" action="{{ route('umkm.requests.cancel', $req) }}" id="cancel-form-d-{{ $req->id }}" class="shrink-0">
                                                @csrf
                                                <button type="button"
                                                        onclick="window.kulaanConfirm({
                                                            title: 'Batalkan Pengajuan?',
                                                            message: 'Apakah Anda yakin ingin membatalkan pengajuan {{ addslashes($req->product->name) }} ini? Kuota Anda akan ditarik dari grup pengadaan.',
                                                            confirmText: 'Ya, Batalkan',
                                                            cancelText: 'Kembali',
                                                            isDanger: true,
                                                            onConfirm: () => document.getElementById('cancel-form-d-{{ $req->id }}').submit()
                                                        })"
                                                        class="text-xs font-bold text-danger cursor-pointer">
                                                    Batalkan
                                                </button>
                                            </form>
                                        </div>
                                        <div class="flex gap-4 mt-4">
                                            <div class="w-16 h-16 rounded-2xl flex items-center justify-center font-extrabold text-2xl shrink-0 {{ $req->isExpired() ? 'bg-brass/10 text-brass' : 'bg-forest/10 text-forest' }}">{{ strtoupper(substr($req->product->name, 0, 1)) }}</div>
                                            <div class="flex-1">
                                                <p class="font-extrabold text-lg">{{ $req->product->name }}</p>
                                                <p class="text-sm text-muted mt-1">
                                                    {{ number_format($req->quantity, 0) }} {{ $req->unit }} &middot; Distributor: <span class="font-semibold text-ink">{{ $req->preferredDistributor?->company_name ?? '-' }}</span>
                                                    @if ($req->preferredDistributor?->rating)
                                                        <span class="text-muted">&middot; rating {{ number_format((float) $req->preferredDistributor->rating, 1) }}</span>
                                                    @endif
                                                </p>
                                                <p class="text-xs font-bold mt-2 flex items-center gap-1.5 {{ $req->isExpired() ? 'text-danger' : 'text-brass' }}">
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                                                    @if ($req->isExpired())
                                                        Batas waktu berakhir
                                                    @else
                                                        Sisa {{ $req->deadline->diffForHumans() }}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>

                                        @if ($req->matchingGroup && (float) $req->matchingGroup->target_moq > 0)
                                            @php
                                                $gathered = (float) $req->matchingGroup->total_quantity;
                                                $target = (float) $req->matchingGroup->target_moq;
                                                $progressPercent = $target > 0 ? min(100, (int) round($gathered / $target * 100)) : 0;
                                                $remaining = max(0, $target - $gathered);
                                            @endphp
                                            <div class="mt-4">
                                                <div class="flex items-center justify-between text-xs">
                                                    <span class="font-semibold text-muted">Terkumpul {{ number_format($gathered, 0) }} dari {{ number_format($target, 0) }} {{ $req->unit }}</span>
                                                    <span class="font-bold {{ $req->isExpired() ? 'text-danger' : 'text-brass' }}">kurang {{ number_format($remaining, 0) }} {{ $req->unit }} lagi menuju minimum pemesanan</span>
                                                </div>
                                                <div class="h-3 bg-cream border border-line rounded-full mt-2 overflow-hidden">
                                                    <div class="h-full bg-gradient-to-r from-brass to-forest rounded-full" style="width: {{ $progressPercent }}%"></div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <div class="space-y-6">
                            @if ($fallbackItems->isNotEmpty())
                                <div>
                                    <h2 class="text-base font-extrabold">Kuota Belum Tercapai</h2>
                                    @foreach ($fallbackItems as $req)
                                        <div class="mt-4 bg-white border-2 border-dashed border-brass/60 rounded-3xl p-5">
                                            <div class="flex gap-3">
                                                <div class="w-12 h-12 rounded-2xl bg-brass/10 text-brass flex items-center justify-center font-extrabold text-lg shrink-0">{{ strtoupper(substr($req->product->name, 0, 1)) }}</div>
                                                <div>
                                                    <p class="font-extrabold text-sm">{{ $req->product->name }}</p>
                                                    <p class="text-xs text-muted mt-0.5">{{ number_format($req->quantity, 0) }} {{ $req->unit }} &middot; {{ $req->preferredDistributor?->company_name ?? '-' }}</p>
                                                </div>
                                            </div>
                                            <p class="text-xs text-muted mt-3 leading-relaxed">Kuota urunan belum tercapai. Pilih langkah selanjutnya.</p>
                                             <div class="flex flex-col gap-2 mt-3">
                                                <form method="POST" action="{{ route('umkm.requests.direct-buy', $req) }}">
                                                    @csrf
                                                    <button type="submit" class="w-full bg-forest text-white text-sm font-bold py-3 rounded-2xl shadow-sm cursor-pointer">Bayar Langsung</button>
                                                </form>
                                                <form method="POST" action="{{ route('umkm.requests.retry-matching', $req) }}">
                                                    @csrf
                                                    <button type="submit" class="w-full bg-white border-2 border-forest text-forest text-sm font-bold py-3 rounded-2xl cursor-pointer">Ulangi Matching</button>
                                                </form>
                                                <form method="POST" action="{{ route('umkm.requests.cancel', $req) }}" id="cancel-form-fb-d-{{ $req->id }}" class="text-center mt-1">
                                                    @csrf
                                                    <button type="button"
                                                            onclick="window.kulaanConfirm({
                                                                title: 'Batalkan Pengajuan?',
                                                                message: 'Apakah Anda yakin ingin membatalkan pengajuan {{ addslashes($req->product->name) }} ini?',
                                                                confirmText: 'Ya, Batalkan',
                                                                cancelText: 'Kembali',
                                                                isDanger: true,
                                                                onConfirm: () => document.getElementById('cancel-form-fb-d-{{ $req->id }}').submit()
                                                            })"
                                                            class="text-xs font-bold text-danger py-1 px-2 cursor-pointer">
                                                        Batalkan Pengajuan
                                                    </button>
                                                </form>
                                            </div>
                                            <p class="text-[11px] text-muted mt-3 leading-relaxed"><span class="font-bold text-ink">Bayar Langsung:</span> bayar di harga saat ini tanpa menunggu kuota terkumpul.</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <p class="text-xs text-muted text-center mt-10">Semua harga bersifat estimasi &mdash; harga final mengikuti total quantity kelompok kulaan.</p>
                @endif

                <div class="pt-4">{{ $requests->links() }}</div>
            </main>
        </div>
    </div>

    {{-- bottom tab --}}
    <x-umkm.mobile-tab active="requests" />
</x-layouts.app>
