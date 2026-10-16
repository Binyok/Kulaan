<x-layouts.app>
    <x-slot name="title">Riwayat Pesanan &amp; Tagihan</x-slot>

    @php
        $displayName = $umkm?->business_name ?? auth()->user()->name;
        $initials = strtoupper(substr(auth()->user()->name, 0, 2));
        $cartCount = app(\App\Services\CartService::class)->count();

        $hasOrders = $orderItems->isNotEmpty();
        $unpaidItems = $orderItems->filter(fn ($i) => $i->payment_status->value === 'UNPAID');
        $paidItems = $orderItems->filter(fn ($i) => $i->payment_status->value !== 'UNPAID');
        $unpaidInvoices = $unpaidItems;
        $inTransitCount = $orderItems->filter(fn ($i) => $i->order?->shipments->contains(fn ($s) => $s->status === 'IN_TRANSIT'))->count();

        $paymentPillClass = fn ($status) => match ($status->value) {
            'PAID' => 'bg-success/10 text-success',
            'UNPAID' => 'bg-danger/10 text-danger',
            default => 'bg-brass-soft text-brass',
        };
    @endphp

    <div x-data="{
        showModal: false,
        distributorName: '',
        reviewAction: '',
        rating: 5,
        comment: '',
        openRatingModal(id, name, action) {
            this.distributorName = name;
            this.reviewAction = action;
            this.rating = 5;
            this.comment = '';
            this.showModal = true;
        }
    }">

        {{-- MOBILE --}}
        <div class="lg:hidden pb-24">
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
                <div class="relative" x-data="{ notifOpen: false }">
                    <button type="button" @click="notifOpen = !notifOpen" class="w-10 h-10 rounded-2xl bg-white border border-line flex items-center justify-center text-ink relative cursor-pointer">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14.86 17.08a23.85 23.85 0 0 0 5.45-1.31A8.97 8.97 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.97 8.97 0 0 1-2.31 6.02c1.73.64 3.56 1.09 5.45 1.31m5.72 0a24.26 24.26 0 0 1-5.72 0m5.72 0a3 3 0 1 1-5.72 0"/></svg>
                    </button>
                    <div x-show="notifOpen" @click.away="notifOpen = false" x-cloak class="absolute right-0 mt-2 w-72 bg-white border border-line rounded-2xl shadow-xl p-4 z-40 text-left">
                        <p class="text-sm font-extrabold text-ink">Notifikasi</p>
                        <div class="mt-3 space-y-1">
                            <a href="{{ route('umkm.orders.index') }}" class="flex items-start gap-2 rounded-xl p-2 hover:bg-cream">
                                <span class="w-2 h-2 mt-1.5 rounded-full bg-brass shrink-0"></span>
                                <span class="text-xs text-ink"><b>{{ $unpaidInvoices->count() }}</b> tagihan menunggu pembayaran</span>
                            </a>
                            <a href="{{ route('umkm.tracking.index') }}" class="flex items-start gap-2 rounded-xl p-2 hover:bg-cream">
                                <span class="w-2 h-2 mt-1.5 rounded-full bg-forest shrink-0"></span>
                                <span class="text-xs text-ink"><b>{{ $inTransitCount }}</b> pengiriman sedang berjalan</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-4 pt-2 mt-4 mb-5">
                <div class="bg-gradient-to-r from-forest to-forest-dark text-white rounded-2xl p-5 shadow-sm">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[0.6875rem] font-semibold bg-white/15 text-white">Pesanan Kolektif</span>
                    <h1 class="font-extrabold text-xl mt-2 tracking-tight">Riwayat Pesanan &amp; Tagihan</h1>
                    <p class="text-xs text-white/80 mt-1 leading-relaxed">Invoice, status pembayaran, dan ulasan distributor mitra pengadaanmu.</p>
                    <a href="{{ route('umkm.catalog.index') }}" class="inline-block mt-3 bg-brass text-white text-xs font-bold px-4 py-2.5 rounded-xl">+ Pesan Lagi</a>
                </div>
            </div>

            @if ($hasOrders)
                @if ($unpaidItems->isNotEmpty())
                    <section class="px-4 mb-5">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-2.5 h-2.5 rounded-full bg-danger"></span>
                            <h2 class="text-xs font-bold text-danger uppercase tracking-wider">Menunggu Pembayaran ({{ $unpaidItems->count() }})</h2>
                        </div>
                        <div class="space-y-3">
                            @foreach ($unpaidItems as $item)
                                <article class="bg-white rounded-2xl border border-danger/30 shadow-sm overflow-hidden">
                                    <div class="flex items-center justify-between px-4 py-3 border-b border-line/60 bg-cream/40">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="font-mono text-xs text-muted">{{ $item->order->order_number }}</span>
                                            <span class="text-[10px] font-bold {{ $paymentPillClass($item->payment_status) }} px-2 py-0.5 rounded-full">{{ $item->payment_status->label() }}</span>
                                        </div>
                                        <span class="text-[10px] text-muted">{{ $item->created_at->format('d M Y') }}</span>
                                    </div>
                                    <div class="px-4 py-3.5">
                                        <p class="font-bold text-ink text-sm">{{ $item->product->name }}</p>
                                        <p class="text-xs text-muted mt-0.5">{{ number_format($item->quantity, 0) }} {{ $item->product->unit }} &bull; Distributor: {{ $item->order->distributor->company_name }}</p>
                                        @if ($item->savings_amount > 0)
                                            <p class="text-[0.6875rem] font-semibold text-brass mt-1">Hemat Rp {{ number_format($item->savings_amount, 0, ',', '.') }} vs eceran</p>
                                        @endif
                                        <div class="flex items-center justify-between mt-3">
                                            <span class="font-extrabold text-forest text-lg tabular-nums">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                            <a href="{{ route('payment.show', $item) }}" class="bg-forest text-white text-xs font-bold py-2.5 px-4 rounded-xl">Bayar Sekarang &rarr;</a>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($paidItems->isNotEmpty())
                    <section class="px-4 pb-6">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-2.5 h-2.5 rounded-full bg-success"></span>
                            <h2 class="text-xs font-bold text-muted uppercase tracking-wider">Riwayat Pesanan Lunas ({{ $paidItems->count() }})</h2>
                        </div>
                        <div class="space-y-3">
                            @foreach ($paidItems as $item)
                                @php $shipment = $item->order->shipments->first(); @endphp
                                <article class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
                                    <div class="flex items-center justify-between px-4 py-3 border-b border-line/60">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="font-mono text-xs text-muted">{{ $item->order->order_number }}</span>
                                            <span class="text-[10px] font-bold {{ $paymentPillClass($item->payment_status) }} px-2 py-0.5 rounded-full">{{ $item->payment_status->label() }}</span>
                                        </div>
                                        <span class="text-[10px] text-muted">{{ $item->created_at->format('d M Y') }}</span>
                                    </div>
                                    <div class="px-4 py-3.5">
                                        <p class="font-bold text-ink text-sm">{{ $item->product->name }}</p>
                                        <p class="text-xs text-muted mt-0.5">{{ number_format($item->quantity, 0) }} {{ $item->product->unit }} &bull; {{ $item->order->distributor->company_name }}</p>
                                        @if ($item->savings_amount > 0)
                                            <p class="text-[0.6875rem] font-semibold text-brass mt-0.5">Hemat Rp {{ number_format($item->savings_amount, 0, ',', '.') }} vs eceran</p>
                                        @endif
                                        <div class="flex items-center justify-between mt-3">
                                            <span class="font-extrabold text-ink text-base tabular-nums">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 mt-3 flex-wrap">
                                            @if ($item->review)
                                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-brass bg-brass-soft px-2.5 py-1 rounded-full border border-brass/30">
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.9 6.26 6.6.56-5 4.36 1.5 6.45L12 16.9 5.99 19.63l1.5-6.45-5-4.36 6.6-.56L12 2z"/></svg>
                                                    {{ $item->review->rating }}/5
                                                </span>
                                            @elseif (in_array($item->order->status->value, ['DELIVERED', 'COMPLETED'], true))
                                                <button type="button"
                                                        @click="openRatingModal({{ $item->id }}, '{{ addslashes($item->order->distributor->company_name) }}', '{{ route('umkm.orders.review', $item) }}')"
                                                        class="inline-flex items-center gap-1 text-[11px] font-semibold text-brass bg-brass-soft rounded-lg border border-brass/30 px-3 py-1.5 transition-colors cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.9 6.26 6.6.56-5 4.36 1.5 6.45L12 16.9 5.99 19.63l1.5-6.45-5-4.36 6.6-.56L12 2z"/></svg>
                                                    Beri Ulasan
                                                </button>
                                            @endif

                                            @if ($shipment)
                                                <a href="{{ route('umkm.tracking.show', $shipment) }}" class="text-xs font-bold text-forest">Lacak Kurir &rarr;</a>
                                            @endif

                                            <a href="{{ route('payment.show', $item) }}" class="text-xs font-semibold text-muted border border-line rounded-lg px-3 py-1.5">Invoice</a>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                <div class="px-4">{{ $orderItems->links() }}</div>
            @else
                <section class="px-4">
                    <div class="bg-white rounded-2xl border border-line p-10 text-center shadow-sm">
                        <div class="w-14 h-14 rounded-2xl bg-brass-soft text-brass flex items-center justify-center mx-auto font-display font-bold text-2xl">K</div>
                        <h2 class="font-display font-bold text-ink text-lg mt-4">Belum Ada Riwayat Pesanan</h2>
                        <p class="text-xs text-muted mt-2 leading-relaxed max-w-sm mx-auto">
                            Setelah pengajuanmu ter-matching dan dibayar, invoice dan ulasan mitra tampil di sini.
                        </p>
                        <a href="{{ route('umkm.catalog.index') }}" class="btn-primary inline-flex text-sm font-bold px-5 py-2.5 mt-5">
                            Buka Katalog &rarr;
                        </a>
                    </div>
                </section>
            @endif
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
                <nav class="flex-1 px-4 py-6 space-y-1 text-sm font-semibold">
                    <a href="{{ route('umkm.catalog.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>
                        Katalog
                    </a>
                    <a href="{{ route('umkm.cart.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2.25 3h1.39c.51 0 .96.34 1.09.84l.38 1.44M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.22c1.12-2.3 2.1-4.68 2.92-7.14a60.11 60.11 0 0 0-16.54-1.84M7.5 14.25 5.11 5.27M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                        Keranjang
                        @if ($cartCount > 0)
                            <span class="ml-auto bg-brass text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $cartCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('umkm.requests.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                        Pengajuan
                    </a>
                    <a href="{{ route('umkm.orders.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-forest text-white">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.25 7.5l-.63 10.63a2.25 2.25 0 0 1-2.25 2.12H6.63a2.25 2.25 0 0 1-2.25-2.12L3.75 7.5M10 11.25h4M3.38 7.5h17.25c.62 0 1.12-.5 1.12-1.12v-1.5C21.75 4.26 21.25 3.75 20.63 3.75H3.38c-.62 0-1.13.5-1.13 1.12v1.5c0 .63.5 1.13 1.12 1.13Z"/></svg>
                        Pesanan
                    </a>
                    <a href="{{ route('umkm.tracking.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path d="M19.5 10.5c0 7.14-7.5 11.25-7.5 11.25S4.5 17.64 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                        Pelacakan
                    </a>
                </nav>
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
                            <div class="relative" x-data="{ notifOpen: false }">
                                <button type="button" @click="notifOpen = !notifOpen" class="w-11 h-11 rounded-2xl bg-white border border-line flex items-center justify-center relative cursor-pointer">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14.86 17.08a23.85 23.85 0 0 0 5.45-1.31A8.97 8.97 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.97 8.97 0 0 1-2.31 6.02c1.73.64 3.56 1.09 5.45 1.31m5.72 0a24.26 24.26 0 0 1-5.72 0m5.72 0a3 3 0 1 1-5.72 0"/></svg>
                                </button>
                                <div x-show="notifOpen" @click.away="notifOpen = false" x-cloak class="absolute right-0 mt-2 w-72 bg-white border border-line rounded-2xl shadow-xl p-4 z-40 text-left">
                                    <p class="text-sm font-extrabold text-ink">Notifikasi</p>
                                    <div class="mt-3 space-y-1">
                                        <a href="{{ route('umkm.orders.index') }}" class="flex items-start gap-2 rounded-xl p-2 hover:bg-cream">
                                            <span class="w-2 h-2 mt-1.5 rounded-full bg-brass shrink-0"></span>
                                            <span class="text-xs text-ink"><b>{{ $unpaidInvoices->count() }}</b> tagihan menunggu pembayaran</span>
                                        </a>
                                        <a href="{{ route('umkm.tracking.index') }}" class="flex items-start gap-2 rounded-xl p-2 hover:bg-cream">
                                            <span class="w-2 h-2 mt-1.5 rounded-full bg-forest shrink-0"></span>
                                            <span class="text-xs text-ink"><b>{{ $inTransitCount }}</b> pengiriman sedang berjalan</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <main class="px-8 py-6 max-w-6xl">
                    <div class="bg-gradient-to-r from-forest to-forest-dark text-white rounded-2xl p-7 shadow-sm mb-8">
                        <div class="flex items-center justify-between gap-4 flex-wrap">
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[0.6875rem] font-semibold bg-white/15 text-white">Pesanan Kolektif</span>
                                <h1 class="font-extrabold text-3xl mt-2 tracking-tight">Riwayat Pesanan &amp; Tagihan</h1>
                                <p class="text-sm text-white/80 mt-1">Invoice, status pembayaran, dan ulasan distributor mitra pengadaanmu.</p>
                            </div>
                            <a href="{{ route('umkm.catalog.index') }}" class="bg-brass text-white text-sm font-bold px-5 py-3 rounded-xl">+ Pesan Lagi</a>
                        </div>
                    </div>

                    @if ($hasOrders)
                        @if ($unpaidItems->isNotEmpty())
                            <section class="mb-8">
                                <div class="flex items-center gap-2 mb-4">
                                    <span class="w-2.5 h-2.5 rounded-full bg-danger"></span>
                                    <h2 class="text-xs font-bold text-danger uppercase tracking-wider">Menunggu Pembayaran ({{ $unpaidItems->count() }})</h2>
                                </div>
                                <div class="space-y-4">
                                    @foreach ($unpaidItems as $item)
                                        <article class="bg-white rounded-2xl border border-danger/30 shadow-sm overflow-hidden">
                                            <div class="flex items-center justify-between px-5 py-3 border-b border-line/60 bg-cream/40">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-mono text-xs text-muted">{{ $item->order->order_number }}</span>
                                                    <span class="text-[10px] font-bold {{ $paymentPillClass($item->payment_status) }} px-2 py-0.5 rounded-full">{{ $item->payment_status->label() }}</span>
                                                </div>
                                                <span class="text-[11px] text-muted">{{ $item->created_at->format('d M Y') }}</span>
                                            </div>
                                            <div class="px-5 py-4 flex items-center justify-between gap-4 flex-wrap">
                                                <div>
                                                    <p class="font-bold text-ink text-base">{{ $item->product->name }}</p>
                                                    <p class="text-xs text-muted mt-0.5">{{ number_format($item->quantity, 0) }} {{ $item->product->unit }} &bull; Distributor: {{ $item->order->distributor->company_name }}</p>
                                                    @if ($item->savings_amount > 0)
                                                        <p class="text-xs font-semibold text-brass mt-1">Hemat Rp {{ number_format($item->savings_amount, 0, ',', '.') }} vs eceran</p>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-4">
                                                    <span class="font-extrabold text-forest text-xl tabular-nums">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                                    <a href="{{ route('payment.show', $item) }}" class="bg-forest text-white text-sm font-bold py-3 px-5 rounded-xl">Bayar Sekarang &rarr;</a>
                                                </div>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        @if ($paidItems->isNotEmpty())
                            <section>
                                <div class="flex items-center gap-2 mb-4">
                                    <span class="w-2.5 h-2.5 rounded-full bg-success"></span>
                                    <h2 class="text-xs font-bold text-muted uppercase tracking-wider">Riwayat Pesanan Lunas ({{ $paidItems->count() }})</h2>
                                </div>
                                <div class="space-y-4">
                                    @foreach ($paidItems as $item)
                                        @php $shipment = $item->order->shipments->first(); @endphp
                                        <article class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
                                            <div class="flex items-center justify-between px-5 py-3 border-b border-line/60">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-mono text-xs text-muted">{{ $item->order->order_number }}</span>
                                                    <span class="text-[10px] font-bold {{ $paymentPillClass($item->payment_status) }} px-2 py-0.5 rounded-full">{{ $item->payment_status->label() }}</span>
                                                </div>
                                                <span class="text-[11px] text-muted">{{ $item->created_at->format('d M Y') }}</span>
                                            </div>
                                            <div class="px-5 py-4 flex items-center justify-between gap-4 flex-wrap">
                                                <div>
                                                    <p class="font-bold text-ink">{{ $item->product->name }}</p>
                                                    <p class="text-xs text-muted mt-0.5">{{ number_format($item->quantity, 0) }} {{ $item->product->unit }} &bull; {{ $item->order->distributor->company_name }}</p>
                                                    @if ($item->savings_amount > 0)
                                                        <p class="text-xs font-semibold text-brass mt-1">Hemat Rp {{ number_format($item->savings_amount, 0, ',', '.') }} vs eceran</p>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-3 flex-wrap">
                                                    <span class="font-extrabold text-ink tabular-nums">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>

                                                    @if ($item->review)
                                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-brass bg-brass-soft px-2.5 py-1 rounded-full border border-brass/30">
                                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.9 6.26 6.6.56-5 4.36 1.5 6.45L12 16.9 5.99 19.63l1.5-6.45-5-4.36 6.6-.56L12 2z"/></svg>
                                                            {{ $item->review->rating }}/5
                                                        </span>
                                                    @elseif (in_array($item->order->status->value, ['DELIVERED', 'COMPLETED'], true))
                                                        <button type="button"
                                                                @click="openRatingModal({{ $item->id }}, '{{ addslashes($item->order->distributor->company_name) }}', '{{ route('umkm.orders.review', $item) }}')"
                                                                class="inline-flex items-center gap-1 text-[11px] font-semibold text-brass bg-brass-soft rounded-lg border border-brass/30 px-3 py-2 transition-colors cursor-pointer">
                                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.9 6.26 6.6.56-5 4.36 1.5 6.45L12 16.9 5.99 19.63l1.5-6.45-5-4.36 6.6-.56L12 2z"/></svg>
                                                            Beri Ulasan
                                                        </button>
                                                    @endif

                                                    @if ($shipment)
                                                        <a href="{{ route('umkm.tracking.show', $shipment) }}" class="text-xs font-bold text-forest hover:underline">Lacak Kurir &rarr;</a>
                                                    @endif

                                                    <a href="{{ route('payment.show', $item) }}" class="text-xs font-semibold text-muted border border-line rounded-lg px-3 py-2">Invoice</a>
                                                </div>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        <div class="pt-6">{{ $orderItems->links() }}</div>
                    @else
                        <section class="bg-white rounded-2xl border border-line p-16 text-center max-w-lg mx-auto shadow-sm">
                            <div class="w-14 h-14 rounded-2xl bg-brass-soft text-brass flex items-center justify-center mx-auto font-display font-bold text-2xl">K</div>
                            <h2 class="font-display font-bold text-ink text-xl mt-4">Belum Ada Riwayat Pesanan</h2>
                            <p class="text-sm text-muted mt-2 leading-relaxed max-w-sm mx-auto">
                                Setelah pengajuanmu ter-matching dan dibayar, invoice dan ulasan mitra tampil di sini.
                            </p>
                            <a href="{{ route('umkm.catalog.index') }}" class="btn-primary inline-flex text-sm font-bold px-5 py-2.5 mt-5">
                                Buka Katalog &rarr;
                            </a>
                        </section>
                    @endif
                </main>
            </div>
        </div>

        {{-- MOBILE BOTTOM TAB --}}
        <div class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-white border-t border-line px-2 pt-2 pb-5 flex items-start justify-around">
            <a href="{{ route('umkm.catalog.index') }}" class="flex flex-col items-center gap-1 w-14 text-muted">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>
                <span class="text-[10px] font-semibold">Katalog</span>
            </a>
            <a href="{{ route('umkm.cart.index') }}" class="flex flex-col items-center gap-1 w-14 text-muted relative">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2.25 3h1.39c.51 0 .96.34 1.09.84l.38 1.44M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.22c1.12-2.3 2.1-4.68 2.92-7.14a60.11 60.11 0 0 0-16.54-1.84M7.5 14.25 5.11 5.27M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                <span class="text-[10px] font-semibold">Keranjang</span>
                @if ($cartCount > 0)
                    <span class="absolute top-0 right-2 min-w-[18px] h-[18px] px-1 rounded-full bg-brass text-white text-[10px] font-bold flex items-center justify-center">{{ $cartCount }}</span>
                @endif
            </a>
            <a href="{{ route('umkm.requests.index') }}" class="flex flex-col items-center gap-1 w-14 text-muted">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                <span class="text-[10px] font-semibold">Pengajuan</span>
            </a>
            <a href="{{ route('umkm.orders.index') }}" class="flex flex-col items-center gap-1 w-14 text-forest">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.25 7.5l-.63 10.63a2.25 2.25 0 0 1-2.25 2.12H6.63a2.25 2.25 0 0 1-2.25-2.12L3.75 7.5M10 11.25h4M3.38 7.5h17.25c.62 0 1.12-.5 1.12-1.12v-1.5C21.75 4.26 21.25 3.75 20.63 3.75H3.38c-.62 0-1.13.5-1.13 1.12v1.5c0 .63.5 1.13 1.12 1.13Z"/></svg>
                <span class="text-[10px] font-bold">Pesanan</span>
            </a>
            <a href="{{ route('umkm.tracking.index') }}" class="flex flex-col items-center gap-1 w-14 text-muted">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path d="M19.5 10.5c0 7.14-7.5 11.25-7.5 11.25S4.5 17.64 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                <span class="text-[10px] font-semibold">Pelacakan</span>
            </a>
        </div>

        <!-- Rating Modal -->
        <x-modal open="showModal" size="sm" title="Beri Penilaian Distributor">
            <p class="text-xs text-muted mt-4" x-text="distributorName"></p>

            <form :action="reviewAction" method="POST" class="pt-4 space-y-4" data-loading>
                @csrf
                <div>
                    <label class="field-label">Rating bintang (kualitas layanan &amp; produk)</label>
                    <div class="flex items-center gap-1">
                        <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                            <button type="button" @click="rating = star"
                                class="min-w-11 min-h-11 flex items-center justify-center transition-transform hover:scale-110 rounded-lg cursor-pointer"
                                :aria-label="star + ' bintang'">
                                <svg class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" :class="star <= rating ? 'text-brass' : 'text-muted/30'"><path d="M12 2l2.9 6.26 6.6.56-5 4.36 1.5 6.45L12 16.9 5.99 19.63l1.5-6.45-5-4.36 6.6-.56L12 2z"/></svg>
                            </button>
                        </template>
                        <span class="text-xs font-bold text-muted ml-2" x-text="rating + ' / 5 Bintang'"></span>
                    </div>
                    <input type="hidden" name="rating" :value="rating">
                </div>

                <div>
                    <label for="review-comment" class="field-label">Catatan / ulasan tambahan (opsional)</label>
                    <textarea id="review-comment" name="comment" rows="3" x-model="comment" placeholder="Bagikan pengalaman transaksi, kualitas bahan baku, dan ketepatan waktu pengiriman..." class="input-field"></textarea>
                </div>

                <div class="flex flex-col-reverse sm:flex-row-reverse sm:justify-end gap-3 pt-2 border-t border-black/[0.06]">
                    <button type="submit" class="btn-primary text-sm py-3 px-6 w-full sm:w-auto">Kirim Penilaian</button>
                    <button type="button" @click="showModal = false" class="btn-ghost text-sm py-3 w-full sm:w-auto">Batal</button>
                </div>
            </form>
        </x-modal>
    </div>
</x-layouts.app>
