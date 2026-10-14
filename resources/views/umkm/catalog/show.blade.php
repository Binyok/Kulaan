<x-layouts.app>
    <x-slot name="title">{{ $product->name }} — Penawaran Distributor</x-slot>

    <div class="-mx-3 sm:-mx-6 lg:-mx-8 -mt-6 sm:-mt-8 mb-6 sm:mb-8">
        <div class="texture-grain bg-brand-forest text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
                <div class="max-w-2xl" data-reveal>
                    <a href="{{ route('umkm.catalog.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-white/70 hover:text-white transition-colors">
                        &larr; Kembali ke Katalog
                    </a>
                    <h1 class="font-display font-semibold tracking-tight text-balance text-4xl sm:text-5xl mt-4">
                        {{ $product->name }}
                    </h1>
                    <div class="flex flex-wrap items-center gap-2 mt-3">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[0.625rem] font-semibold bg-white/10 text-[#D8E8DD] uppercase tracking-[0.14em]">
                            {{ $product->category->name ?? 'Tanpa kategori' }}
                        </span>
                        <span class="text-xs text-white/70">per {{ $product->unit }} &bull; Patokan eceran Rp {{ number_format($product->benchmark_retail_price, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $maxStock = 9999;
        if (! empty($offers)) {
            $stocks = array_map(fn($o) => (float) $o['supplier_product']->stock_available, $offers);
            $maxStock = max($stocks) > 0 ? max($stocks) : 9999;
        }

        // Prepare tiers data for JS live calculation
        $tiersDataByDistributor = [];
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
        }
    @endphp

    <div class="space-y-6 sm:space-y-8"
         x-data="catalogDetail({
             initialQty: {{ $quantity }},
             benchmark: {{ (float) $product->benchmark_retail_price }},
             unit: '{{ $product->unit }}',
             minQty: 0.1,
             stepQty: 0.5,
             maxQty: {{ $maxStock }},
             tiersData: {{ Js::from($tiersDataByDistributor) }}
         })">

        <section class="bg-white rounded-[20px] border border-brand-line p-5 sm:p-7 shadow-sm" data-reveal>
            @php
                $topOffer = $offers[0] ?? null;
            @endphp
            @if ($topOffer && $topOffer['supplier_product']->priceTiers->isNotEmpty())
                <div class="mb-4 pb-4 border-b border-brand-line">
                    <p class="text-[0.6875rem] font-bold uppercase tracking-wider text-brand-slate">Skema Harga Distributor Rekomendasi (Estimasi)</p>
                    <p class="text-xs text-brand-dark mt-1 font-medium">
                        Estimasi eceran Rp {{ number_format($topOffer['supplier_product']->base_retail_price, 0, ',', '.') }}/{{ $product->unit }}
                        @foreach ($topOffer['supplier_product']->priceTiers as $pt)
                            &middot; &ge;{{ number_format($pt->min_quantity, 0) }} {{ $product->unit }} Rp {{ number_format($pt->unit_price, 0, ',', '.') }}/{{ $product->unit }}
                        @endforeach
                    </p>
                </div>
            @endif

            <form id="recalcForm" method="GET" action="{{ route('umkm.catalog.show', $product) }}" class="flex flex-col sm:flex-row sm:items-end gap-4">
                <div class="flex-1">
                    <label for="quantityInput" class="field-label">Jumlah kebutuhan ({{ $product->unit }})</label>
                    <div class="flex items-center gap-2 mt-1">
                        <button type="button" @click="dec()" class="w-11 h-11 rounded-xl border border-brand-line bg-brand-cream hover:bg-black/[0.04] text-brand-dark font-bold text-lg flex items-center justify-center transition-colors">
                            &minus;
                        </button>
                        <input type="number"
                               id="quantityInput"
                               name="quantity"
                               x-model.number="qty"
                               @input="onQtyChange()"
                               :min="minQty"
                               :step="stepQty"
                               :max="maxQty"
                               required
                               class="input-field text-center font-bold text-lg max-w-[8rem] tabular-nums">
                        <button type="button" @click="inc()" class="w-11 h-11 rounded-xl border border-brand-line bg-brand-cream hover:bg-black/[0.04] text-brand-dark font-bold text-lg flex items-center justify-center transition-colors">
                            &plus;
                        </button>
                        <span class="text-sm font-semibold text-brand-slate ml-2">{{ $product->unit }}</span>
                    </div>
                </div>
                <button type="submit" class="btn-ghost text-sm px-6 shrink-0 min-h-11">
                    Hitung Ulang Rekomendasi
                </button>
            </form>

            <div class="mt-4 p-3 rounded-xl bg-brand-mint text-brand-primary text-xs font-semibold flex items-center justify-between flex-wrap gap-2"
                 x-show="savingsAmount > 0">
                <span x-text="savingsText"></span>
                <span class="text-[0.6875rem] font-normal text-brand-dark/70">vs patokan eceran</span>
            </div>

            <p class="text-[0.6875rem] text-brand-slate mt-3 leading-relaxed">
                <strong>Disclaimer wajib:</strong> Semua harga tier berlabel <em>Estimasi</em>. Harga final mengikuti total quantity kelompok kulaan — bisa lebih murah dari estimasi ini. Peringkat rekomendasi dihitung ulang via form di atas.
            </p>
        </section>

        @if (! empty($offers))
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4" data-reveal>
                @foreach ($offers as $offer)
                    @php
                        $sp = $offer['supplier_product'];
                        $d = $offer['distributor'];
                        $isTop = ($offer['rank'] ?? null) === 1;
                        $pct = $sp->moq > 0 ? min(100, ($offer['pooled_quantity'] / (float) $sp->moq) * 100) : 100;
                        $remaining = max(0, (float) $sp->moq - $offer['pooled_quantity']);
                    @endphp
                    <article class="bg-white rounded-2xl border p-5 sm:p-6 shadow-sm flex flex-col justify-between {{ $isTop ? 'ring-2 ring-brand-accent border-brand-accent/40 shadow-md' : 'border-brand-line' }}">
                        <div>
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-bold text-brand-dark text-base">{{ $d->company_name }}</p>
                                    @if ($isTop)
                                        <p class="text-[0.6875rem] text-brand-accent font-bold mt-0.5">
                                            &#9733; {{ number_format((float) $d->rating, 1) }} — rating distributor rekomendasi
                                        </p>
                                    @endif
                                </div>
                                @if ($isTop)
                                    <span class="badge bg-brand-ochre-soft text-brand-brass border border-brand-accent/30 shrink-0">Rekomendasi #1 Sistem</span>
                                @elseif (! empty($offer['rank']))
                                    <span class="badge bg-brand-cream text-brand-slate shrink-0">Peringkat #{{ $offer['rank'] }}</span>
                                @endif
                            </div>

                            <p class="text-xs text-brand-slate mt-2">
                                <span class="font-bold text-brand-accent">&#9733; {{ number_format((float) $d->rating, 1) }}</span>
                                <span class="mx-1">&bull;</span> Estimasi tiba {{ $d->delivery_lead_time_days ?? '-' }} hari
                            </p>

                            <dl class="grid grid-cols-3 gap-2 mt-4 text-xs">
                                <div class="p-2.5 bg-brand-cream/70 rounded-xl">
                                    <dt class="text-[0.6875rem] text-brand-slate">Jarak</dt>
                                    <dd class="font-bold text-brand-dark mt-0.5 tabular-nums">{{ $offer['distance_km'] !== null ? number_format($offer['distance_km'], 1).' km' : '±5 km' }}</dd>
                                </div>
                                <div class="p-2.5 bg-brand-cream/70 rounded-xl">
                                    <dt class="text-[0.6875rem] text-brand-slate">MOQ</dt>
                                    <dd class="font-bold text-brand-dark mt-0.5 tabular-nums">{{ number_format($sp->moq, 0) }} {{ $product->unit }}</dd>
                                </div>
                                <div class="p-2.5 bg-brand-cream/70 rounded-xl">
                                    <dt class="text-[0.6875rem] text-brand-slate">Stok</dt>
                                    <dd class="font-bold text-brand-dark mt-0.5 tabular-nums">{{ number_format($sp->stock_available, 0) }} {{ $product->unit }}</dd>
                                </div>
                            </dl>

                            <div class="mt-4">
                                <p class="text-[0.6875rem] text-brand-slate">
                                    Terkumpul {{ number_format($offer['pooled_quantity'], 1) }}/{{ number_format($sp->moq, 0) }} {{ $product->unit }} &mdash; kurang {{ number_format($remaining, 1) }} {{ $product->unit }} lagi menuju minimum pemesanan (MOQ)
                                </p>
                                <div class="h-2 rounded-full bg-brand-cream mt-1.5 overflow-hidden">
                                    <div class="h-full bg-brand-emerald rounded-full transition-all" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-t border-brand-line">
                                <p class="text-[0.6875rem] uppercase tracking-wider text-brand-slate font-bold">Harga Satuan (Estimasi)</p>
                                <div class="flex items-baseline gap-2 mt-0.5">
                                    <p class="font-extrabold text-brand-primary text-2xl tabular-nums">
                                        Rp <span x-text="formatNumber(getPrice({{ $d->id }}))">{{ number_format($offer['unit_price'], 0, ',', '.') }}</span>
                                    </p>
                                    <span class="text-xs text-brand-slate">/{{ $product->unit }}</span>
                                </div>
                                <p class="text-[0.6875rem] text-brand-slate mt-0.5">
                                    Subtotal: Rp <span x-text="formatNumber(getPrice({{ $d->id }}) * qty)">{{ number_format($offer['unit_price'] * $quantity, 0, ',', '.') }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-5">
                            <form method="POST" action="{{ route('umkm.cart.add') }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="distributor_id" value="{{ $d->id }}">
                                <input type="hidden" name="quantity" :value="qty">
                                <button type="submit" class="btn-ghost w-full justify-center py-2.5 text-xs font-bold min-h-11">
                                    + Keranjang
                                </button>
                            </form>

                            <form method="POST" action="{{ route('umkm.cart.buy-now') }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="distributor_id" value="{{ $d->id }}">
                                <input type="hidden" name="quantity" :value="qty">
                                <button type="submit" class="btn-primary w-full justify-center py-2.5 text-xs font-bold min-h-11">
                                    Beli Sekarang &rarr;
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>

            <section class="bg-white rounded-[20px] border border-brand-line p-5 sm:p-7 overflow-x-auto shadow-sm" data-reveal>
                <h2 class="text-sm font-bold text-brand-dark mb-3">Perbandingan Ringkas Distributor</h2>
                <table class="table-premium min-w-[560px]">
                    <thead>
                        <tr>
                            <th>Distributor</th>
                            <th class="text-right">Harga Satuan (Estimasi)</th>
                            <th class="text-right">Rating</th>
                            <th class="text-right">Jarak</th>
                            <th class="text-right">MOQ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($offers as $offer)
                            <tr>
                                <td class="font-bold text-brand-dark">{{ $offer['distributor']->company_name }}</td>
                                <td class="text-right tabular-nums">Rp {{ number_format($offer['unit_price'], 0, ',', '.') }}</td>
                                <td class="text-right tabular-nums">&#9733; {{ number_format((float) $offer['distributor']->rating, 1) }}</td>
                                <td class="text-right tabular-nums">{{ $offer['distance_km'] !== null ? number_format($offer['distance_km'], 1).' km' : '-' }}</td>
                                <td class="text-right tabular-nums">{{ number_format($offer['supplier_product']->moq, 0) }} {{ $product->unit }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @else
            <section class="bg-white rounded-[20px] border border-brand-line px-6 py-12 text-center" data-reveal>
                <p class="font-bold text-brand-dark">Belum ada distributor untuk jumlah ini.</p>
                <p class="text-[0.8125rem] text-brand-slate mt-1.5">Coba naikkan jumlah kebutuhan atau kembali lagi nanti.</p>
                <a href="{{ route('umkm.catalog.index') }}" class="btn-ghost text-sm mt-5">Kembali ke Katalog</a>
            </section>
        @endif
    </div>

    <script>
        function catalogDetail(config) {
            return {
                qty: config.initialQty,
                benchmark: config.benchmark,
                unit: config.unit,
                minQty: config.minQty,
                stepQty: config.stepQty,
                maxQty: config.maxQty,
                tiersData: config.tiersData,
                debounceTimer: null,

                inc() {
                    const next = Math.round((this.qty + this.stepQty) * 10) / 10;
                    if (next <= this.maxQty) {
                        this.qty = next;
                        this.triggerDebounceRecalc();
                    }
                },
                dec() {
                    const next = Math.round((this.qty - this.stepQty) * 10) / 10;
                    if (next >= this.minQty) {
                        this.qty = next;
                        this.triggerDebounceRecalc();
                    }
                },
                onQtyChange() {
                    if (this.qty < this.minQty) this.qty = this.minQty;
                    if (this.qty > this.maxQty) this.qty = this.maxQty;
                    this.triggerDebounceRecalc();
                },
                triggerDebounceRecalc() {
                    clearTimeout(this.debounceTimer);
                    this.debounceTimer = setTimeout(() => {
                        const form = document.getElementById('recalcForm');
                        if (form) form.submit();
                    }, 500);
                },
                getPrice(distId) {
                    const data = this.tiersData[distId];
                    if (!data) return 0;
                    const q = parseFloat(this.qty) || 0;
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
                get savingsAmount() {
                    const firstDistId = Object.keys(this.tiersData)[0];
                    if (!firstDistId) return 0;
                    const price = this.getPrice(firstDistId);
                    const q = parseFloat(this.qty) || 0;
                    const retailTotal = this.benchmark * q;
                    const actualTotal = price * q;
                    return Math.max(0, retailTotal - actualTotal);
                },
                get savingsText() {
                    return `Beli ${this.qty} ${this.unit} hemat Rp ${this.formatNumber(this.savingsAmount)} dibanding eceran`;
                },
                formatNumber(val) {
                    return Math.round(val || 0).toLocaleString('id-ID');
                }
            };
        }
    </script>
</x-layouts.app>
