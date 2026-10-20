<x-layouts.app>
    <x-slot name="title">Pesanan Saya</x-slot>

    @php
        $displayName = $umkm?->business_name ?? auth()->user()->name;
        $initials = strtoupper(substr(auth()->user()->name, 0, 2));

        $hasOrders = $orderItems->isNotEmpty();
        $unpaidItems = $orderItems->filter(fn ($i) => $i->payment_status->value === 'UNPAID');
        $activeItems = $orderItems->filter(fn ($i) => $i->payment_status->value !== 'UNPAID');

        $reviewableItems = $orderItems
            ->filter(fn ($i) => $i->review === null && $i->received_confirmed_at !== null)
            ->mapWithKeys(fn ($i) => [$i->id => [
                'name' => $i->order->distributor?->company_name ?? 'Distributor',
                'action' => route('umkm.orders.review', $i),
            ]]);

        $paymentPillClass = fn ($status) => match ($status->value) {
            'PAID' => 'bg-success/10 text-success',
            'UNPAID' => 'bg-danger/10 text-danger',
            default => 'bg-brass-soft text-brass',
        };

        $deliverySteps = ['Dipesan', 'Dibayar', 'Diproses', 'Dikirim', 'Sampai'];

        $stepIndexFor = function ($order, $shipment = null) {
            $status = $order->status->value;

            return match (true) {
                in_array($status, ['DELIVERED', 'COMPLETED'], true) => 4,
                $status === 'IN_TRANSIT' || $shipment?->status === 'IN_TRANSIT' => 3,
                in_array($status, ['CONFIRMED', 'PREPARING', 'READY_TO_SHIP', 'ASSIGNED_TO_COURIER'], true) => 2,
                $status === 'PAID' => 1,
                default => 0,
            };
        };

        $deliveries = [];
        $payments = [];
        foreach ($unpaidItems->concat($activeItems) as $item) {
            $order = $item->order;
            $shipment = $order->shipments->first();
            $products = $order->items
                ->where('umkm_id', $umkm->id)
                ->map(fn ($line) => [
                    'name' => $line->product?->name ?? 'Produk',
                    'qty' => number_format($line->quantity, 0).' '.($line->product?->unit ?? ''),
                ])->values()->all();

            if ($item->payment_status->value === 'UNPAID') {
                $payments[$item->id] = [
                    'orderNumber' => $order->order_number,
                    'distributor' => $order->distributor?->company_name ?? '-',
                    'status' => $item->payment_status->label(),
                    'step' => $stepIndexFor($order, $shipment),
                    'products' => $products,
                    'subtotal' => 'Rp '.number_format($item->subtotal, 0, ',', '.'),
                    'savings' => $item->savings_amount > 0 ? 'Rp '.number_format($item->savings_amount, 0, ',', '.') : null,
                    'payUrl' => route('payment.show', $item),
                ];
            } elseif ($shipment) {
                $courier = $shipment->courier;
                $loc = $shipment->latestLocation;
                $deliveries[$item->id] = [
                    'shipmentNumber' => $shipment->shipment_number,
                    'orderNumber' => $order->order_number,
                    'statusLabel' => $shipment->status === 'DELIVERED' ? 'Pesanan Telah Tiba' : ($shipment->status === 'IN_TRANSIT' ? 'Kurir Sedang Bergerak' : 'Menunggu Pengambilan'),
                    'statusDot' => $shipment->status === 'DELIVERED' ? 'bg-forest' : ($shipment->status === 'IN_TRANSIT' ? 'bg-success' : 'bg-brass'),
                    'step' => $stepIndexFor($order, $shipment),
                    'courierName' => $courier?->user?->name ?? 'Kurir Kulaan',
                    'courierInitial' => strtoupper(substr($courier?->user?->name ?? 'K', 0, 1)),
                    'courierVehicle' => ($courier?->vehicle_type ?? '-').' ('.($courier?->vehicle_number ?? '-').')',
                    'courierPhone' => $courier?->user?->phone ?? '-',
                    'speed' => (float) ($loc?->speed ?? 0),
                    'lastUpdated' => $loc?->updated_at?->diffForHumans() ?? 'Belum ada sinyal GPS',
                    'distributor' => $order->distributor?->company_name ?? '-',
                    'address' => $item->delivery_address ?? '-',
                    'products' => $products,
                ];
            }
        }
    @endphp

    <div x-data="{
        showModal: false,
        distributorName: '',
        reviewAction: '',
        rating: 5,
        comment: '',
        lightboxSrc: '',
        showDelivery: false,
        delivery: {},
        showPayment: false,
        payment: {},
        deliverySteps: {{ json_encode($deliverySteps) }},
        deliveries: {{ json_encode((object) $deliveries) }},
        payments: {{ json_encode((object) $payments) }},
        reviewables: {{ json_encode($reviewableItems) }},
        autoRate: {{ (int) request('rate') }},
        init() {
            const target = this.reviewables[this.autoRate];
            if (target) {
                this.openRatingModal(this.autoRate, target.name, target.action);
            }
        },
        openRatingModal(id, name, action) {
            this.distributorName = name;
            this.reviewAction = action;
            this.rating = 5;
            this.comment = '';
            this.showModal = true;
        },
        openLightbox(src) { this.lightboxSrc = src; },
        closeLightbox() { this.lightboxSrc = ''; },
        openDelivery(id) { const d = this.deliveries[id]; if (! d) { return; } this.delivery = d; this.showDelivery = true; },
        openPayment(id) { const p = this.payments[id]; if (! p) { return; } this.payment = p; this.showPayment = true; }
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
                <x-notification-bell />
            </div>

            <div class="px-4 pt-2 mt-4 mb-4">
                <div class="bg-gradient-to-r from-forest to-forest-dark text-white rounded-2xl p-5 shadow-sm">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[0.6875rem] font-semibold bg-white/15 text-white">Pesanan Kolektif</span>
                    <h1 class="font-extrabold text-xl mt-2 tracking-tight">Pesanan Saya</h1>
                    <p class="text-xs text-white/80 mt-1 leading-relaxed">
                        {{ $tab === 'aktif' ? 'Pantau pembayaran dan pengiriman yang sedang berjalan.' : 'Riwayat pesanan yang sudah kamu terima beserta bukti pengiriman dan ulasanmu.' }}
                    </p>
                    <a href="{{ route('umkm.catalog.index') }}" class="inline-block mt-3 bg-brass text-white text-xs font-bold px-4 py-2.5 rounded-xl">+ Pesan Lagi</a>
                </div>
            </div>

            <div class="px-4 mb-5">
                <div class="bg-white border border-line rounded-2xl p-1.5 flex gap-1">
                    <a href="{{ route('umkm.orders.index', ['tab' => 'aktif']) }}" @if ($tab === 'aktif') aria-current="page" @endif class="flex-1 text-center text-xs font-bold py-2.5 rounded-xl transition {{ $tab === 'aktif' ? 'bg-forest text-white' : 'text-muted hover:bg-cream' }}">Aktif</a>
                    <a href="{{ route('umkm.orders.index', ['tab' => 'riwayat']) }}" @if ($tab === 'riwayat') aria-current="page" @endif class="flex-1 text-center text-xs font-bold py-2.5 rounded-xl transition {{ $tab === 'riwayat' ? 'bg-forest text-white' : 'text-muted hover:bg-cream' }}">Riwayat</a>
                </div>
            </div>

            @if ($hasOrders)
                @if ($tab === 'aktif')
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
                                                <div class="flex items-center gap-2">
                                                    <button type="button" @click="openPayment({{ $item->id }})" class="text-xs font-semibold text-muted border border-line rounded-lg px-3 py-2 cursor-pointer">Detail</button>
                                                    <a href="{{ route('payment.show', $item) }}" class="bg-forest text-white text-xs font-bold py-2.5 px-4 rounded-xl">Bayar Sekarang &rarr;</a>
                                                </div>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if ($activeItems->isNotEmpty())
                        <section class="px-4 pb-6">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="w-2.5 h-2.5 rounded-full bg-success"></span>
                                <h2 class="text-xs font-bold text-muted uppercase tracking-wider">Dalam Proses &amp; Pengiriman ({{ $activeItems->count() }})</h2>
                            </div>
                            <div class="space-y-3">
                                @foreach ($activeItems as $item)
                                    @php
                                        $shipment = $item->order->shipments->first();
                                        $hasProof = $shipment && $shipment->status === 'DELIVERED' && $shipment->proof_image_path;
                                        $isReceived = $item->received_confirmed_at !== null;
                                        $needsConfirm = ! $isReceived && in_array($item->order->status->value, ['DELIVERED', 'COMPLETED'], true);
                                    @endphp
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

                                            @if ($hasProof)
                                                <button type="button" @click="openLightbox('{{ asset('storage/'.$shipment->proof_image_path) }}')" class="mt-3 w-full flex items-center gap-3 p-2.5 rounded-xl bg-cream/60 border border-line text-left cursor-pointer">
                                                    <img src="{{ asset('storage/'.$shipment->proof_image_path) }}" alt="Bukti pengiriman" class="w-14 h-14 rounded-lg object-cover border border-line">
                                                    <div class="min-w-0 flex-1">
                                                        <p class="text-[11px] font-bold text-ink">Bukti pengiriman kurir</p>
                                                        <p class="text-[11px] text-muted truncate">{{ $shipment->courier?->user?->name ?? 'Kurir Kulaan' }} &bull; {{ optional($shipment->delivered_at)->format('d M Y, H:i') }}</p>
                                                        <p class="text-[10px] font-semibold text-forest mt-0.5">Ketuk untuk perbesar</p>
                                                    </div>
                                                </button>
                                            @endif

                                            <div class="flex items-center justify-between mt-3">
                                                <span class="font-extrabold text-ink text-base tabular-nums">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                            </div>
                                        </div>

                                        <div class="px-4 pb-4 pt-3 border-t border-line/60 flex items-center gap-2 flex-wrap">
                                            <p class="w-full text-[11px] font-semibold text-muted">Status: <span class="text-ink">{{ $item->order->status->label() }}</span></p>
                                            @if ($needsConfirm)
                                                <form method="POST" action="{{ route('umkm.orders.confirm', $item) }}" class="w-full">
                                                    @csrf
                                                    <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 text-sm font-bold text-white bg-forest rounded-xl px-4 py-3 cursor-pointer">
                                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                                        Pesanan Diterima
                                                    </button>
                                                </form>
                                            @endif

                                            @if ($shipment && ! $isReceived)
                                                <a href="{{ route('umkm.tracking.show', $shipment) }}" class="text-xs font-bold text-forest">Lacak Kurir &rarr;</a>
                                            @endif

                                            @if (! $shipment)
                                                <p class="text-[11px] text-muted">Pelacakan kurir muncul setelah distributor menugaskan kurir.</p>
                                            @endif

                                            <a href="{{ route('payment.show', $item) }}" class="text-xs font-semibold text-muted border border-line rounded-lg px-3 py-1.5 ml-auto">Invoice</a>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endif
                @else
                    <section class="px-4 pb-6">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-2.5 h-2.5 rounded-full bg-success"></span>
                            <h2 class="text-xs font-bold text-muted uppercase tracking-wider">Riwayat Pesanan Selesai ({{ $orderItems->count() }})</h2>
                        </div>
                        <div class="space-y-3">
                            @foreach ($orderItems as $item)
                                @php $shipment = $item->order->shipments->first(); @endphp
                                <article class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
                                    <div class="flex items-center justify-between px-4 py-3 border-b border-line/60 bg-cream/40">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="font-mono text-xs text-muted">{{ $item->order->order_number }}</span>
                                            <span class="text-[10px] font-bold bg-success/10 text-success px-2 py-0.5 rounded-full">Diterima</span>
                                        </div>
                                        <span class="text-[10px] text-muted">{{ optional($item->received_confirmed_at)->format('d M Y') }}</span>
                                    </div>
                                    <div class="px-4 py-3.5">
                                        <p class="font-bold text-ink text-sm">{{ $item->product->name }}</p>
                                        <p class="text-xs text-muted mt-0.5">{{ number_format($item->quantity, 0) }} {{ $item->product->unit }} &bull; {{ $item->order->distributor->company_name }}</p>

                                        @if ($shipment && $shipment->proof_image_path)
                                            <button type="button" @click="openLightbox('{{ asset('storage/'.$shipment->proof_image_path) }}')" class="mt-3 w-full flex items-center gap-3 p-2.5 rounded-xl bg-cream/60 border border-line text-left cursor-pointer">
                                                <img src="{{ asset('storage/'.$shipment->proof_image_path) }}" alt="Bukti pengiriman" class="w-14 h-14 rounded-lg object-cover border border-line">
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-[11px] font-bold text-ink">Bukti pengiriman kurir</p>
                                                    <p class="text-[11px] text-muted truncate">{{ $shipment->courier?->user?->name ?? 'Kurir Kulaan' }} &bull; {{ optional($shipment->delivered_at)->format('d M Y, H:i') }}</p>
                                                    <p class="text-[10px] text-muted mt-0.5">Surat jalan {{ $shipment->shipment_number }}</p>
                                                </div>
                                            </button>
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
                                            @else
                                                <button type="button"
                                                        @click="openRatingModal({{ $item->id }}, '{{ addslashes($item->order->distributor->company_name) }}', '{{ route('umkm.orders.review', $item) }}')"
                                                        class="inline-flex items-center gap-1 text-[11px] font-semibold text-brass bg-brass-soft rounded-lg border border-brass/30 px-3 py-1.5 transition-colors cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.9 6.26 6.6.56-5 4.36 1.5 6.45L12 16.9 5.99 19.63l1.5-6.45-5-4.36 6.6-.56L12 2z"/></svg>
                                                    Beri Ulasan
                                                </button>
                                            @endif

                                            @if ($shipment)
                                                <button type="button" @click="openDelivery({{ $item->id }})" class="inline-flex items-center gap-1 text-[11px] font-semibold text-forest bg-forest/10 rounded-lg border border-forest/20 px-3 py-1.5 cursor-pointer">Detail Pengiriman</button>
                                            @endif

                                            <a href="{{ route('payment.show', $item) }}" class="text-xs font-semibold text-muted border border-line rounded-lg px-3 py-1.5 ml-auto">Invoice</a>
                                        </div>

                                        @if ($item->review?->comment)
                                            <p class="text-[11px] text-muted mt-2.5 leading-relaxed border-t border-line/60 pt-2.5">{{ $item->review->comment }}</p>
                                        @endif
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
                        @if ($tab === 'aktif')
                            <h2 class="font-display font-bold text-ink text-lg mt-4">Tidak Ada Pesanan Aktif</h2>
                            <p class="text-xs text-muted mt-2 leading-relaxed max-w-sm mx-auto">
                                Pesanan yang menunggu pembayaran atau sedang dikirim akan tampil di sini.
                            </p>
                        @else
                            <h2 class="font-display font-bold text-ink text-lg mt-4">Belum Ada Riwayat Pesanan</h2>
                            <p class="text-xs text-muted mt-2 leading-relaxed max-w-sm mx-auto">
                                Setelah kamu konfirmasi pesanan diterima, bukti pengiriman dan ulasan mitra tampil di sini.
                            </p>
                        @endif
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
                <x-umkm.sidebar-nav active="orders" />
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
                    <div class="bg-gradient-to-r from-forest to-forest-dark text-white rounded-2xl p-7 shadow-sm mb-6">
                        <div class="flex items-center justify-between gap-4 flex-wrap">
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[0.6875rem] font-semibold bg-white/15 text-white">Pesanan Kolektif</span>
                                <h1 class="font-extrabold text-3xl mt-2 tracking-tight">Pesanan Saya</h1>
                                <p class="text-sm text-white/80 mt-1">
                                    {{ $tab === 'aktif' ? 'Pantau status pembayaran dan pengiriman yang sedang berjalan.' : 'Riwayat pesanan yang sudah diterima beserta bukti pengiriman dan ulasanmu.' }}
                                </p>
                            </div>
                            <a href="{{ route('umkm.catalog.index') }}" class="bg-brass text-white text-sm font-bold px-5 py-3 rounded-xl">+ Pesan Lagi</a>
                        </div>
                    </div>

                    <div class="bg-white border border-line rounded-2xl p-1.5 flex gap-1 w-full max-w-md mb-6">
                        <a href="{{ route('umkm.orders.index', ['tab' => 'aktif']) }}" @if ($tab === 'aktif') aria-current="page" @endif class="flex-1 text-center text-sm font-bold py-2.5 rounded-xl transition {{ $tab === 'aktif' ? 'bg-forest text-white' : 'text-muted hover:bg-cream' }}">Aktif</a>
                        <a href="{{ route('umkm.orders.index', ['tab' => 'riwayat']) }}" @if ($tab === 'riwayat') aria-current="page" @endif class="flex-1 text-center text-sm font-bold py-2.5 rounded-xl transition {{ $tab === 'riwayat' ? 'bg-forest text-white' : 'text-muted hover:bg-cream' }}">Riwayat</a>
                    </div>

                    @if ($hasOrders)
                        @if ($tab === 'aktif')
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
                                                        <button type="button" @click="openPayment({{ $item->id }})" class="text-xs font-semibold text-muted border border-line rounded-lg px-3 py-2 cursor-pointer">Detail</button>
                                                        <a href="{{ route('payment.show', $item) }}" class="bg-forest text-white text-sm font-bold py-3 px-5 rounded-xl">Bayar Sekarang &rarr;</a>
                                                    </div>
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                </section>
                            @endif

                            @if ($activeItems->isNotEmpty())
                                <section>
                                    <div class="flex items-center gap-2 mb-4">
                                        <span class="w-2.5 h-2.5 rounded-full bg-success"></span>
                                        <h2 class="text-xs font-bold text-muted uppercase tracking-wider">Dalam Proses &amp; Pengiriman ({{ $activeItems->count() }})</h2>
                                    </div>
                                    <div class="space-y-4">
                                        @foreach ($activeItems as $item)
                                            @php
                                                $shipment = $item->order->shipments->first();
                                                $hasProof = $shipment && $shipment->status === 'DELIVERED' && $shipment->proof_image_path;
                                                $isReceived = $item->received_confirmed_at !== null;
                                                $needsConfirm = ! $isReceived && in_array($item->order->status->value, ['DELIVERED', 'COMPLETED'], true);
                                            @endphp
                                            <article class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
                                                <div class="flex items-center justify-between px-5 py-3 border-b border-line/60">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-mono text-xs text-muted">{{ $item->order->order_number }}</span>
                                                        <span class="text-[10px] font-bold {{ $paymentPillClass($item->payment_status) }} px-2 py-0.5 rounded-full">{{ $item->payment_status->label() }}</span>
                                                    </div>
                                                    <span class="text-[11px] text-muted">{{ $item->created_at->format('d M Y') }}</span>
                                                </div>
                                                <div class="px-5 py-4 flex items-start justify-between gap-4 flex-wrap">
                                                    <div class="min-w-0">
                                                        <p class="font-bold text-ink">{{ $item->product->name }}</p>
                                                        <p class="text-xs text-muted mt-0.5">{{ number_format($item->quantity, 0) }} {{ $item->product->unit }} &bull; {{ $item->order->distributor->company_name }}</p>
                                                        @if ($item->savings_amount > 0)
                                                            <p class="text-xs font-semibold text-brass mt-1">Hemat Rp {{ number_format($item->savings_amount, 0, ',', '.') }} vs eceran</p>
                                                        @endif
                                                        @if ($hasProof)
                                                            <button type="button" @click="openLightbox('{{ asset('storage/'.$shipment->proof_image_path) }}')" class="mt-3 flex items-center gap-3 p-2.5 rounded-xl bg-cream/60 border border-line text-left cursor-pointer">
                                                                <img src="{{ asset('storage/'.$shipment->proof_image_path) }}" alt="Bukti pengiriman" class="w-16 h-16 rounded-lg object-cover border border-line">
                                                                <div class="min-w-0">
                                                                    <p class="text-xs font-bold text-ink">Bukti pengiriman kurir</p>
                                                                    <p class="text-xs text-muted truncate">{{ $shipment->courier?->user?->name ?? 'Kurir Kulaan' }} &bull; {{ optional($shipment->delivered_at)->format('d M Y, H:i') }}</p>
                                                                    <p class="text-[11px] font-semibold text-forest mt-0.5">Klik untuk perbesar</p>
                                                                </div>
                                                            </button>
                                                        @endif
                                                    </div>
                                                    <span class="font-extrabold text-ink text-lg tabular-nums">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                                </div>

                                                <div class="px-5 pb-4 pt-3 border-t border-line/60 flex items-center gap-3 flex-wrap">
                                                    <p class="w-full text-xs font-semibold text-muted">Status: <span class="text-ink">{{ $item->order->status->label() }}</span></p>
                                                    @if ($needsConfirm)
                                                        <form method="POST" action="{{ route('umkm.orders.confirm', $item) }}">
                                                            @csrf
                                                            <button type="submit" class="inline-flex items-center gap-1.5 text-sm font-bold text-white bg-forest rounded-xl px-4 py-2.5 cursor-pointer">
                                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                                                Pesanan Diterima
                                                            </button>
                                                        </form>
                                                    @endif

                                                    @if ($shipment && ! $isReceived)
                                                        <a href="{{ route('umkm.tracking.show', $shipment) }}" class="text-sm font-bold text-forest hover:underline">Lacak Kurir &rarr;</a>
                                                    @endif

                                                    @if (! $shipment)
                                                        <p class="text-xs text-muted">Pelacakan kurir muncul setelah distributor menugaskan kurir.</p>
                                                    @endif

                                                    <a href="{{ route('payment.show', $item) }}" class="text-xs font-semibold text-muted border border-line rounded-lg px-3 py-2 ml-auto">Invoice</a>
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                </section>
                            @endif
                        @else
                            <section>
                                <div class="flex items-center gap-2 mb-4">
                                    <span class="w-2.5 h-2.5 rounded-full bg-success"></span>
                                    <h2 class="text-xs font-bold text-muted uppercase tracking-wider">Riwayat Pesanan Selesai ({{ $orderItems->count() }})</h2>
                                </div>
                                <div class="space-y-4">
                                    @foreach ($orderItems as $item)
                                        @php $shipment = $item->order->shipments->first(); @endphp
                                        <article class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
                                            <div class="flex items-center justify-between px-5 py-3 border-b border-line/60 bg-cream/40">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-mono text-xs text-muted">{{ $item->order->order_number }}</span>
                                                    <span class="text-[10px] font-bold bg-success/10 text-success px-2 py-0.5 rounded-full">Diterima</span>
                                                </div>
                                                <span class="text-[11px] text-muted">{{ optional($item->received_confirmed_at)->format('d M Y') }}</span>
                                            </div>
                                            <div class="px-5 py-4">
                                                <div class="flex items-start justify-between gap-4 flex-wrap">
                                                    <div class="min-w-0">
                                                        <p class="font-bold text-ink">{{ $item->product->name }}</p>
                                                        <p class="text-xs text-muted mt-0.5">{{ number_format($item->quantity, 0) }} {{ $item->product->unit }} &bull; {{ $item->order->distributor->company_name }}</p>
                                                    </div>
                                                    <span class="font-extrabold text-ink tabular-nums">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                                </div>

                                                @if ($shipment && $shipment->proof_image_path)
                                                    <button type="button" @click="openLightbox('{{ asset('storage/'.$shipment->proof_image_path) }}')" class="mt-3 flex items-center gap-3 p-2.5 rounded-xl bg-cream/60 border border-line text-left cursor-pointer">
                                                        <img src="{{ asset('storage/'.$shipment->proof_image_path) }}" alt="Bukti pengiriman" class="w-16 h-16 rounded-lg object-cover border border-line">
                                                        <div class="min-w-0">
                                                            <p class="text-xs font-bold text-ink">Bukti pengiriman kurir</p>
                                                            <p class="text-xs text-muted truncate">{{ $shipment->courier?->user?->name ?? 'Kurir Kulaan' }} &bull; {{ optional($shipment->delivered_at)->format('d M Y, H:i') }}</p>
                                                            <p class="text-[11px] text-muted mt-0.5">Surat jalan {{ $shipment->shipment_number }}</p>
                                                        </div>
                                                    </button>
                                                @endif

                                                <div class="flex items-center gap-3 mt-3 flex-wrap">
                                                    @if ($item->review)
                                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-brass bg-brass-soft px-2.5 py-1 rounded-full border border-brass/30">
                                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.9 6.26 6.6.56-5 4.36 1.5 6.45L12 16.9 5.99 19.63l1.5-6.45-5-4.36 6.6-.56L12 2z"/></svg>
                                                            {{ $item->review->rating }}/5
                                                        </span>
                                                    @else
                                                        <button type="button"
                                                                @click="openRatingModal({{ $item->id }}, '{{ addslashes($item->order->distributor->company_name) }}', '{{ route('umkm.orders.review', $item) }}')"
                                                                class="inline-flex items-center gap-1 text-[11px] font-semibold text-brass bg-brass-soft rounded-lg border border-brass/30 px-3 py-2 transition-colors cursor-pointer">
                                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.9 6.26 6.6.56-5 4.36 1.5 6.45L12 16.9 5.99 19.63l1.5-6.45-5-4.36 6.6-.56L12 2z"/></svg>
                                                            Beri Ulasan
                                                        </button>
                                                    @endif

                                                    @if ($shipment)
                                                        <button type="button" @click="openDelivery({{ $item->id }})" class="inline-flex items-center gap-1 text-[11px] font-semibold text-forest bg-forest/10 rounded-lg border border-forest/20 px-3 py-2 cursor-pointer">Detail Pengiriman</button>
                                                    @endif

                                                    <a href="{{ route('payment.show', $item) }}" class="text-xs font-semibold text-muted border border-line rounded-lg px-3 py-2 ml-auto">Invoice</a>
                                                </div>

                                                @if ($item->review?->comment)
                                                    <p class="text-xs text-muted mt-3 leading-relaxed border-t border-line/60 pt-3">{{ $item->review->comment }}</p>
                                                @endif
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
                            @if ($tab === 'aktif')
                                <h2 class="font-display font-bold text-ink text-xl mt-4">Tidak Ada Pesanan Aktif</h2>
                                <p class="text-sm text-muted mt-2 leading-relaxed max-w-sm mx-auto">
                                    Pesanan yang menunggu pembayaran atau sedang dalam pengiriman akan tampil di sini.
                                </p>
                            @else
                                <h2 class="font-display font-bold text-ink text-xl mt-4">Belum Ada Riwayat Pesanan</h2>
                                <p class="text-sm text-muted mt-2 leading-relaxed max-w-sm mx-auto">
                                    Setelah kamu konfirmasi pesanan diterima, bukti pengiriman dan ulasan mitra tampil di sini.
                                </p>
                            @endif
                            <a href="{{ route('umkm.catalog.index') }}" class="btn-primary inline-flex text-sm font-bold px-5 py-2.5 mt-5">
                                Buka Katalog &rarr;
                            </a>
                        </section>
                    @endif
                </main>
            </div>
        </div>

        {{-- MOBILE BOTTOM TAB --}}
        <x-umkm.mobile-tab active="orders" />

        <!-- Lightbox Bukti Pengiriman -->
        <div x-show="lightboxSrc" x-cloak @keydown.escape.window="closeLightbox()" @click.self="closeLightbox()" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-ink/80 backdrop-blur-sm" role="dialog" aria-modal="true" aria-label="Bukti pengiriman">
            <button type="button" @click="closeLightbox()" class="absolute top-4 right-4 w-11 h-11 rounded-full bg-white/15 text-white flex items-center justify-center cursor-pointer" aria-label="Tutup">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6 6 18"/></svg>
            </button>
            <img :src="lightboxSrc" alt="Bukti pengiriman diperbesar" class="max-h-[85vh] max-w-full rounded-2xl border border-white/20 shadow-2xl object-contain">
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

        <!-- Detail Pengiriman Modal -->
        <x-modal open="showDelivery" size="2xl" title="Detail Pengiriman">
            <div class="pt-4 space-y-4">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <p class="text-xs text-muted">
                        Surat jalan <span class="font-mono font-bold text-ink" x-text="delivery.shipmentNumber"></span>
                        &bull; Pesanan <span class="font-mono" x-text="delivery.orderNumber"></span>
                    </p>
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-cream/60 rounded-full border border-line text-[11px] font-bold text-ink">
                        <span class="w-2 h-2 rounded-full" :class="delivery.statusDot"></span>
                        <span x-text="delivery.statusLabel"></span>
                    </span>
                </div>

                <ol class="bg-cream/40 rounded-2xl border border-line px-2 sm:px-4 py-3 flex items-start" aria-label="Status pengiriman">
                    <template x-for="(label, i) in deliverySteps" :key="i">
                        <li class="flex-1 flex flex-col items-center gap-1.5 min-w-0 relative">
                            <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-extrabold shrink-0"
                                  :class="i < delivery.step ? 'bg-forest text-white' : (i === delivery.step ? 'bg-brass text-ink ring-4 ring-brass/20' : 'bg-cream text-muted border border-line')">
                                <template x-if="i < delivery.step">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </template>
                                <template x-if="i >= delivery.step"><span x-text="i + 1"></span></template>
                            </span>
                            <span class="text-[9px] sm:text-[11px] font-bold text-center leading-tight" :class="i <= delivery.step ? 'text-ink' : 'text-muted/60'" x-text="label"></span>
                            <template x-if="i < deliverySteps.length - 1">
                                <span class="absolute top-3.5 left-[calc(50%+1.25rem)] right-[calc(-50%+1.25rem)] h-0.5 rounded" :class="i < delivery.step ? 'bg-forest' : 'bg-line'" aria-hidden="true"></span>
                            </template>
                        </li>
                    </template>
                </ol>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="bg-white rounded-2xl border border-line p-4">
                        <h3 class="text-[11px] font-bold text-ink uppercase tracking-wider mb-3">Kurir Ditugaskan</h3>
                        <div class="flex items-center gap-3">
                            <span class="w-12 h-12 rounded-2xl bg-forest text-white flex items-center justify-center font-extrabold text-lg shrink-0" x-text="delivery.courierInitial"></span>
                            <div class="min-w-0">
                                <p class="font-bold text-sm text-ink truncate" x-text="delivery.courierName"></p>
                                <p class="text-xs text-muted truncate" x-text="delivery.courierVehicle"></p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mt-3 text-xs">
                            <div class="bg-cream/60 rounded-xl p-2.5 border border-line/60">
                                <p class="text-[10px] text-muted">No. Telepon</p>
                                <p class="font-bold text-ink mt-0.5" x-text="delivery.courierPhone"></p>
                            </div>
                            <div class="bg-cream/60 rounded-xl p-2.5 border border-line/60">
                                <p class="text-[10px] text-muted">Kecepatan</p>
                                <p class="font-bold text-ink mt-0.5"><span x-text="delivery.speed"></span> km/jam</p>
                            </div>
                        </div>
                        <p class="text-[11px] text-muted mt-2.5">Update terakhir: <span class="font-semibold text-ink" x-text="delivery.lastUpdated"></span></p>
                        <a x-show="delivery.courierPhone && delivery.courierPhone !== '-'" :href="'tel:' + (delivery.courierPhone || '')" class="mt-3 w-full inline-flex items-center justify-center gap-2 bg-forest text-white text-sm font-bold py-3 rounded-xl">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2.25 6.75c0 8.28 6.72 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.37c0-.5-.28-.96-.73-1.19l-2.5-1.25a1.13 1.13 0 0 0-1.28.21l-.82.82a16.5 16.5 0 0 1-7.07-7.07l.82-.82a1.13 1.13 0 0 0 .21-1.28L9.13 4.06a1.13 1.13 0 0 0-1.19-.73H6.75A2.25 2.25 0 0 0 4.5 5.58v1.17Z"/></svg>
                            Hubungi Kurir
                        </a>
                    </div>

                    <div class="bg-white rounded-2xl border border-line p-4">
                        <h3 class="text-[11px] font-bold text-ink uppercase tracking-wider mb-3">Isi Pengiriman</h3>
                        <div class="space-y-2 text-sm">
                            <template x-for="(p, i) in delivery.products" :key="i">
                                <div class="flex justify-between gap-2">
                                    <span class="text-muted" x-text="p.name"></span>
                                    <span class="font-bold text-ink whitespace-nowrap" x-text="p.qty"></span>
                                </div>
                            </template>
                            <div class="flex justify-between gap-2 pt-2 border-t border-line/60">
                                <span class="text-muted">Distributor</span>
                                <span class="font-bold text-ink text-right" x-text="delivery.distributor"></span>
                            </div>
                            <div class="pt-2 border-t border-line/60">
                                <span class="text-xs text-muted">Alamat Kirim</span>
                                <p class="text-xs text-ink mt-0.5 leading-relaxed" x-text="delivery.address"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-5 mt-1 border-t border-black/[0.06]">
                <button type="button" @click="showDelivery = false" class="btn-ghost text-sm py-3 w-full">Tutup</button>
            </div>
        </x-modal>

        <!-- Detail Pesanan (Menunggu Pembayaran) Modal -->
        <x-modal open="showPayment" size="xl" title="Detail Pesanan" eyebrow="Menunggu Pembayaran">
            <div class="pt-4 space-y-4">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <p class="text-xs text-muted">Pesanan <span class="font-mono font-bold text-ink" x-text="payment.orderNumber"></span></p>
                    <span class="text-[11px] font-bold bg-danger/10 text-danger px-3 py-1.5 rounded-full" x-text="payment.status"></span>
                </div>

                <ol class="bg-cream/40 rounded-2xl border border-line px-2 sm:px-4 py-3 flex items-start" aria-label="Status pesanan">
                    <template x-for="(label, i) in deliverySteps" :key="i">
                        <li class="flex-1 flex flex-col items-center gap-1.5 min-w-0 relative">
                            <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-extrabold shrink-0"
                                  :class="i < payment.step ? 'bg-forest text-white' : (i === payment.step ? 'bg-brass text-ink ring-4 ring-brass/20' : 'bg-cream text-muted border border-line')">
                                <template x-if="i < payment.step">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </template>
                                <template x-if="i >= payment.step"><span x-text="i + 1"></span></template>
                            </span>
                            <span class="text-[9px] sm:text-[11px] font-bold text-center leading-tight" :class="i <= payment.step ? 'text-ink' : 'text-muted/60'" x-text="label"></span>
                            <template x-if="i < deliverySteps.length - 1">
                                <span class="absolute top-3.5 left-[calc(50%+1.25rem)] right-[calc(-50%+1.25rem)] h-0.5 rounded" :class="i < payment.step ? 'bg-forest' : 'bg-line'" aria-hidden="true"></span>
                            </template>
                        </li>
                    </template>
                </ol>

                <div class="bg-white rounded-2xl border border-line p-4">
                    <h3 class="text-[11px] font-bold text-ink uppercase tracking-wider mb-3">Rincian Pesanan</h3>
                    <div class="space-y-2 text-sm">
                        <template x-for="(p, i) in payment.products" :key="i">
                            <div class="flex justify-between gap-2">
                                <span class="text-muted" x-text="p.name"></span>
                                <span class="font-bold text-ink whitespace-nowrap" x-text="p.qty"></span>
                            </div>
                        </template>
                        <div class="flex justify-between gap-2 pt-2 border-t border-line/60">
                            <span class="text-muted">Distributor</span>
                            <span class="font-bold text-ink text-right" x-text="payment.distributor"></span>
                        </div>
                        <template x-if="payment.savings">
                            <div class="flex justify-between gap-2">
                                <span class="text-brass font-semibold text-xs">Hemat vs harga eceran</span>
                                <span class="font-bold text-brass text-xs" x-text="payment.savings"></span>
                            </div>
                        </template>
                        <div class="flex justify-between items-center gap-2 pt-2 border-t border-line/60">
                            <span class="text-muted">Total tagihan</span>
                            <span class="font-extrabold text-forest text-lg tabular-nums" x-text="payment.subtotal"></span>
                        </div>
                    </div>
                </div>

                <p class="bg-brass-soft/60 rounded-2xl border border-brass/30 p-3.5 text-[11px] text-ink leading-relaxed">
                    Selesaikan pembayaran agar pesanan masuk ke proses pengiriman. Lanjutkan ke halaman pembayaran lewat tombol di bawah.
                </p>
            </div>

            <div class="pt-5 mt-1 border-t border-black/[0.06] flex flex-col-reverse sm:flex-row gap-3">
                <a :href="payment.payUrl" class="btn-primary text-sm py-3 px-6 w-full sm:w-auto text-center">Bayar Sekarang</a>
                <button type="button" @click="showPayment = false" class="btn-ghost text-sm py-3 w-full sm:w-auto">Tutup</button>
            </div>
        </x-modal>
    </div>
</x-layouts.app>
