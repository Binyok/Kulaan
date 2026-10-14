<x-layouts.app>
    <x-slot name="title">{{ $product->name }} — Detail & Penawaran Distributor</x-slot>

    @php
        $maxStock = 9999;
        if (! empty($offers)) {
            $stocks = array_map(fn($o) => (float) $o['supplier_product']->stock_available, $offers);
            $maxStock = max($stocks) > 0 ? max($stocks) : 9999;
        }

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

        $defaultQty = max(1, (int) round($quantity));
    @endphp

    <div x-data="marketplaceDetail({
            qty: {{ $defaultQty }},
            benchmark: {{ (float) $product->benchmark_retail_price }},
            unit: '{{ $product->unit }}',
            maxQty: {{ $maxStock }},
            tiersData: {{ Js::from($tiersDataByDistributor) }}
         })" class="space-y-6">

        <div class="flex items-center gap-2 text-xs text-brand-slate">
            <a href="{{ route('umkm.catalog.index') }}" class="hover:text-brand-primary font-semibold flex items-center gap-1">
                &larr; Katalog Bahan Baku
            </a>
            <span>/</span>
            <span class="text-brand-dark font-medium truncate max-w-xs">{{ $product->name }}</span>
        </div>

        <div class="bg-white rounded-2xl border border-brand-line p-5 sm:p-7 shadow-sm grid grid-cols-1 md:grid-cols-12 gap-6">
            <div class="md:col-span-4 flex flex-col items-center justify-center bg-gradient-to-br from-brand-mint/50 to-brand-cream rounded-xl p-8 border border-brand-line/50 aspect-square text-center">
                <span class="font-display font-extrabold text-7xl text-brand-primary/30">
                    {{ strtoupper(substr($product->name, 0, 1)) }}
                </span>
                <span class="mt-4 px-3 py-1 rounded-full text-xs font-bold bg-white text-brand-dark shadow-xs border border-brand-line">
                    {{ $product->category->name ?? 'Bahan Baku' }}
                </span>
                <span class="text-[0.6875rem] text-brand-slate mt-2">Satuan: per {{ $product->unit }}</span>
            </div>

            <div class="md:col-span-8 flex flex-col justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="badge bg-brand-cream text-brand-slate">
                            Kulaan Kolektif
                        </span>
                        <span class="text-xs text-brand-slate">
                            Patokan eceran: <strong class="text-brand-dark">Rp {{ number_format($product->benchmark_retail_price, 0, ',', '.') }}/{{ $product->unit }}</strong>
                        </span>
                    </div>

                    <h1 class="font-display font-bold text-2xl sm:text-3xl text-brand-dark mt-2 tracking-tight leading-snug">
                        {{ $product->name }}
                    </h1>

                    @if ($product->standard_specification)
                        <p class="text-xs sm:text-sm text-brand-slate mt-2 leading-relaxed">
                            {{ $product->standard_specification }}
                        </p>
                    @endif

                    <div class="mt-6 pt-5 border-t border-brand-line">
                        <label class="block text-xs font-bold text-brand-dark uppercase tracking-wider">
                            Tentukan Kebutuhan Anda ({{ $product->unit }})
                        </label>
                        <p class="text-[0.6875rem] text-brand-slate mt-0.5">
                            Beli berapa pun tetap bisa pesan — kuota akan diurunkan bersama UMKM lain sampai tembus grosir.
                        </p>

                        <div class="flex items-center gap-3 mt-3">
                            <button type="button"
                                    @click="dec()"
                                    class="w-10 h-10 rounded-xl border border-brand-line bg-brand-cream hover:bg-black/[0.04] text-brand-dark font-extrabold text-lg flex items-center justify-center transition-colors">
                                &minus;
                            </button>
                            <input type="number"
                                   x-model.number="qty"
                                   @input="normalizeQty()"
                                   min="1"
                                   step="1"
                                   :max="maxQty"
                                   class="w-24 text-center font-bold text-lg rounded-xl border border-brand-line py-2 tabular-nums focus:ring-2 focus:ring-brand-primary">
                            <button type="button"
                                    @click="inc()"
                                    class="w-10 h-10 rounded-xl border border-brand-line bg-brand-cream hover:bg-black/[0.04] text-brand-dark font-extrabold text-lg flex items-center justify-center transition-colors">
                                &plus;
                            </button>
                            <span class="text-sm font-bold text-brand-slate">{{ $product->unit }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 p-4 rounded-xl bg-brand-mint/80 border border-brand-primary/20 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <span class="text-[0.6875rem] font-bold uppercase tracking-wider text-brand-primary block">Estimasi Keuntungan Kulaan</span>
                        <p class="text-xs sm:text-sm font-extrabold text-brand-primary mt-0.5" x-text="savingsText"></p>
                    </div>
                    <span class="text-[0.6875rem] text-brand-slate">
                        Harga final dihitung saat grup kulaan terpenuhi
                    </span>
                </div>
            </div>
        </div>

        <div>
            <div class="flex items-baseline justify-between mb-3">
                <div>
                    <h2 class="font-display font-bold text-lg sm:text-xl text-brand-dark">
                        Pilihan Distributor Penyedia ({{ count($offers) }})
                    </h2>
                    <p class="text-xs text-brand-slate">
                        Pilih distributor yang Anda inginkan. Harga dan kuota di bawah otomatis menyesuaikan <strong class="text-brand-dark" x-text="qty + ' ' + unit"></strong>.
                    </p>
                </div>
            </div>

            @if (! empty($offers))
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($offers as $offer)
                        @php
                            $sp = $offer['supplier_product'];
                            $d = $offer['distributor'];
                            $isTop = ($offer['rank'] ?? null) === 1;
                            $pct = $sp->moq > 0 ? min(100, ($offer['pooled_quantity'] / (float) $sp->moq) * 100) : 100;
                            $remaining = max(0, (float) $sp->moq - $offer['pooled_quantity']);
                        @endphp
                        <article class="bg-white rounded-2xl border p-5 sm:p-6 shadow-sm flex flex-col justify-between transition-all {{ $isTop ? 'border-brand-accent/50 ring-2 ring-brand-accent/20' : 'border-brand-line' }}">
                            <div>
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="font-bold text-brand-dark text-base leading-tight">
                                            {{ $d->company_name }}
                                        </h3>
                                        <p class="text-[0.6875rem] text-brand-slate mt-1 flex items-center gap-1.5">
                                            <span class="font-bold text-brand-brass">&#9733; {{ number_format((float) $d->rating, 1) }}</span>
                                            <span>&bull;</span>
                                            <span>Kirim &plusmn;{{ $d->delivery_lead_time_days ?? 2 }} hari</span>
                                            <span>&bull;</span>
                                            <span>Jarak {{ $offer['distance_km'] !== null ? number_format($offer['distance_km'], 1).' km' : '±5 km' }}</span>
                                        </p>
                                    </div>
                                    @if ($isTop)
                                        <span class="px-2.5 py-1 rounded-full text-[0.625rem] font-bold bg-brand-ochre-soft text-brand-brass border border-brand-accent/30 shrink-0">
                                            Rekomendasi #1
                                        </span>
                                    @endif
                                </div>

                                <div class="mt-4 p-3 rounded-xl bg-brand-cream/60 border border-brand-line/60">
                                    <div class="flex items-baseline justify-between text-[0.6875rem]">
                                        <span class="text-brand-slate">Kuota Urunan Terkumpul:</span>
                                        <span class="font-bold text-brand-dark tabular-nums">
                                            {{ number_format($offer['pooled_quantity'], 0) }} / {{ number_format($sp->moq, 0) }} {{ $product->unit }}
                                        </span>
                                    </div>
                                    <div class="h-2 rounded-full bg-brand-line/50 mt-1.5 overflow-hidden">
                                        <div class="h-full bg-brand-emerald rounded-full transition-all" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <p class="text-[0.625rem] text-brand-slate mt-1">
                                        @if ($remaining > 0)
                                            Kurang {{ number_format($remaining, 0) }} {{ $product->unit }} lagi untuk pesanan kolektif dikirim distributor
                                        @else
                                            Kuota terpenuhi! Pesanan langsung diproses saat checkout.
                                        @endif
                                    </p>
                                </div>

                                <div class="mt-4 pt-3 border-t border-brand-line/70 flex items-baseline justify-between">
                                    <div>
                                        <span class="text-[0.625rem] uppercase tracking-wider text-brand-slate font-medium block">
                                            Harga Satuan Tier (Estimasi)
                                        </span>
                                        <div class="flex items-baseline gap-1 mt-0.5">
                                            <span class="text-xl sm:text-2xl font-extrabold text-brand-primary tabular-nums">
                                                Rp <span x-text="formatNumber(getPrice({{ $d->id }}))">{{ number_format($offer['unit_price'], 0, ',', '.') }}</span>
                                            </span>
                                            <span class="text-xs text-brand-slate">/{{ $product->unit }}</span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-[0.625rem] text-brand-slate block">Subtotal Kebutuhan</span>
                                        <span class="font-extrabold text-brand-dark text-sm sm:text-base tabular-nums">
                                            Rp <span x-text="formatNumber(getPrice({{ $d->id }}) * qty)">{{ number_format($offer['unit_price'] * $defaultQty, 0, ',', '.') }}</span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2 mt-5 pt-3 border-t border-brand-line/60">
                                <form method="POST" action="{{ route('umkm.cart.add') }}">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="distributor_id" value="{{ $d->id }}">
                                    <input type="hidden" name="quantity" :value="qty">
                                    <button type="submit" class="w-full py-2.5 px-3 rounded-xl border border-brand-line hover:border-brand-primary/50 bg-white hover:bg-brand-mint text-brand-primary font-bold text-xs transition-colors flex items-center justify-center gap-1.5 min-h-10">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        + Keranjang
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('umkm.cart.buy-now') }}">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="distributor_id" value="{{ $d->id }}">
                                    <input type="hidden" name="quantity" :value="qty">
                                    <button type="submit" class="w-full py-2.5 px-3 rounded-xl bg-brand-primary hover:bg-brand-primary-hover text-white font-bold text-xs transition-colors shadow-sm flex items-center justify-center gap-1 min-h-10">
                                        Beli Sekarang &rarr;
                                    </button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-2xl border border-brand-line p-8 text-center">
                    <p class="font-bold text-brand-dark">Belum ada distributor aktif untuk produk ini.</p>
                    <a href="{{ route('umkm.catalog.index') }}" class="btn-ghost text-xs mt-3 inline-flex">Kembali ke Katalog</a>
                </div>
            @endif
        </div>
    </div>

    <script>
        function marketplaceDetail(config) {
            return {
                qty: config.qty,
                benchmark: config.benchmark,
                unit: config.unit,
                maxQty: config.maxQty,
                tiersData: config.tiersData,

                inc() {
                    if (this.qty < this.maxQty) {
                        this.qty = Math.round(this.qty + 1);
                    }
                },
                dec() {
                    if (this.qty > 1) {
                        this.qty = Math.round(this.qty - 1);
                    }
                },
                normalizeQty() {
                    if (!this.qty || this.qty < 1) {
                        this.qty = 1;
                    }
                    if (this.qty > this.maxQty) {
                        this.qty = this.maxQty;
                    }
                },
                getPrice(distId) {
                    const data = this.tiersData[distId];
                    if (!data) return 0;
                    const q = parseFloat(this.qty) || 1;
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
                    const q = parseFloat(this.qty) || 1;
                    const retailTotal = this.benchmark * q;
                    const actualTotal = price * q;
                    return Math.max(0, retailTotal - actualTotal);
                },
                get savingsText() {
                    if (this.savingsAmount <= 0) {
                        return `Pesan ${this.qty} ${this.unit} — gabung kuota untuk harga grosir termurah`;
                    }
                    return `Hemat ~Rp ${this.formatNumber(this.savingsAmount)} dibanding harga eceran biasa (${this.qty} ${this.unit})`;
                },
                formatNumber(val) {
                    return Math.round(val || 0).toLocaleString('id-ID');
                }
            };
        }
    </script>
</x-layouts.app>
