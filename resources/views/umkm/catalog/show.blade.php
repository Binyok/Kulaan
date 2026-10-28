<x-layouts.app>
    <x-slot name="title">{{ $product->name }} — Detail & Penawaran Distributor</x-slot>

    @php
        $maxStock = 9999;
        if (! empty($offers)) {
            $stocks = array_map(fn($o) => (float) $o['supplier_product']->stock_available, $offers);
            $maxStock = max($stocks) > 0 ? max($stocks) : 9999;
        }

        $tiersDataByDistributor = [];
        $initialQuantities = [];
        $defaultQty = max(1, (int) round($quantity));

        foreach ($offers as $offer) {
            $sp = $offer['supplier_product'];
            $tiers = [];
            foreach ($sp->priceTiers as $t) {
                $tiers[] = [
                    'min' => (float) $t->min_quantity,
                    'max' => $t->max_quantity !== null ? (float) $t->max_quantity : null,
                    'price' => (float) $t->unit_price,
                ];
            }
            $tiersDataByDistributor[$offer['distributor']->id] = [
                'base' => (float) $sp->base_retail_price,
                'moq' => (float) $sp->moq,
                'stock' => (float) $sp->stock_available,
                'tiers' => $tiers,
            ];
            $initialQuantities[$offer['distributor']->id] = $defaultQty;
        }

        $displayName = $umkm->business_name ?? auth()->user()->name;
        $initials = strtoupper(substr(auth()->user()->name, 0, 2));
        $cartCount = app(\App\Services\CartService::class)->count();
        $initial = strtoupper(substr($product->name, 0, 1));
        $benchmark = (float) $product->benchmark_retail_price;

        $topOffer = null;
        foreach ($offers as $offer) {
            if (($offer['rank'] ?? null) === 1) {
                $topOffer = $offer;
                break;
            }
        }
        if (! $topOffer && ! empty($offers)) {
            $topOffer = $offers[0];
        }

        $displaySp = $topOffer['supplier_product'] ?? null;
        $displayTiers = $displaySp ? $displaySp->priceTiers : collect();
        $displayUnitPrice = (float) ($topOffer['unit_price'] ?? $benchmark);
        $displaySavings = (int) max(0, round(($benchmark - $displayUnitPrice) * $defaultQty));

        $bestDiscount = 0.0;
        $stockValues = [];
        foreach ($offers as $offer) {
            $stockValues[] = (float) $offer['supplier_product']->stock_available;
            $offerPrice = (float) $offer['unit_price'];
            if ($benchmark > 0 && $offerPrice < $benchmark) {
                $bestDiscount = max($bestDiscount, ($benchmark - $offerPrice) / $benchmark * 100);
            }
        }
        $bestDiscount = (int) round($bestDiscount);
        $maxStockShown = ! empty($stockValues) ? max($stockValues) : 0;
    @endphp

    <div x-data="marketplaceDetail({
            benchmark: {{ (float) $product->benchmark_retail_price }},
            unit: '{{ $product->unit }}',
            maxQty: {{ $maxStock }},
            tiersData: {{ Js::from($tiersDataByDistributor) }},
            quantities: {{ Js::from($initialQuantities) }}
         })">

        {{-- MOBILE --}}
        <div class="lg:hidden pb-24">
            <div class="px-4 pt-6 flex items-center justify-between">
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
                        <p class="text-[15px] font-extrabold leading-tight">{{ $displayName }}</p>
                    </div>
                </div>
                <a href="{{ route('umkm.cart.index') }}" class="w-10 h-10 rounded-2xl bg-forest flex items-center justify-center text-white relative">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2.25 3h1.39c.51 0 .96.34 1.09.84l.38 1.44M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.22c1.12-2.3 2.1-4.68 2.92-7.14a60.11 60.11 0 0 0-16.54-1.84M7.5 14.25 5.11 5.27M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                    @if ($cartCount > 0)
                        <span class="absolute -top-1.5 -right-1.5 min-w-[20px] h-5 px-1 rounded-full bg-brass text-white text-[11px] font-bold flex items-center justify-center">{{ $cartCount }}</span>
                    @endif
                </a>
            </div>

            <div class="px-4 mt-4 flex items-center gap-3">
                <a href="{{ route('umkm.catalog.index') }}" class="w-10 h-10 rounded-2xl bg-white border border-line flex items-center justify-center text-ink">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                </a>
                <p class="text-sm font-extrabold">Detail Produk</p>
            </div>

            <div class="px-4 mt-3">
                <div class="relative h-60 rounded-3xl bg-forest/10 border border-line overflow-hidden flex items-center justify-center">
                    <span class="text-[7rem] font-extrabold text-forest/20 select-none">{{ $initial }}</span>
                    @if ($bestDiscount > 0)
                        <span class="absolute top-3 left-3 bg-danger text-white text-[11px] font-bold px-2.5 py-1 rounded-full">-{{ $bestDiscount }}%</span>
                    @endif
                    <span class="absolute top-3 right-3 bg-white/85 backdrop-blur border border-line text-ink text-[11px] font-bold px-2.5 py-1 rounded-full">{{ $product->category->name ?? 'Bahan Baku' }}</span>
                </div>
            </div>

            <div class="px-4 mt-4">
                <div class="flex items-center gap-1.5 text-xs text-muted">
                    <svg class="w-4 h-4 text-muted/40" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                    <span>Rating produk belum tersedia</span>
                </div>
                <h1 class="text-xl font-extrabold mt-1.5">{{ $product->name }}</h1>
                <p class="text-xs text-muted mt-0.5">Satuan per {{ $product->unit }} &middot; {{ $product->category->name ?? 'Bahan Baku' }}</p>
                <p class="text-[13px] text-muted leading-relaxed mt-2">{{ $product->standard_specification ?? 'Belum ada deskripsi produk untuk bahan baku ini.' }}</p>
            </div>

            <div class="px-4 mt-4">
                <div class="bg-white border border-line rounded-3xl p-4">
                    <p class="text-xs font-bold uppercase tracking-wider text-muted">Harga bertingkat <span class="normal-case font-medium">(estimasi)</span></p>
                    <div class="mt-2.5 space-y-2 text-[13px]">
                        <div class="flex justify-between"><span class="text-muted">Eceran</span><span class="font-bold tnum">Rp {{ number_format($benchmark, 0, ',', '.') }}<span class="text-muted font-medium">/{{ $product->unit }}</span></span></div>
                        @forelse ($displayTiers as $tier)
                            @php
                                $isActiveTier = $defaultQty >= (float) $tier->min_quantity && ($tier->max_quantity === null || $defaultQty <= (float) $tier->max_quantity);
                            @endphp
                            <div class="flex justify-between {{ $isActiveTier ? 'bg-brass-soft/60 -mx-1 px-1 py-1.5 rounded-xl' : '' }}">
                                <span class="{{ $isActiveTier ? 'font-bold text-brass' : 'text-muted' }}">&ge; {{ number_format((float) $tier->min_quantity, 0, ',', '.') }} {{ $product->unit }}</span>
                                <span class="{{ $isActiveTier ? 'font-extrabold text-brass' : 'font-bold' }} tnum">Rp {{ number_format((float) $tier->unit_price, 0, ',', '.') }}<span class="{{ $isActiveTier ? 'font-medium' : 'text-muted font-medium' }}">/{{ $product->unit }}</span></span>
                            </div>
                        @empty
                            <p class="text-[13px] text-muted">Harga bertingkat grosir belum tersedia.</p>
                        @endforelse
                    </div>
                    @if ($displaySavings > 0)
                        <div class="mt-3 flex items-start gap-2 bg-success/10 border border-success/20 rounded-2xl p-3">
                            <svg class="w-5 h-5 text-success shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 12.75 11.25 15 15 9.75m-3-7.04A11.96 11.96 0 0 1 3.6 6 12 12 0 0 0 3 9.75c0 5.59 3.82 10.29 9 11.62 5.18-1.33 9-6.03 9-11.62 0-1.31-.21-2.57-.6-3.75h-.15c-3.2 0-6.1-1.25-8.25-3.29Z"/></svg>
                            <p class="text-xs font-semibold text-success">Beli {{ number_format($defaultQty, 0, ',', '.') }} {{ $product->unit }} hemat <span class="font-extrabold">Rp {{ number_format($displaySavings, 0, ',', '.') }}</span> dibanding harga eceran</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="px-4 mt-4">
                <div class="bg-forest/[0.06] border border-forest/15 rounded-2xl p-4 flex items-start gap-3">
                    <svg class="w-5 h-5 text-forest shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M11.25 11.25h1.5m-1.5 3h1.5m-1.5 3h1.5m-4.5-9.75h7.5M4.5 6.75h15a1.5 1.5 0 0 1 1.5 1.5v12a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 20.25v-12a1.5 1.5 0 0 1 1.5-1.5Z"/></svg>
                    <div>
                        <p class="text-sm font-bold">Pesan dari distributor pilihan</p>
                        <p class="text-xs text-muted mt-0.5">
                            Tersedia {{ count($offers) }} distributor. Atur jumlah pada kartu distributor di bawah, lalu tambah ke keranjang atau beli sekarang.
                            @if ($maxStockShown > 0)
                                Stok terbesar {{ number_format($maxStockShown, 0, ',', '.') }} {{ $product->unit }}.
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="px-4 mt-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-extrabold">Penawaran Distributor <span class="text-muted font-semibold">({{ count($offers) }})</span></h2>
                </div>
                <p class="text-[11px] text-muted mt-1">Diurutkan oleh sistem SPK (AHP &middot; TOPSIS &middot; SAW)</p>

                @forelse ($offers as $offer)
                    @php
                        $sp = $offer['supplier_product'];
                        $d = $offer['distributor'];
                        $isTop = ($offer['rank'] ?? null) === 1;
                        $pct = $sp->moq > 0 ? min(100, ($offer['pooled_quantity'] / (float) $sp->moq) * 100) : 100;
                        $remaining = max(0, (float) $sp->moq - $offer['pooled_quantity']);
                        $discountPct = $benchmark > 0 && (float) $offer['unit_price'] < $benchmark ? (int) round(($benchmark - (float) $offer['unit_price']) / $benchmark * 100) : 0;
                    @endphp
                    <article id="offer-m-{{ $d->id }}" class="mt-3 bg-white rounded-3xl {{ $isTop ? 'border-2 border-brass shadow-md' : 'border border-line' }} p-4">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-extrabold text-[15px] leading-tight line-clamp-2">{{ $d->company_name }}</p>
                            @if ($isTop)
                                <span class="bg-brass text-white text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wide whitespace-nowrap shrink-0">Rekomendasi SPK #1</span>
                            @elseif (! is_null($offer['rank']))
                                <span class="bg-cream border border-line text-muted text-[10px] font-bold px-2.5 py-1 rounded-full shrink-0">#{{ $offer['rank'] }}</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-1.5 mt-1.5 text-xs">
                            <svg class="w-3.5 h-3.5 text-brass" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                            <span class="font-bold">{{ number_format((float) $d->rating, 1) }}</span><span class="text-muted">&middot; estimasi tiba {{ $d->delivery_lead_time_days ?? 2 }} hari</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2 mt-3 text-center">
                            <div class="bg-cream rounded-2xl py-2"><p class="text-[10px] text-muted font-semibold">MOQ</p><p class="text-xs font-extrabold tnum">{{ number_format((float) $sp->moq, 0, ',', '.') }} {{ $product->unit }}</p></div>
                            <div class="bg-cream rounded-2xl py-2"><p class="text-[10px] text-muted font-semibold">Jarak</p><p class="text-xs font-extrabold tnum">{{ $offer['distance_km'] !== null ? number_format($offer['distance_km'], 1, ',', '.').' km' : '—' }}</p></div>
                            <div class="bg-cream rounded-2xl py-2"><p class="text-[10px] text-muted font-semibold">Stok</p><p class="text-xs font-extrabold tnum">{{ number_format((float) $sp->stock_available, 0, ',', '.') }}</p></div>
                        </div>
                        <div class="mt-3">
                            <div class="h-2.5 rounded-full bg-line overflow-hidden"><div class="h-full rounded-full {{ $isTop ? 'bg-gradient-to-r from-forest to-success' : 'bg-forest/60' }}" style="width: {{ $pct }}%"></div></div>
                            <p class="text-[11px] text-muted mt-1.5">
                                @if ($remaining > 0)
                                    Terkumpul <span class="font-bold text-ink tnum">{{ number_format($offer['pooled_quantity'], 0, ',', '.') }}/{{ number_format((float) $sp->moq, 0, ',', '.') }} {{ $product->unit }}</span> &mdash; kurang <span class="font-bold {{ $isTop ? 'text-brass' : 'text-ink' }} tnum">{{ number_format($remaining, 0, ',', '.') }} {{ $product->unit }}</span> lagi menuju minimum pemesanan
                                @else
                                    Kuota terpenuhi &mdash; pesanan diproses saat checkout
                                @endif
                            </p>
                        </div>
                        <div class="mt-3 pt-3 border-t border-line">
                            <label class="block text-[11px] uppercase tracking-wider font-bold text-muted mb-1.5">Jumlah Kebutuhan Anda ({{ $product->unit }})</label>
                            <div class="flex items-center justify-between gap-3 flex-wrap">
                                <div class="flex items-center gap-3 bg-cream border border-line rounded-2xl p-1.5">
                                    <button type="button" @click="dec({{ $d->id }})" class="w-9 h-9 rounded-xl bg-white border border-line flex items-center justify-center text-ink cursor-pointer">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14"/></svg>
                                    </button>
                                    <input type="number" x-model.number="quantities[{{ $d->id }}]" @change="normalizeQty({{ $d->id }})" @blur="normalizeQty({{ $d->id }})" min="1" step="1" :max="maxQty" aria-label="Jumlah kebutuhan" class="w-20 text-center font-extrabold text-base bg-transparent outline-none tnum">
                                    <button type="button" @click="inc({{ $d->id }})" class="w-9 h-9 rounded-xl bg-forest text-white flex items-center justify-center cursor-pointer">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                    </button>
                                </div>
                                 <div class="text-right shrink-0">
                                    <p class="text-[11px] text-muted">Harga <span class="font-semibold">(estimasi)</span></p>
                                    <p class="text-xl font-extrabold text-ink tnum whitespace-nowrap">Rp <span x-text="formatNumber(getPrice({{ $d->id }}))">{{ number_format($offer['unit_price'], 0, ',', '.') }}</span><span class="text-xs font-semibold text-muted">/{{ $product->unit }}</span></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 mt-2.5 flex-wrap text-[11px]">
                                <span class="text-muted font-bold">Pintasan:</span>
                                <button type="button" @click="addQty({{ $d->id }}, 10)" class="font-bold px-2 py-0.5 rounded-lg bg-white border border-line hover:border-forest text-ink transition cursor-pointer">+10</button>
                                <button type="button" @click="addQty({{ $d->id }}, 50)" class="font-bold px-2 py-0.5 rounded-lg bg-white border border-line hover:border-forest text-ink transition cursor-pointer">+50</button>
                                <button type="button" @click="addQty({{ $d->id }}, 100)" class="font-bold px-2 py-0.5 rounded-lg bg-white border border-line hover:border-forest text-ink transition cursor-pointer">+100</button>
                                <button type="button" @click="setDirectQty({{ $d->id }}, 100)" class="font-bold px-2 py-0.5 rounded-lg bg-forest/10 text-forest border border-forest/20 hover:bg-forest hover:text-white transition cursor-pointer">100 {{ $product->unit }}</button>
                                <button type="button" @click="setDirectQty({{ $d->id }}, 500)" class="font-bold px-2 py-0.5 rounded-lg bg-forest/10 text-forest border border-forest/20 hover:bg-forest hover:text-white transition cursor-pointer">500 {{ $product->unit }}</button>
                            </div>
                            <div class="flex items-center justify-between mt-2 text-[11px]">
                                <span class="text-muted">Subtotal kebutuhan</span>
                                <span class="font-extrabold text-ink tnum">Rp <span x-text="formatNumber(getSubtotal({{ $d->id }}))">{{ number_format($offer['unit_price'] * $defaultQty, 0, ',', '.') }}</span></span>
                            </div>
                            <div x-show="getSavings({{ $d->id }}) > 0" class="mt-2 p-2 rounded-xl bg-success/10 border border-success/20 flex items-center justify-between text-[11px]">
                                <span class="font-bold text-success">Estimasi hemat kolektif</span>
                                <span class="font-extrabold text-success tnum">~Rp <span x-text="formatNumber(getSavings({{ $d->id }}))"></span></span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2.5 mt-3">
                            <form method="POST" action="{{ route('umkm.cart.add') }}" class="shrink-0">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="distributor_id" value="{{ $d->id }}">
                                <input type="hidden" name="quantity" :value="getQty({{ $d->id }})">
                                <button type="submit" title="Tambah ke Keranjang" aria-label="Tambah ke Keranjang" class="w-12 h-12 rounded-2xl border-2 border-forest text-forest flex items-center justify-center cursor-pointer">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M2.25 3h1.39c.51 0 .96.34 1.09.84l.38 1.44M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.22c1.12-2.3 2.1-4.68 2.92-7.14a60.11 60.11 0 0 0-16.54-1.84M7.5 14.25 5.11 5.27M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('umkm.cart.buy-now') }}" class="flex-1">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="distributor_id" value="{{ $d->id }}">
                                <input type="hidden" name="quantity" :value="getQty({{ $d->id }})">
                                <button type="submit" class="w-full h-12 rounded-2xl bg-forest text-white text-sm font-bold shadow-md cursor-pointer">Beli Sekarang</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="mt-3 bg-white rounded-3xl border border-line p-8 text-center">
                        <p class="font-bold text-ink">Belum ada distributor aktif untuk produk ini.</p>
                        <a href="{{ route('umkm.catalog.index') }}" class="btn-ghost text-xs mt-3 inline-flex">Kembali ke Katalog</a>
                    </div>
                @endforelse
            </div>

            <div class="px-4 mt-6 pb-8">
                <h2 class="text-base font-extrabold">Bandingkan Penawaran</h2>
                @if (! empty($offers))
                    <div class="mt-3 overflow-x-auto no-scrollbar -mx-4 px-4">
                        <table class="w-[620px] text-xs bg-white border border-line rounded-3xl overflow-hidden">
                            <thead>
                                <tr class="bg-cream text-muted text-left">
                                    <th class="p-3 font-bold">Distributor</th><th class="p-3 font-bold">Harga</th><th class="p-3 font-bold">Diskon</th><th class="p-3 font-bold">Tiba</th><th class="p-3 font-bold">Rating</th><th class="p-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line/70">
                                @foreach ($offers as $offer)
                                    @php
                                        $d = $offer['distributor'];
                                        $isTop = ($offer['rank'] ?? null) === 1;
                                        $discountPct = $benchmark > 0 && (float) $offer['unit_price'] < $benchmark ? (int) round(($benchmark - (float) $offer['unit_price']) / $benchmark * 100) : 0;
                                    @endphp
                                    <tr class="{{ $isTop ? 'bg-brass-soft/40' : '' }}">
                                        <td class="p-3 font-bold">{{ $d->company_name }}</td>
                                        <td class="p-3 font-extrabold tnum">Rp {{ number_format($offer['unit_price'], 0, ',', '.') }}</td>
                                        <td class="p-3 tnum {{ $discountPct > 0 ? 'text-danger font-bold' : 'text-muted' }}">{{ $discountPct > 0 ? '-'.$discountPct.'%' : '—' }}</td>
                                        <td class="p-3">{{ $d->delivery_lead_time_days ?? 2 }} hari</td>
                                        <td class="p-3 font-bold tnum">{{ number_format((float) $d->rating, 1) }}</td>
                                        <td class="p-3"><a href="#offer-m-{{ $d->id }}" class="{{ $isTop ? 'bg-forest text-white' : 'border border-forest text-forest' }} text-[11px] font-bold px-3 py-1.5 rounded-lg inline-block">Pilih</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="text-[11px] text-muted mt-2">Geser tabel untuk melihat semua kolom.</p>
                @else
                    <p class="text-xs text-muted mt-2">Belum ada penawaran untuk dibandingkan.</p>
                @endif
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
                <x-umkm.sidebar-nav active="catalog" />
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
                            <h1 class="text-lg font-extrabold leading-tight">Detail Produk</h1>
                            <p class="text-xs text-muted">{{ $product->name }}</p>
                        </div>
                        <div class="ml-auto flex items-center gap-3">
                            <form method="GET" action="{{ route('umkm.catalog.index') }}" class="hidden lg:flex items-center gap-2 bg-white border border-line rounded-2xl pl-4 pr-2 py-2 w-72">
                                <svg class="w-4 h-4 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21 21-5.2-5.2m0 0A7.5 7.5 0 1 0 5.2 5.2a7.5 7.5 0 0 0 10.6 10.6Z"/></svg>
                                <input name="q" class="flex-1 bg-transparent text-sm outline-none placeholder:text-muted/70" placeholder="Cari pesanan, produk...">
                            </form>
                            <a href="{{ route('umkm.cart.index') }}" class="w-11 h-11 rounded-2xl bg-white border border-line flex items-center justify-center relative">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2.25 3h1.39c.51 0 .96.34 1.09.84l.38 1.44M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.22c1.12-2.3 2.1-4.68 2.92-7.14a60.11 60.11 0 0 0-16.54-1.84M7.5 14.25 5.11 5.27M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                                @if ($cartCount > 0)
                                    <span class="absolute -top-1.5 -right-1.5 min-w-[20px] h-5 px-1 rounded-full bg-brass text-white text-[11px] font-bold flex items-center justify-center">{{ $cartCount }}</span>
                                @endif
                            </a>
                            <span class="w-11 h-11 rounded-2xl bg-forest text-white flex items-center justify-center font-extrabold text-sm">{{ $initials }}</span>
                        </div>
                    </div>
                </header>

                <main class="px-8 py-6 max-w-6xl">
                    <nav class="flex items-center gap-2 text-xs text-muted">
                        <a href="{{ route('umkm.catalog.index') }}" class="hover:text-forest font-semibold">Katalog</a>
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                        <span class="font-semibold">{{ $product->category->name ?? 'Bahan Baku' }}</span>
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                        <span class="text-ink font-bold">{{ $product->name }}</span>
                    </nav>

                    <div class="grid lg:grid-cols-2 gap-8 mt-6">
                        <div>
                            <div class="relative h-[420px] rounded-[2rem] bg-forest/10 border border-line overflow-hidden flex items-center justify-center">
                                <span class="text-[12rem] font-extrabold text-forest/20 select-none">{{ $initial }}</span>
                                @if ($bestDiscount > 0)
                                    <span class="absolute top-5 left-5 bg-danger text-white text-xs font-bold px-3 py-1.5 rounded-full">-{{ $bestDiscount }}%</span>
                                @endif
                                <span class="absolute top-5 right-5 bg-white/85 backdrop-blur border border-line text-ink text-xs font-bold px-3 py-1.5 rounded-full">{{ $product->category->name ?? 'Bahan Baku' }}</span>
                            </div>
                            <div class="bg-white border border-line rounded-3xl p-6 mt-6">
                                <h3 class="font-extrabold">Deskripsi Produk</h3>
                                <p class="text-sm text-muted leading-relaxed mt-2">{{ $product->standard_specification ?? 'Belum ada deskripsi produk untuk bahan baku ini.' }}</p>
                                <div class="grid grid-cols-3 gap-3 mt-4 text-center">
                                    <div class="bg-cream rounded-2xl p-3"><p class="text-[11px] text-muted font-semibold">Satuan</p><p class="text-sm font-extrabold">per {{ $product->unit }}</p></div>
                                    <div class="bg-cream rounded-2xl p-3"><p class="text-[11px] text-muted font-semibold">Kategori</p><p class="text-sm font-extrabold">{{ $product->category->name ?? '—' }}</p></div>
                                    <div class="bg-cream rounded-2xl p-3"><p class="text-[11px] text-muted font-semibold">Patokan Eceran</p><p class="text-sm font-extrabold tnum">Rp {{ number_format($benchmark, 0, ',', '.') }}</p></div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center gap-2 text-xs text-muted">
                                <svg class="w-4 h-4 text-muted/40" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                <span>Rating produk belum tersedia</span>
                            </div>
                            <h1 class="text-3xl font-extrabold mt-2">{{ $product->name }}</h1>
                            <p class="text-sm text-muted mt-1">Satuan per {{ $product->unit }} &middot; {{ $product->category->name ?? 'Bahan Baku' }}</p>

                            <div class="bg-white border border-line rounded-3xl p-5 mt-5">
                                <p class="text-xs font-bold uppercase tracking-wider text-muted">Harga bertingkat <span class="normal-case font-medium">(estimasi)</span></p>
                                <div class="mt-3 space-y-2 text-sm">
                                    <div class="flex justify-between"><span class="text-muted">Eceran</span><span class="font-bold tnum">Rp {{ number_format($benchmark, 0, ',', '.') }}<span class="text-muted font-medium">/{{ $product->unit }}</span></span></div>
                                    @forelse ($displayTiers as $tier)
                                        @php
                                            $isActiveTier = $defaultQty >= (float) $tier->min_quantity && ($tier->max_quantity === null || $defaultQty <= (float) $tier->max_quantity);
                                        @endphp
                                        <div class="flex justify-between {{ $isActiveTier ? 'bg-brass-soft/60 -mx-2 px-2 py-2 rounded-xl' : '' }}">
                                            <span class="{{ $isActiveTier ? 'font-bold text-brass' : 'text-muted' }}">&ge; {{ number_format((float) $tier->min_quantity, 0, ',', '.') }} {{ $product->unit }}</span>
                                            <span class="{{ $isActiveTier ? 'font-extrabold text-brass' : 'font-bold' }} tnum">Rp {{ number_format((float) $tier->unit_price, 0, ',', '.') }}<span class="{{ $isActiveTier ? 'font-medium' : 'text-muted font-medium' }}">/{{ $product->unit }}</span></span>
                                        </div>
                                    @empty
                                        <p class="text-sm text-muted">Harga bertingkat grosir belum tersedia.</p>
                                    @endforelse
                                </div>
                                @if ($displaySavings > 0)
                                    <div class="mt-4 flex items-center gap-2.5 bg-success/10 border border-success/20 rounded-2xl p-3.5">
                                        <svg class="w-6 h-6 text-success shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 12.75 11.25 15 15 9.75m-3-7.04A11.96 11.96 0 0 1 3.6 6 12 12 0 0 0 3 9.75c0 5.59 3.82 10.29 9 11.62 5.18-1.33 9-6.03 9-11.62 0-1.31-.21-2.57-.6-3.75h-.15c-3.2 0-6.1-1.25-8.25-3.29Z"/></svg>
                                        <p class="text-sm font-semibold text-success">Beli {{ number_format($defaultQty, 0, ',', '.') }} {{ $product->unit }} hemat <span class="font-extrabold">Rp {{ number_format($displaySavings, 0, ',', '.') }}</span> dibanding harga eceran</p>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-4 flex items-start gap-2.5 bg-forest/[0.06] border border-forest/15 rounded-2xl p-4">
                                <svg class="w-5 h-5 text-forest shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M11.25 11.25h1.5m-1.5 3h1.5m-1.5 3h1.5m-4.5-9.75h7.5M4.5 6.75h15a1.5 1.5 0 0 1 1.5 1.5v12a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 20.25v-12a1.5 1.5 0 0 1 1.5-1.5Z"/></svg>
                                <p class="text-sm text-muted">Tentukan jumlah pesanan ({{ $product->unit }}) pada distributor pilihan di bawah. Harga &amp; subtotal otomatis menyesuaikan.</p>
                            </div>
                        </div>
                    </div>

                    <section class="mt-12">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-extrabold">Penawaran Distributor <span class="text-muted font-semibold">({{ count($offers) }})</span></h2>
                            <p class="text-xs text-muted">Diurutkan oleh sistem SPK (AHP &middot; TOPSIS &middot; SAW)</p>
                        </div>

                        @if (! empty($offers))
                            <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-5 mt-5">
                                @foreach ($offers as $offer)
                                    @php
                                        $sp = $offer['supplier_product'];
                                        $d = $offer['distributor'];
                                        $isTop = ($offer['rank'] ?? null) === 1;
                                        $pct = $sp->moq > 0 ? min(100, ($offer['pooled_quantity'] / (float) $sp->moq) * 100) : 100;
                                        $remaining = max(0, (float) $sp->moq - $offer['pooled_quantity']);
                                        $discountPct = $benchmark > 0 && (float) $offer['unit_price'] < $benchmark ? (int) round(($benchmark - (float) $offer['unit_price']) / $benchmark * 100) : 0;
                                    @endphp
                                    <article id="offer-d-{{ $d->id }}" class="bg-white rounded-3xl {{ $isTop ? 'border-2 border-brass shadow-md' : 'border border-line' }} p-6 flex flex-col">
                                        <div class="flex items-start justify-between gap-2 min-h-[3.5rem]">
                                            <p class="font-extrabold text-lg leading-tight line-clamp-2">{{ $d->company_name }}</p>
                                            @if ($isTop)
                                                <span class="bg-brass text-white text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wide whitespace-nowrap shrink-0">Rekomendasi SPK #1</span>
                                            @elseif (! is_null($offer['rank']))
                                                <span class="bg-cream border border-line text-muted text-[10px] font-bold px-2.5 py-1 rounded-full shrink-0">#{{ $offer['rank'] }}</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-1.5 mt-2 text-xs">
                                            <svg class="w-3.5 h-3.5 text-brass" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                            <span class="font-bold">{{ number_format((float) $d->rating, 1) }}</span><span class="text-muted">&middot; estimasi tiba {{ $d->delivery_lead_time_days ?? 2 }} hari</span>
                                        </div>
                                        <div class="grid grid-cols-3 gap-2 mt-4 text-center">
                                            <div class="bg-cream rounded-2xl py-2.5"><p class="text-[10px] text-muted font-semibold">MOQ</p><p class="text-sm font-extrabold tnum">{{ number_format((float) $sp->moq, 0, ',', '.') }} {{ $product->unit }}</p></div>
                                            <div class="bg-cream rounded-2xl py-2.5"><p class="text-[10px] text-muted font-semibold">Jarak</p><p class="text-sm font-extrabold tnum">{{ $offer['distance_km'] !== null ? number_format($offer['distance_km'], 1, ',', '.').' km' : '—' }}</p></div>
                                            <div class="bg-cream rounded-2xl py-2.5"><p class="text-[10px] text-muted font-semibold">Stok</p><p class="text-sm font-extrabold tnum">{{ number_format((float) $sp->stock_available, 0, ',', '.') }}</p></div>
                                        </div>
                                        <div class="mt-4">
                                            <div class="h-2.5 rounded-full bg-line overflow-hidden"><div class="h-full rounded-full {{ $isTop ? 'bg-gradient-to-r from-forest to-success' : 'bg-forest/60' }}" style="width: {{ $pct }}%"></div></div>
                                            <p class="text-[11px] text-muted mt-2">
                                                @if ($remaining > 0)
                                                    Terkumpul <span class="font-bold text-ink tnum">{{ number_format($offer['pooled_quantity'], 0, ',', '.') }}/{{ number_format((float) $sp->moq, 0, ',', '.') }} {{ $product->unit }}</span> &mdash; kurang <span class="font-bold {{ $isTop ? 'text-brass' : 'text-ink' }} tnum">{{ number_format($remaining, 0, ',', '.') }} {{ $product->unit }}</span> lagi menuju minimum pemesanan
                                                @else
                                                    Kuota terpenuhi &mdash; pesanan diproses saat checkout
                                                @endif
                                            </p>
                                        </div>

                                        <div class="mt-4 pt-4 border-t border-line">
                                            <label class="block text-[11px] uppercase tracking-wider font-bold text-muted mb-2">Jumlah Kebutuhan Anda ({{ $product->unit }})</label>
                                            <div class="flex items-center justify-between gap-3 flex-wrap">
                                                <div class="flex items-center gap-3 bg-cream border border-line rounded-2xl p-1.5">
                                                    <button type="button" @click="dec({{ $d->id }})" class="w-10 h-10 rounded-xl bg-white border border-line flex items-center justify-center text-ink cursor-pointer">
                                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14"/></svg>
                                                    </button>
                                                    <input type="number" x-model.number="quantities[{{ $d->id }}]" @change="normalizeQty({{ $d->id }})" @blur="normalizeQty({{ $d->id }})" min="1" step="1" :max="maxQty" aria-label="Jumlah kebutuhan" class="w-24 text-center font-extrabold text-lg bg-transparent outline-none tnum">
                                                    <button type="button" @click="inc({{ $d->id }})" class="w-10 h-10 rounded-xl bg-forest text-white flex items-center justify-center cursor-pointer">
                                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                                    </button>
                                                </div>
                                                <div class="text-right shrink-0">
                                                    <p class="text-[11px] text-muted">Harga <span class="font-semibold">(estimasi)</span></p>
                                                    <p class="text-xl font-extrabold text-ink tnum whitespace-nowrap">Rp <span x-text="formatNumber(getPrice({{ $d->id }}))">{{ number_format($offer['unit_price'], 0, ',', '.') }}</span><span class="text-xs font-semibold text-muted">/{{ $product->unit }}</span></p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-1.5 mt-2.5 flex-wrap text-[11px]">
                                                <span class="text-muted font-bold">Pintasan:</span>
                                                <button type="button" @click="addQty({{ $d->id }}, 10)" class="font-bold px-2 py-0.5 rounded-lg bg-white border border-line hover:border-forest text-ink transition cursor-pointer">+10</button>
                                                <button type="button" @click="addQty({{ $d->id }}, 50)" class="font-bold px-2 py-0.5 rounded-lg bg-white border border-line hover:border-forest text-ink transition cursor-pointer">+50</button>
                                                <button type="button" @click="addQty({{ $d->id }}, 100)" class="font-bold px-2 py-0.5 rounded-lg bg-white border border-line hover:border-forest text-ink transition cursor-pointer">+100</button>
                                                <button type="button" @click="setDirectQty({{ $d->id }}, 100)" class="font-bold px-2 py-0.5 rounded-lg bg-forest/10 text-forest border border-forest/20 hover:bg-forest hover:text-white transition cursor-pointer">100 {{ $product->unit }}</button>
                                                <button type="button" @click="setDirectQty({{ $d->id }}, 500)" class="font-bold px-2 py-0.5 rounded-lg bg-forest/10 text-forest border border-forest/20 hover:bg-forest hover:text-white transition cursor-pointer">500 {{ $product->unit }}</button>
                                            </div>
                                            <div class="flex items-center justify-between mt-2 text-[11px]">
                                                <span class="text-muted">Subtotal kebutuhan</span>
                                                <span class="font-extrabold text-ink tnum">Rp <span x-text="formatNumber(getSubtotal({{ $d->id }}))">{{ number_format($offer['unit_price'] * $defaultQty, 0, ',', '.') }}</span></span>
                                            </div>
                                            <div x-show="getSavings({{ $d->id }}) > 0" class="mt-2 p-2 rounded-xl bg-success/10 border border-success/20 flex items-center justify-between text-[11px]">
                                                <span class="font-bold text-success">Estimasi hemat kolektif</span>
                                                <span class="font-extrabold text-success tnum">~Rp <span x-text="formatNumber(getSavings({{ $d->id }}))"></span></span>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-3 mt-auto pt-4">
                                            <form method="POST" action="{{ route('umkm.cart.add') }}" class="shrink-0">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                <input type="hidden" name="distributor_id" value="{{ $d->id }}">
                                                <input type="hidden" name="quantity" :value="getQty({{ $d->id }})">
                                                <button type="submit" title="Tambah ke Keranjang" aria-label="Tambah ke Keranjang" class="w-12 h-12 rounded-2xl border-2 border-forest text-forest flex items-center justify-center cursor-pointer hover:bg-forest/5 transition">
                                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M2.25 3h1.39c.51 0 .96.34 1.09.84l.38 1.44M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.22c1.12-2.3 2.1-4.68 2.92-7.14a60.11 60.11 0 0 0-16.54-1.84M7.5 14.25 5.11 5.27M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('umkm.cart.buy-now') }}" class="flex-1">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                <input type="hidden" name="distributor_id" value="{{ $d->id }}">
                                                <input type="hidden" name="quantity" :value="getQty({{ $d->id }})">
                                                <button type="submit" class="w-full py-3.5 rounded-2xl bg-forest text-white font-bold shadow-md cursor-pointer">Beli Sekarang</button>
                                            </form>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @else
                            <div class="bg-white border border-line rounded-3xl p-8 text-center mt-5">
                                <p class="font-bold text-ink">Belum ada distributor aktif untuk produk ini.</p>
                                <a href="{{ route('umkm.catalog.index') }}" class="btn-ghost text-xs mt-3 inline-flex">Kembali ke Katalog</a>
                            </div>
                        @endif
                    </section>

                    <section class="mt-10">
                        <h2 class="text-xl font-extrabold">Bandingkan Semua Penawaran</h2>
                        @if (! empty($offers))
                            <div class="bg-white border border-line rounded-3xl overflow-hidden mt-4">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-cream text-muted text-left text-xs uppercase tracking-wide">
                                            <th class="p-4 font-bold">Distributor</th><th class="p-4 font-bold">Harga</th><th class="p-4 font-bold">Diskon</th><th class="p-4 font-bold">Estimasi Kirim</th><th class="p-4 font-bold">Rating</th><th class="p-4"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-line/70">
                                        @foreach ($offers as $offer)
                                            @php
                                                $d = $offer['distributor'];
                                                $isTop = ($offer['rank'] ?? null) === 1;
                                                $discountPct = $benchmark > 0 && (float) $offer['unit_price'] < $benchmark ? (int) round(($benchmark - (float) $offer['unit_price']) / $benchmark * 100) : 0;
                                            @endphp
                                            <tr class="{{ $isTop ? 'bg-brass-soft/40' : '' }}">
                                                <td class="p-4"><span class="font-bold">{{ $d->company_name }}</span> @if ($isTop)<span class="ml-2 bg-brass text-white text-[10px] font-bold px-2 py-0.5 rounded-full">SPK #1</span>@endif</td>
                                                <td class="p-4 font-extrabold tnum">Rp {{ number_format($offer['unit_price'], 0, ',', '.') }}</td>
                                                <td class="p-4 tnum {{ $discountPct > 0 ? 'text-danger font-bold' : 'text-muted' }}">{{ $discountPct > 0 ? '-'.$discountPct.'%' : '—' }}</td>
                                                <td class="p-4">{{ $d->delivery_lead_time_days ?? 2 }} hari</td>
                                                <td class="p-4 font-bold tnum">{{ number_format((float) $d->rating, 1) }}</td>
                                                <td class="p-4 text-right"><a href="#offer-d-{{ $d->id }}" class="{{ $isTop ? 'bg-forest text-white' : 'border-2 border-forest text-forest' }} text-xs font-bold px-5 py-2.5 rounded-xl inline-block">Pilih</a></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <p class="text-xs text-muted mt-3">Harga estimasi &mdash; harga final mengikuti total quantity kelompok kulaan.</p>
                        @else
                            <p class="text-sm text-muted mt-3">Belum ada penawaran untuk dibandingkan.</p>
                        @endif
                    </section>
                </main>
            </div>
        </div>

        <x-umkm.mobile-tab active="catalog" />
    </div>

    <script>
        function marketplaceDetail(config) {
            return {
                benchmark: config.benchmark,
                unit: config.unit,
                maxQty: config.maxQty,
                tiersData: config.tiersData,
                quantities: config.quantities || {},

                getQty(distId) {
                    const val = parseInt(this.quantities[distId], 10);
                    return isNaN(val) || val < 1 ? 1 : (this.maxQty > 0 ? Math.min(val, this.maxQty) : val);
                },
                setQty(distId, val) {
                    let parsed = parseInt(val, 10);
                    if (isNaN(parsed) || parsed < 1) parsed = 1;
                    if (this.maxQty > 0 && parsed > this.maxQty) parsed = this.maxQty;
                    this.quantities[distId] = parsed;
                },
                inc(distId, step = 1) {
                    this.setQty(distId, this.getQty(distId) + step);
                },
                dec(distId, step = 1) {
                    this.setQty(distId, this.getQty(distId) - step);
                },
                addQty(distId, amount) {
                    this.setQty(distId, this.getQty(distId) + amount);
                },
                setDirectQty(distId, amount) {
                    this.setQty(distId, amount);
                },
                normalizeQty(distId) {
                    this.setQty(distId, this.quantities[distId]);
                },
                getPrice(distId) {
                    const data = this.tiersData[distId];
                    if (!data) return 0;
                    const q = this.getQty(distId);
                    let bestPrice = data.base;
                    if (Array.isArray(data.tiers)) {
                        for (const t of data.tiers) {
                            if (q >= t.min && (t.max === null || q <= t.max)) {
                                return t.price;
                            }
                        }
                        const last = data.tiers[data.tiers.length - 1];
                        if (last && q >= last.min) {
                            return last.price;
                        }
                    }
                    return bestPrice;
                },
                getSubtotal(distId) {
                    return this.getPrice(distId) * this.getQty(distId);
                },
                getSavings(distId) {
                    const q = this.getQty(distId);
                    const retailTotal = this.benchmark * q;
                    const actualTotal = this.getSubtotal(distId);
                    return Math.max(0, retailTotal - actualTotal);
                },
                formatNumber(val) {
                    return Math.round(val || 0).toLocaleString('id-ID');
                }
            };
        }
    </script>
</x-layouts.app>
