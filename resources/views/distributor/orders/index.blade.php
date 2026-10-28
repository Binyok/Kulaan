<x-layouts.app>
    <x-slot name="title">Kelola Pesanan Kolektif</x-slot>

    <div x-data="{ openCourierModal: false, selectedOrder: null, openDetailModal: false, selectedDetail: null, openCancelModal: false }">

        <div class="lg:hidden bg-cream min-h-screen">
            <div class="pb-24">
                <div class="px-4 pt-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="relative" x-data="{ menuOpen: false }">
                            <button type="button" @click="menuOpen = !menuOpen" class="w-11 h-11 rounded-2xl bg-forest text-white flex items-center justify-center font-extrabold text-sm cursor-pointer">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</button>
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
                            <p class="text-[15px] font-extrabold leading-tight">{{ $distributor?->company_name ?? auth()->user()->name }}</p>
                        </div>
                    </div>
                    <x-notification-bell />
                </div>

                <div class="mx-4 mt-4 rounded-3xl bg-gradient-to-br from-forest-deep via-forest to-forest-dark text-white p-5 relative overflow-hidden shrink-0">
                    <div class="absolute -right-10 -top-12 w-44 h-44 rounded-full bg-white/10"></div>
                    <div class="absolute right-16 -bottom-14 w-32 h-32 rounded-full bg-brass/20"></div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold bg-white/10 text-white/85 uppercase tracking-[0.14em]">Pesanan Masuk</span>
                    <h1 class="text-2xl font-extrabold mt-2 tracking-tight">Pesanan Kolektif UMKM</h1>
                    <p class="text-xs text-white/70 leading-relaxed mt-1.5">Konfirmasi pesanan, siapkan barang, terus tugaskan kurir — alurnya berurutan biar nggak ada yang kelewat.</p>
                </div>

                <div class="px-4 mt-4">
                    <div class="bg-white rounded-2xl border border-line p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex flex-col items-center gap-1.5 flex-1">
                                <span class="w-8 h-8 rounded-full bg-forest text-white flex items-center justify-center">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m4.5 12.75 6 6 9-13.5"/></svg>
                                </span>
                                <span class="text-[10px] font-bold text-forest">Konfirmasi</span>
                            </div>
                            <div class="flex-1 h-0.5 bg-forest/30 rounded -mt-5 mx-1"></div>
                            <div class="flex flex-col items-center gap-1.5 flex-1">
                                <span class="w-8 h-8 rounded-full bg-forest text-white flex items-center justify-center text-xs font-extrabold">2</span>
                                <span class="text-[10px] font-bold text-forest">Siapkan</span>
                            </div>
                            <div class="flex-1 h-0.5 bg-line rounded -mt-5 mx-1"></div>
                            <div class="flex flex-col items-center gap-1.5 flex-1">
                                <span class="w-8 h-8 rounded-full bg-cream border border-line text-muted flex items-center justify-center text-xs font-extrabold">3</span>
                                <span class="text-[10px] font-semibold text-muted">Siap Kirim</span>
                            </div>
                            <div class="flex-1 h-0.5 bg-line rounded -mt-5 mx-1"></div>
                            <div class="flex flex-col items-center gap-1.5 flex-1">
                                <span class="w-8 h-8 rounded-full bg-cream border border-line text-muted flex items-center justify-center">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 17h-2v-11a1 1 0 0 1 1-1h9v12m-8 0h8m4 0h2v-6l-3-4h-3v10"/><circle cx="7.5" cy="17.5" r="1.8"/><circle cx="17.5" cy="17.5" r="1.8"/></svg>
                                </span>
                                <span class="text-[10px] font-semibold text-muted">Kurir</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-4 mt-4">
                    <div class="inline-flex bg-white border border-line rounded-2xl p-1 text-xs font-bold">
                        <a href="{{ route('distributor.orders.index', ['tab' => 'active']) }}" class="px-4 py-2 rounded-xl {{ $tab === 'active' ? 'bg-forest text-white' : 'text-muted' }}">Aktif</a>
                        <a href="{{ route('distributor.orders.index', ['tab' => 'history']) }}" class="px-4 py-2 rounded-xl {{ $tab === 'history' ? 'bg-forest text-white' : 'text-muted' }}">Riwayat</a>
                    </div>
                </div>

                <div class="px-4 mt-4 space-y-3">
                    @forelse ($orders as $order)
                        @php
                            $statusBadge = match ($order->status->value) {
                                'PAID', 'CONFIRMED' => 'bg-success/10 text-success',
                                'PREPARING', 'READY_TO_SHIP' => 'bg-forest/10 text-forest',
                                'ASSIGNED_TO_COURIER', 'IN_TRANSIT' => 'bg-brass/15 text-brass',
                                'DELIVERED', 'COMPLETED' => 'bg-forest text-white',
                                'CANCELLED', 'EXPIRED' => 'bg-danger/10 text-danger',
                                default => 'bg-brass-soft text-brass',
                            };
                        @endphp
                        <article class="bg-white rounded-3xl {{ $order->status->value === 'READY_TO_SHIP' ? 'border-2 border-brass/40' : 'border border-line' }} p-4 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-mono font-bold text-ink text-sm">{{ $order->order_number }}</p>
                                    <p class="text-[11px] text-muted mt-0.5">{{ number_format($order->total_quantity, 0) }} unit &bull; Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold {{ $statusBadge }} shrink-0">{{ $order->status->label() }}</span>
                            </div>
                            <div class="mt-3 space-y-1.5 bg-cream/60 rounded-2xl p-3">
                                @foreach ($order->items as $item)
                                    @php
                                        $paymentBadge = match ($item->payment_status->value) {
                                            'PAID' => 'bg-success/10 text-success',
                                            'UNPAID', 'PENDING' => 'bg-brass-soft text-brass',
                                            default => 'bg-danger/10 text-danger',
                                        };
                                    @endphp
                                    <div class="flex items-center justify-between text-xs">
                                        <span><strong class="text-ink">{{ $item->umkm->business_name }}</strong> <span class="text-muted">{{ number_format($item->quantity, 0) }} {{ $item->product->unit }}</span></span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $paymentBadge }}">{{ $item->payment_status->label() }}</span>
                                    </div>
                                @endforeach
                            </div>
                            @php $shipment = $order->shipments->first(); @endphp
                            @if ($shipment)
                                <div class="mt-3 rounded-2xl border border-line p-3 space-y-2">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-bold text-ink">Kurir: {{ $shipment->courier?->user?->name ?? 'Belum ditugaskan' }}</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-brass-soft text-brass">{{ $shipment->status }}</span>
                                    </div>
                                    @if ($shipment->courier)
                                        <p class="text-[11px] text-muted">{{ $shipment->courier->vehicle_type }} &bull; {{ $shipment->courier->vehicle_number }} &bull; {{ $shipment->courier->user?->phone ?? '-' }}</p>
                                    @endif
                                    @if ($shipment->proof_image_path)
                                        <img src="{{ asset('storage/'.$shipment->proof_image_path) }}" alt="Bukti pengiriman" class="w-full h-28 object-cover rounded-xl border border-line">
                                    @endif
                                </div>
                            @endif
                            <div class="mt-3">
                                @if ($order->status->value === 'PAID')
                                    <form method="POST" action="{{ route('distributor.orders.update-status', $order) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="CONFIRMED">
                                        <button type="submit" class="w-full bg-forest text-white text-sm font-bold py-3 rounded-2xl cursor-pointer">Konfirmasi</button>
                                    </form>
                                @elseif ($order->status->value === 'CONFIRMED')
                                    <form method="POST" action="{{ route('distributor.orders.update-status', $order) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="PREPARING">
                                        <button type="submit" class="w-full bg-forest text-white text-sm font-bold py-3 rounded-2xl cursor-pointer">Siapkan Barang</button>
                                    </form>
                                @elseif ($order->status->value === 'PREPARING')
                                    <form method="POST" action="{{ route('distributor.orders.update-status', $order) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="READY_TO_SHIP">
                                        <button type="submit" class="w-full bg-forest text-white text-sm font-bold py-3 rounded-2xl cursor-pointer">Siap Kirim</button>
                                    </form>
                                @elseif ($order->status->value === 'READY_TO_SHIP' && $order->shipments->isEmpty())
                                    <button type="button" @click="selectedOrder = {{ json_encode($order) }}; openCourierModal = true" class="w-full bg-brass text-white text-sm font-bold py-3 rounded-2xl cursor-pointer">Tugaskan Kurir</button>
                                @elseif ($order->status->value === 'READY_TO_SHIP')
                                    <span class="block text-center text-xs text-muted py-2">Kurir sudah ditugaskan</span>
                                @else
                                    <span class="block text-center text-xs text-muted py-2">Tidak ada aksi</span>
                                @endif
                                 @if (in_array($order->status->value, ['PAID', 'CONFIRMED', 'PREPARING', 'READY_TO_SHIP'], true))
                                     <button type="button" @click="selectedOrder = {{ json_encode($order) }}; openCancelModal = true" class="mt-2 w-full text-center text-xs font-bold text-danger bg-danger/10 hover:bg-danger/20 rounded-2xl py-2.5 transition cursor-pointer">Batalkan Pesanan</button>
                                 @endif
                                 <button type="button" @click="selectedDetail = {{ json_encode($order) }}; openDetailModal = true" class="mt-2 w-full text-center text-xs font-bold text-forest border border-line rounded-2xl py-2.5 cursor-pointer">Lihat Detail</button>
                            </div>
                        </article>
                    @empty
                        <section class="bg-white rounded-3xl border border-line px-6 py-14 text-center">
                            <span class="w-16 h-16 rounded-2xl bg-forest/10 text-forest inline-flex items-center justify-center mb-4">
                                <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 17h6m-5 4h4M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/><path d="M9 8h6M9 12h4"/></svg>
                            </span>
                            <h3 class="font-extrabold text-ink text-lg">Belum ada pesanan kolektif masuk.</h3>
                            <p class="text-xs text-muted mt-1.5 max-w-md mx-auto leading-relaxed">Pesanan muncul otomatis setelah grup UMKM mencapai kuota MOQ dan pembayaran terkonfirmasi.</p>
                            <a href="{{ route('distributor.products.index') }}" class="bg-forest text-white text-sm font-bold px-6 py-3 rounded-2xl mt-5 inline-flex">Kelola Katalog Produk</a>
                        </section>
                    @endforelse
                </div>

                <div class="px-4 mt-4">
                    {{ $orders->links() }}
                </div>
            </div>

            <x-mobile-tab role="distributor" active="orders" />
        </div>

        <div class="hidden lg:flex min-h-screen">
            <aside class="w-64 shrink-0 bg-white border-r border-line flex flex-col sticky top-0 h-screen">
                <div class="flex items-center gap-2.5 px-6 h-[72px] border-b border-line">
                    <span class="w-10 h-10 rounded-2xl bg-forest flex items-center justify-center text-white">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.25 7.5l-.63 10.63a2.25 2.25 0 0 1-2.25 2.12H6.63a2.25 2.25 0 0 1-2.25-2.12L3.75 7.5M10 11.25h4M3.38 7.5h17.25c.62 0 1.12-.5 1.12-1.12v-1.5C21.75 4.26 21.25 3.75 20.63 3.75H3.38c-.62 0-1.13.5-1.13 1.12v1.5c0 .63.5 1.13 1.12 1.13Z"/></svg>
                    </span>
                    <span class="text-xl font-extrabold tracking-tight">Kulaan</span>
                </div>
                <nav class="flex-1 px-4 py-6 space-y-1 text-sm font-semibold">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m2.25 12 8.95-8.95a1.13 1.13 0 0 1 1.6 0l8.95 8.95M4.5 9.75V19.5a.75.75 0 0 0 .75.75h13.5a.75.75 0 0 0 .75-.75V9.75"/></svg>
                        Dashboard
                    </a>
                    <a href="{{ route('distributor.products.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5M12 22V12"/></svg>
                        Katalog Produk
                    </a>
                    <a href="{{ route('distributor.orders.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-forest text-white">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 17h6m-5 4h4M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/><path d="M9 8h6M9 12h4"/></svg>
                        Pesanan
                    </a>
                </nav>
                <div class="p-4 border-t border-line">
                    <a href="{{ route('profile.complete') }}" class="flex items-center gap-3 px-2">
                        <span class="w-10 h-10 rounded-2xl bg-forest text-white flex items-center justify-center font-extrabold text-xs">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                        <div class="min-w-0">
                            <p class="text-sm font-bold truncate">{{ $distributor?->company_name ?? auth()->user()->name }}</p>
                            <p class="text-[11px] text-muted">{{ $distributor?->city ?? 'Sidoarjo' }}, {{ $distributor?->province ?? 'Jawa Timur' }}</p>
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
                            <h1 class="text-lg font-extrabold leading-tight">Selamat pagi, {{ $distributor?->company_name ?? auth()->user()->name }}</h1>
                            <p class="text-xs text-muted">{{ now()->translatedFormat('l, d F Y') }}</p>
                        </div>
                        <div class="ml-auto flex items-center gap-3">
                            <div class="hidden lg:flex items-center gap-2 bg-white border border-line rounded-2xl pl-4 pr-2 py-2 w-72">
                                <svg class="w-4 h-4 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21 21-5.2-5.2m0 0A7.5 7.5 0 1 0 5.2 5.2a7.5 7.5 0 0 0 10.6 10.6Z"/></svg>
                                <input class="flex-1 bg-transparent text-sm outline-none placeholder:text-muted/70" placeholder="Cari pesanan, produk...">
                            </div>
                            <x-notification-bell />
                        </div>
                    </div>
                </header>

                <main class="px-8 py-6 max-w-6xl">
                    <section class="relative overflow-hidden rounded-[2rem] bg-gradient-to-r from-forest-deep via-forest to-forest-dark text-white p-8">
                        <div class="absolute -right-16 -top-20 w-72 h-72 rounded-full bg-white/10"></div>
                        <div class="absolute right-40 -bottom-24 w-56 h-56 rounded-full bg-brass/20"></div>
                        <div class="relative max-w-2xl">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-bold bg-white/10 text-white/85 uppercase tracking-[0.14em]">Pesanan Masuk</span>
                            <h1 class="text-3xl font-extrabold mt-3 tracking-tight">Pesanan Kolektif UMKM</h1>
                            <p class="text-sm text-white/70 mt-2 leading-relaxed">Konfirmasi pesanan, siapkan barang, terus tugaskan kurir — alurnya berurutan biar nggak ada yang kelewat.</p>
                        </div>
                    </section>

                    <div class="bg-white rounded-3xl border border-line p-6 mt-6 mb-6">
                        <div class="flex items-center max-w-3xl">
                            <div class="flex flex-col items-center gap-2">
                                <span class="w-10 h-10 rounded-full bg-forest text-white flex items-center justify-center"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m4.5 12.75 6 6 9-13.5"/></svg></span>
                                <span class="text-xs font-bold text-forest">Konfirmasi</span>
                            </div>
                            <div class="flex-1 h-0.5 bg-forest/40 rounded mx-2 -mt-7"></div>
                            <div class="flex flex-col items-center gap-2">
                                <span class="w-10 h-10 rounded-full bg-forest text-white flex items-center justify-center text-sm font-extrabold">2</span>
                                <span class="text-xs font-bold text-forest">Siapkan Barang</span>
                            </div>
                            <div class="flex-1 h-0.5 bg-line rounded mx-2 -mt-7"></div>
                            <div class="flex flex-col items-center gap-2">
                                <span class="w-10 h-10 rounded-full bg-cream border border-line text-muted flex items-center justify-center text-sm font-extrabold">3</span>
                                <span class="text-xs font-semibold text-muted">Siap Kirim</span>
                            </div>
                            <div class="flex-1 h-0.5 bg-line rounded mx-2 -mt-7"></div>
                            <div class="flex flex-col items-center gap-2">
                                <span class="w-10 h-10 rounded-full bg-cream border border-line text-muted flex items-center justify-center"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 17h-2v-11a1 1 0 0 1 1-1h9v12m-8 0h8m4 0h2v-6l-3-4h-3v10"/><circle cx="7.5" cy="17.5" r="1.8"/><circle cx="17.5" cy="17.5" r="1.8"/></svg></span>
                                <span class="text-xs font-semibold text-muted">Tugaskan Kurir</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <div class="inline-flex bg-white border border-line rounded-2xl p-1 text-sm font-bold">
                            <a href="{{ route('distributor.orders.index', ['tab' => 'active']) }}" class="px-5 py-2.5 rounded-xl {{ $tab === 'active' ? 'bg-forest text-white' : 'text-muted' }}">Aktif</a>
                            <a href="{{ route('distributor.orders.index', ['tab' => 'history']) }}" class="px-5 py-2.5 rounded-xl {{ $tab === 'history' ? 'bg-forest text-white' : 'text-muted' }}">Riwayat</a>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @forelse ($orders as $order)
                            @php
                                $statusBadge = match ($order->status->value) {
                                    'PAID', 'CONFIRMED' => 'bg-success/10 text-success',
                                    'PREPARING', 'READY_TO_SHIP' => 'bg-forest/10 text-forest',
                                    'ASSIGNED_TO_COURIER', 'IN_TRANSIT' => 'bg-brass/15 text-brass',
                                    'DELIVERED', 'COMPLETED' => 'bg-forest text-white',
                                    'CANCELLED', 'EXPIRED' => 'bg-danger/10 text-danger',
                                    default => 'bg-brass-soft text-brass',
                                };
                            @endphp
                            <article class="bg-white rounded-3xl {{ $order->status->value === 'READY_TO_SHIP' ? 'border-2 border-brass/40' : 'border border-line' }} p-6 flex flex-col lg:flex-row gap-6">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 flex-wrap">
                                        <p class="font-mono font-bold text-ink">{{ $order->order_number }}</p>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $statusBadge }}">{{ $order->status->label() }}</span>
                                    </div>
                                    <p class="text-sm text-muted mt-1">{{ number_format($order->total_quantity, 0) }} unit &bull; Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                                    <div class="mt-4 grid sm:grid-cols-2 gap-2">
                                        @foreach ($order->items as $item)
                                            @php
                                                $paymentBadge = match ($item->payment_status->value) {
                                                    'PAID' => 'bg-success/10 text-success',
                                                    'UNPAID', 'PENDING' => 'bg-brass-soft text-brass',
                                                    default => 'bg-danger/10 text-danger',
                                                };
                                            @endphp
                                            <div class="flex items-center justify-between bg-cream/60 rounded-2xl px-4 py-3 text-sm">
                                                <span><strong class="text-ink">{{ $item->umkm->business_name }}</strong> <span class="text-muted">{{ number_format($item->quantity, 0) }} {{ $item->product->unit }}</span></span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $paymentBadge }}">{{ $item->payment_status->label() }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @php $shipment = $order->shipments->first(); @endphp
                                @if ($shipment)
                                    <div class="mt-4 rounded-2xl border border-line p-4 space-y-2 self-start min-w-[16rem]">
                                        <div class="flex items-center justify-between gap-3 text-xs">
                                            <span class="font-bold text-ink">Kurir: {{ $shipment->courier?->user?->name ?? 'Belum ditugaskan' }}</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-brass-soft text-brass">{{ $shipment->status }}</span>
                                        </div>
                                        @if ($shipment->courier)
                                            <p class="text-xs text-muted">{{ $shipment->courier->vehicle_type }} &bull; {{ $shipment->courier->vehicle_number }} &bull; {{ $shipment->courier->user?->phone ?? '-' }}</p>
                                        @endif
                                        @if ($shipment->proof_image_path)
                                            <img src="{{ asset('storage/'.$shipment->proof_image_path) }}" alt="Bukti pengiriman" class="w-full h-32 object-cover rounded-xl border border-line">
                                        @endif
                                    </div>
                                @endif
                                <div class="flex lg:flex-col justify-center shrink-0">
                                    @if ($order->status->value === 'PAID')
                                        <form method="POST" action="{{ route('distributor.orders.update-status', $order) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="CONFIRMED">
                                            <button type="submit" class="bg-forest text-white text-sm font-bold py-3 px-8 rounded-2xl cursor-pointer">Konfirmasi</button>
                                        </form>
                                    @elseif ($order->status->value === 'CONFIRMED')
                                        <form method="POST" action="{{ route('distributor.orders.update-status', $order) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="PREPARING">
                                            <button type="submit" class="bg-forest text-white text-sm font-bold py-3 px-8 rounded-2xl cursor-pointer">Siapkan Barang</button>
                                        </form>
                                    @elseif ($order->status->value === 'PREPARING')
                                        <form method="POST" action="{{ route('distributor.orders.update-status', $order) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="READY_TO_SHIP">
                                            <button type="submit" class="bg-forest text-white text-sm font-bold py-3 px-8 rounded-2xl cursor-pointer">Siap Kirim</button>
                                        </form>
                                    @elseif ($order->status->value === 'READY_TO_SHIP' && $order->shipments->isEmpty())
                                        <button type="button" @click="selectedOrder = {{ json_encode($order) }}; openCourierModal = true" class="bg-brass text-white text-sm font-bold py-3 px-8 rounded-2xl cursor-pointer">Tugaskan Kurir</button>
                                    @elseif ($order->status->value === 'READY_TO_SHIP')
                                        <span class="text-xs text-muted">Kurir sudah ditugaskan</span>
                                    @else
                                        <span class="text-xs text-muted">Tidak ada aksi</span>
                                    @endif
                                     @if (in_array($order->status->value, ['PAID', 'CONFIRMED', 'PREPARING', 'READY_TO_SHIP'], true))
                                         <button type="button" @click="selectedOrder = {{ json_encode($order) }}; openCancelModal = true" class="mt-2 text-center text-xs font-bold text-danger bg-danger/10 hover:bg-danger/20 rounded-2xl px-6 py-2.5 transition cursor-pointer">Batalkan Pesanan</button>
                                     @endif
                                     <button type="button" @click="selectedDetail = {{ json_encode($order) }}; openDetailModal = true" class="mt-2 text-center text-xs font-bold text-forest border border-line rounded-2xl px-6 py-2.5 cursor-pointer">Lihat Detail</button>
                                </div>
                            </article>
                        @empty
                            <section class="bg-white rounded-3xl border border-line px-6 py-14 text-center">
                                <span class="w-16 h-16 rounded-2xl bg-forest/10 text-forest inline-flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 17h6m-5 4h4M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/><path d="M9 8h6M9 12h4"/></svg>
                                </span>
                                <h3 class="font-extrabold text-ink text-lg">Belum ada pesanan kolektif masuk.</h3>
                                <p class="text-xs text-muted mt-1.5 max-w-md mx-auto leading-relaxed">Pesanan muncul otomatis setelah grup UMKM mencapai kuota MOQ dan pembayaran terkonfirmasi.</p>
                                <a href="{{ route('distributor.products.index') }}" class="bg-forest text-white text-sm font-bold px-6 py-3 rounded-2xl mt-5 inline-flex">Kelola Katalog Produk</a>
                            </section>
                        @endforelse
                    </div>

                    <div class="mt-6">
                        {{ $orders->links() }}
                    </div>
                </main>
            </div>
        </div>

        <x-modal open="openCourierModal" size="sm"
                 eyebrow="Penugasan Armada"
                 title="Tugaskan Kurir">
            <form :action="`/distributor/orders/${selectedOrder?.id}/assign-courier`" method="POST" class="pt-5 space-y-4">
                @csrf
                <fieldset>
                    <legend class="text-xs font-semibold text-muted mb-2">Pilih kurir armada</legend>
                    @if ($couriers->isEmpty())
                        <p class="text-xs text-muted p-3 bg-cream rounded-xl border border-line">
                            Belum ada kurir tersedia untuk distributor Anda. Daftarkan kurir armada sendiri atau tunggu kurir independen bergabung.
                        </p>
                    @else
                        <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                            @foreach ($couriers as $courier)
                                <label class="flex items-center gap-3 border-2 border-line bg-white rounded-2xl p-3 min-h-[3.5rem] cursor-pointer transition-colors has-[:checked]:border-forest has-[:checked]:bg-success/5 has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-forest/40">
                                    <input type="radio" name="courier_id" value="{{ $courier->id }}" {{ $loop->first ? 'checked' : '' }} class="peer sr-only" required>
                                    <span aria-hidden="true" class="w-5 h-5 rounded-full border-2 border-line bg-white shrink-0 transition-colors peer-checked:border-[6px] peer-checked:border-forest"></span>
                                    <span class="min-w-0">
                                        <span class="block text-sm font-bold text-ink">{{ $courier->user->name }}</span>
                                        <span class="block text-[0.6875rem] text-muted mt-0.5">{{ $courier->vehicle_type }} &bull; {{ $courier->vehicle_number }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </fieldset>

                <div>
                    <label for="courier-notes" class="text-xs font-semibold text-muted mb-2">Catatan pengiriman (opsional)</label>
                    <textarea id="courier-notes" name="notes" rows="2" placeholder="Petunjuk rute atau kontak penerima..." class="w-full bg-white border border-line rounded-2xl px-4 py-3 text-sm outline-none placeholder:text-muted/60 focus:border-forest"></textarea>
                </div>

                <div class="flex gap-3 pt-3 border-t border-line/60">
                    <button type="button" @click="openCourierModal = false" class="flex-1 border border-line text-muted text-sm font-bold py-3 rounded-2xl cursor-pointer">Batal</button>
                    <button type="submit" class="flex-1 bg-forest text-white text-sm font-bold py-3 rounded-2xl cursor-pointer" @if ($couriers->isEmpty()) disabled @endif>
                        Tugaskan Kurir
                    </button>
                </div>
            </form>
        </x-modal>

        <x-modal open="openDetailModal" size="lg"
                 eyebrow="Rincian Pesanan"
                 title="Detail Pesanan Kolektif">
            <template x-if="selectedDetail">
                <div class="pt-5 space-y-4 text-sm">
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="font-mono font-bold text-ink" x-text="selectedDetail.order_number"></span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-brass-soft text-brass" x-text="selectedDetail.status"></span>
                    </div>
                    <div class="rounded-2xl border border-line divide-y divide-line/60">
                        <template x-for="item in selectedDetail.items" :key="item.id">
                            <div class="p-3 flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-ink" x-text="item.umkm?.business_name"></p>
                                    <p class="text-xs text-muted mt-0.5" x-text="(item.quantity) + ' ' + (item.product?.unit ?? '') + ' • ' + (item.delivery_address ?? '')"></p>
                                    <p class="text-[11px] text-muted" x-text="item.delivery_district ?? ''"></p>
                                </div>
                                <span class="text-[10px] font-bold text-muted shrink-0" x-text="item.payment_status"></span>
                            </div>
                        </template>
                    </div>
                    <template x-if="selectedDetail.shipments && selectedDetail.shipments.length">
                        <div class="rounded-2xl border border-line p-4 space-y-2">
                            <p class="text-xs font-bold text-ink">Pengiriman</p>
                            <p class="text-xs text-muted" x-text="'Kurir: ' + (selectedDetail.shipments[0].courier?.user?.name ?? 'Belum ditugaskan')"></p>
                            <p class="text-xs text-muted" x-text="(selectedDetail.shipments[0].courier?.vehicle_type ?? '-') + ' • ' + (selectedDetail.shipments[0].courier?.vehicle_number ?? '-') + ' • ' + (selectedDetail.shipments[0].courier?.user?.phone ?? '-')"></p>
                            <template x-if="selectedDetail.shipments[0].proof_image_path">
                                <img :src="'{{ asset('storage') }}/' + selectedDetail.shipments[0].proof_image_path" alt="Bukti pengiriman" class="w-full h-48 object-cover rounded-xl border border-line">
                            </template>
                        </div>
                    </template>
                    <div class="flex justify-end pt-3 border-t border-line/60">
                        <button type="button" @click="openDetailModal = false" class="btn-ghost text-sm py-3 px-6">Tutup</button>
                    </div>
                </div>
            </template>
        </x-modal>

        <x-modal open="openCancelModal" size="sm"
                 eyebrow="Konfirmasi Pembatalan"
                 title="Batalkan Pesanan & Process Void/Refund">
            <template x-if="selectedOrder">
                <form :action="`/distributor/orders/${selectedOrder.id}/status`" method="POST" class="pt-5 space-y-4">
                    @csrf
                    <input type="hidden" name="status" value="CANCELLED">
                    <p class="text-xs text-muted leading-relaxed">
                        Apakah Anda yakin ingin membatalkan pesanan kolektif <span class="font-bold text-ink" x-text="selectedOrder.order_number"></span>? Pembayaran UMKM terkait akan secara otomatis diajukan Void/Refund ke gateway Midtrans dan stok produk akan dikembalikan.
                    </p>
                    <fieldset>
                        <label for="cancel_reason" class="block text-xs font-bold mb-1">Alasan Pembatalan <span class="text-muted font-normal">(opsional)</span></label>
                        <input type="text" name="cancel_reason" id="cancel_reason" placeholder="Contoh: Stok bahan baku habis / Kendala operasional" class="w-full bg-cream border border-line rounded-2xl px-4 py-3 text-xs outline-none focus:border-danger">
                    </fieldset>
                    <div class="flex items-center gap-3 pt-3">
                        <button type="button" @click="openCancelModal = false" class="flex-1 border border-line text-muted text-xs font-bold py-3 rounded-2xl cursor-pointer">Batal</button>
                        <button type="submit" class="flex-1 bg-danger hover:bg-danger/90 text-white text-xs font-bold py-3 rounded-2xl shadow-sm cursor-pointer transition">Ya, Batalkan Pesanan</button>
                    </div>
                </form>
            </template>
        </x-modal>
    </div>
</x-layouts.app>
