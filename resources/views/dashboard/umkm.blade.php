<x-layouts.app>
    <x-slot name="title">Dashboard UMKM</x-slot>

    @php
        $displayName = $umkm?->business_name ?? $user->name;
        $initials = strtoupper(substr($user->name, 0, 2));
        $cartCount = app(\App\Services\CartService::class)->count();
    @endphp

    {{-- MOBILE --}}
    <div class="lg:hidden pb-24">
        <div class="px-4 pt-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="relative" x-data="{ menuOpen: false }">
                    <button type="button" @click="menuOpen = !menuOpen" class="w-11 h-11 rounded-2xl bg-forest text-white flex items-center justify-center font-extrabold text-sm cursor-pointer">{{ strtoupper(substr($user->name, 0, 2)) }}</button>
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

        <div class="px-4 mt-4">
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-forest-deep via-forest to-forest-dark text-white p-5">
                <div class="absolute -right-10 -top-12 w-44 h-44 rounded-full bg-white/10"></div>
                <div class="absolute right-16 -bottom-14 w-36 h-36 rounded-full bg-brass/20"></div>
                <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-brass-soft">Butuh stok minggu ini?</p>
                <h2 class="text-lg font-extrabold mt-1 leading-snug">Belanja bareng,<br>hemat hingga 20%.</h2>
                <div class="flex gap-2.5 mt-4 relative">
                    <a href="{{ route('umkm.catalog.index') }}" class="flex-1 h-11 rounded-xl bg-white text-forest-dark text-xs font-bold flex items-center justify-center">Buka Katalog</a>
                    <a href="{{ route('umkm.cart.index') }}" class="flex-1 h-11 rounded-xl bg-white/10 border border-white/25 text-white text-xs font-bold flex items-center justify-center">Lihat Keranjang</a>
                </div>
            </div>
        </div>

        <div class="px-4 mt-4">
            <div class="bg-white rounded-3xl border-2 border-brass/40 p-5 relative overflow-hidden">
                <div class="absolute -right-6 -top-6 w-28 h-28 rounded-full bg-brass-soft"></div>
                <div class="flex items-center justify-between relative">
                    <p class="text-xs font-bold uppercase tracking-wider text-muted">Total Penghematan</p>
                    <span class="w-9 h-9 rounded-xl bg-brass-soft text-brass flex items-center justify-center text-xs font-extrabold">Rp</span>
                </div>
                <p class="text-[2rem] font-extrabold text-brass tabular-nums mt-2 relative">Rp {{ number_format($totalSavings, 0, ',', '.') }}</p>
                <p class="text-[11px] text-muted mt-1 relative">vs harga eceran &middot; 3 bulan terakhir</p>
            </div>
        </div>

        <div class="px-4 mt-3 grid grid-cols-3 gap-2.5">
            <div class="bg-white rounded-3xl border border-line p-3.5">
                <p class="text-2xl font-extrabold">{{ $activeRequestsCount }}</p>
                <p class="text-[10px] text-muted font-semibold mt-1 leading-tight">Permintaan aktif</p>
                <span class="inline-block mt-2 bg-success/10 text-success text-[10px] font-bold px-2 py-0.5 rounded-full">+{{ $requestsThisWeek }} minggu ini</span>
            </div>
            <div class="bg-white rounded-3xl border border-line p-3.5">
                <p class="text-2xl font-extrabold">{{ $unpaidInvoices->count() }}</p>
                <p class="text-[10px] text-muted font-semibold mt-1 leading-tight">Menunggu bayar</p>
                <span class="inline-block mt-2 bg-brass-soft text-brass text-[10px] font-bold px-2 py-0.5 rounded-full">Segera bayar</span>
            </div>
            <div class="bg-white rounded-3xl border border-line p-3.5">
                <p class="text-2xl font-extrabold">{{ $inTransitCount }}</p>
                <p class="text-[10px] text-muted font-semibold mt-1 leading-tight">Dalam pengiriman</p>
                <span class="inline-block mt-2 bg-forest/10 text-forest text-[10px] font-bold px-2 py-0.5 rounded-full">Live GPS</span>
            </div>
        </div>

        <div class="px-4 mt-5 pb-6">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-extrabold">Aktivitas Terbaru</h3>
                <a href="{{ route('umkm.orders.index') }}" class="text-xs font-bold text-brass">Lihat semua</a>
            </div>
            <div class="mt-3 space-y-2.5">
                @forelse ($orderItems->take(4) as $item)
                    <div class="bg-white rounded-3xl border border-line p-3.5 flex gap-3">
                        @if ($item->payment_status->value === 'UNPAID')
                            <span class="w-10 h-10 rounded-2xl bg-brass-soft text-brass flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-[13px] font-bold leading-snug">Invoice {{ $item->order?->order_number }} menunggu pembayaran</p>
                                <p class="text-[11px] text-muted mt-0.5">Rp {{ number_format($item->subtotal, 0, ',', '.') }} &middot; {{ $item->product?->name }}</p>
                            </div>
                        @elseif ($item->order && $item->order->shipments->contains(fn ($shipment) => $shipment->status === 'IN_TRANSIT'))
                            <span class="w-10 h-10 rounded-2xl bg-forest/10 text-forest flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.38a1.13 1.13 0 0 1-1.13-1.13V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.13c.62 0 1.13-.5 1.09-1.12a18 18 0 0 0-3.21-9.19 2.06 2.06 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.18v-.96c0-.57-.42-1.05-.99-1.11a48.55 48.55 0 0 0-10.02 0 1.11 1.11 0 0 0-.99 1.11v7.63m12-6.68v6.68m0 4.5v-4.5m0 0h-12"/></svg>
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-[13px] font-bold leading-snug">Kurir mengantar {{ $item->product?->name }}</p>
                                <p class="text-[11px] text-muted mt-0.5">Lacak posisi live di peta</p>
                            </div>
                        @else
                            <span class="w-10 h-10 rounded-2xl bg-success/10 text-success flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m4.5 12.75 6 6 9-13.5"/></svg>
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-[13px] font-bold leading-snug">{{ $item->product?->name }} siap</p>
                                <p class="text-[11px] text-muted mt-0.5">Pesanan kolektif berjalan</p>
                            </div>
                        @endif
                        <span class="text-[10px] text-muted shrink-0">{{ optional($item->updated_at)->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="text-xs text-muted">Belum ada aktivitas.</p>
                @endforelse
            </div>
        </div>
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
            <x-umkm.sidebar-nav active="dashboard" />
            <div class="p-4 border-t border-line">
                <a href="{{ route('profile.complete') }}" class="flex items-center gap-3 px-2">
                    <span class="w-10 h-10 rounded-2xl bg-forest text-white flex items-center justify-center font-extrabold text-xs">{{ $initials }}</span>
                    <div class="min-w-0">
                        <p class="text-sm font-bold truncate">{{ $displayName }}</p>
                        <p class="text-[11px] text-muted">{{ $umkm?->city ?? 'Sidoarjo' }}, {{ $umkm?->province ?? 'Jawa Timur' }}</p>
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
                <section class="relative overflow-hidden rounded-[2rem] bg-gradient-to-r from-forest-deep via-forest to-forest-dark text-white p-8">
                    <div class="absolute -right-16 -top-20 w-72 h-72 rounded-full bg-white/10"></div>
                    <div class="absolute right-40 -bottom-24 w-56 h-56 rounded-full bg-brass/20"></div>
                    <div class="relative flex items-center justify-between gap-6 flex-wrap">
                        <div class="max-w-xl">
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-brass-soft">Butuh stok minggu ini?</p>
                            <h2 class="text-2xl font-extrabold mt-1">Belanja bareng, hemat hingga 20%.</h2>
                            <p class="text-white/70 text-sm mt-2">Kuota grosir Beras Premium hampir penuh &mdash; gabung sekarang sebelum kehabisan.</p>
                        </div>
                        <div class="flex gap-3">
                            <a href="{{ route('umkm.catalog.index') }}" class="h-12 px-6 rounded-2xl bg-white text-forest-dark text-sm font-bold flex items-center justify-center">Buka Katalog</a>
                            <a href="{{ route('umkm.cart.index') }}" class="h-12 px-6 rounded-2xl bg-white/10 border border-white/25 text-white text-sm font-bold flex items-center justify-center">Lihat Keranjang</a>
                        </div>
                    </div>
                </section>

                <section class="grid grid-cols-2 xl:grid-cols-4 gap-4 mt-6">
                    <div class="bg-white rounded-3xl border-2 border-brass/40 p-6 relative overflow-hidden">
                        <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-brass-soft"></div>
                        <div class="flex items-center justify-between relative">
                            <p class="text-xs font-bold uppercase tracking-wider text-muted">Total Penghematan</p>
                            <span class="w-9 h-9 rounded-xl bg-brass-soft text-brass flex items-center justify-center text-xs font-extrabold">Rp</span>
                        </div>
                        <p class="text-[2rem] font-extrabold text-brass tabular-nums mt-3 relative">Rp {{ number_format($totalSavings, 0, ',', '.') }}</p>
                        <p class="text-[11px] text-muted mt-1 relative">vs harga eceran &middot; 3 bulan terakhir</p>
                    </div>
                    <div class="bg-white rounded-3xl border border-line p-6">
                        <p class="text-xs font-bold uppercase tracking-wider text-muted">Permintaan Aktif</p>
                        <p class="text-4xl font-extrabold mt-3">{{ $activeRequestsCount }}</p>
                        <span class="inline-block mt-3 bg-success/10 text-success text-[11px] font-bold px-2.5 py-1 rounded-full">+{{ $requestsThisWeek }} minggu ini</span>
                    </div>
                    <div class="bg-white rounded-3xl border border-line p-6">
                        <p class="text-xs font-bold uppercase tracking-wider text-muted">Menunggu Bayar</p>
                        <p class="text-4xl font-extrabold mt-3">{{ $unpaidInvoices->count() }}</p>
                        <span class="inline-block mt-3 bg-brass-soft text-brass text-[11px] font-bold px-2.5 py-1 rounded-full">Segera bayar</span>
                    </div>
                    <div class="bg-white rounded-3xl border border-line p-6">
                        <p class="text-xs font-bold uppercase tracking-wider text-muted">Dalam Pengiriman</p>
                        <p class="text-4xl font-extrabold mt-3">{{ $inTransitCount }}</p>
                        <span class="inline-block mt-3 bg-forest/10 text-forest text-[11px] font-bold px-2.5 py-1 rounded-full">Live GPS aktif</span>
                    </div>
                </section>

                <div class="grid lg:grid-cols-[1fr_360px] gap-6 mt-6 items-start">
                    <section>
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-extrabold">Aktivitas Terbaru</h3>
                            <a href="{{ route('umkm.orders.index') }}" class="text-xs font-bold text-brass">Lihat semua</a>
                        </div>
                        <div class="mt-4 space-y-3">
                            @forelse ($orderItems->take(4) as $item)
                                <div class="bg-white rounded-3xl border border-line p-4 flex gap-4">
                                    @if ($item->payment_status->value === 'UNPAID')
                                        <span class="w-11 h-11 rounded-2xl bg-brass-soft text-brass flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                        </span>
                                        <div class="flex-1">
                                            <p class="text-sm font-bold">Invoice {{ $item->order?->order_number }} menunggu pembayaran</p>
                                            <p class="text-xs text-muted mt-0.5">Rp {{ number_format($item->subtotal, 0, ',', '.') }} &middot; {{ $item->product?->name }}</p>
                                        </div>
                                    @elseif ($item->order && $item->order->shipments->contains(fn ($shipment) => $shipment->status === 'IN_TRANSIT'))
                                        <span class="w-11 h-11 rounded-2xl bg-forest/10 text-forest flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.38a1.13 1.13 0 0 1-1.13-1.13V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.13c.62 0 1.13-.5 1.09-1.12a18 18 0 0 0-3.21-9.19 2.06 2.06 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.18v-.96c0-.57-.42-1.05-.99-1.11a48.55 48.55 0 0 0-10.02 0 1.11 1.11 0 0 0-.99 1.11v7.63m12-6.68v6.68m0 4.5v-4.5m0 0h-12"/></svg>
                                        </span>
                                        <div class="flex-1">
                                            <p class="text-sm font-bold">Kurir mengantar {{ $item->product?->name }}</p>
                                            <p class="text-xs text-muted mt-0.5">Lacak posisi live di peta</p>
                                        </div>
                                    @else
                                        <span class="w-11 h-11 rounded-2xl bg-success/10 text-success flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m4.5 12.75 6 6 9-13.5"/></svg>
                                        </span>
                                        <div class="flex-1">
                                            <p class="text-sm font-bold">{{ $item->product?->name }} siap</p>
                                            <p class="text-xs text-muted mt-0.5">Pesanan kolektif berjalan</p>
                                        </div>
                                    @endif
                                    <p class="text-[11px] text-muted shrink-0">{{ optional($item->updated_at)->diffForHumans() }}</p>
                                </div>
                            @empty
                                <p class="text-sm text-muted">Belum ada aktivitas.</p>
                            @endforelse
                        </div>
                    </section>

                    <aside class="space-y-4">
                        <div class="bg-white rounded-3xl border border-line p-6">
                            <div class="flex items-center justify-between">
                                <h4 class="font-extrabold text-sm">Kuota Berjalan</h4>
                                <a href="{{ route('umkm.requests.index') }}" class="text-[11px] font-bold text-brass">Semua</a>
                            </div>
                            <div class="mt-4 space-y-4">
                                @forelse ($pendingRequests->take(3) as $req)
                                    <div>
                                        <p class="text-xs font-bold">{{ $req->product?->name }} &middot; {{ $req->preferredDistributor?->company_name ?? '-' }}</p>
                                        <p class="text-[11px] text-muted font-semibold mt-1">{{ number_format($req->quantity, 0) }} {{ $req->unit }} &middot; Menunggu kuota</p>
                                    </div>
                                @empty
                                    <p class="text-xs text-muted">Belum ada kuota berjalan.</p>
                                @endforelse
                            </div>
                        </div>
                        <div class="rounded-3xl bg-forest-deep text-white p-6 relative overflow-hidden">
                            <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-white/10"></div>
                            <p class="text-sm font-extrabold relative">Butuh rekomendasi stok?</p>
                            <p class="text-xs text-white/70 mt-1 relative">Lihat produk yang lagi banyak dibeli UMKM se-Jember minggu ini.</p>
                            <a href="{{ route('umkm.catalog.index') }}" class="mt-4 bg-white text-forest-dark text-xs font-bold px-4 py-2.5 rounded-xl relative inline-flex">Lihat Tren</a>
                        </div>
                    </aside>
                </div>
            </main>
        </div>
    </div>

    <x-umkm.mobile-tab active="dashboard" />
</x-layouts.app>
