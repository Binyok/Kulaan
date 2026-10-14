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

    <div class="space-y-6 sm:space-y-8">
        <section class="bg-white rounded-[20px] border border-brand-line p-5 sm:p-7" data-reveal>
            <form method="GET" action="{{ route('umkm.catalog.show', $product) }}" class="flex flex-col sm:flex-row sm:items-end gap-3">
                <div class="flex-1">
                    <label for="quantity" class="field-label">Jumlah kebutuhan ({{ $product->unit }})</label>
                    <input type="number" id="quantity" name="quantity" value="{{ $quantity }}" step="0.5" min="0.1" required class="input-field max-w-xs">
                </div>
                <button type="submit" class="btn-ghost text-sm px-6 shrink-0">Hitung Ulang Harga</button>
            </form>
            <p class="text-xs text-brand-slate mt-3">Harga tier di semua kartu dihitung untuk <strong class="text-brand-dark">{{ number_format($quantity, 1) }} {{ $product->unit }}</strong>. Keputusan akhir tetap milikmu — badge rekomendasi hanya saran sistem.</p>
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
                    <article class="bg-white rounded-2xl border p-5 sm:p-6 shadow-sm {{ $isTop ? 'ring-2 ring-brand-accent border-brand-accent/40 shadow-md' : 'border-brand-line' }}">
                        <div class="flex items-start justify-between gap-3">
                            <p class="font-bold text-brand-dark text-base">{{ $d->company_name }}</p>
                            @if ($isTop)
                                <span class="badge bg-brand-ochre-soft text-brand-brass border border-brand-accent/30 shrink-0">Rekomendasi #1 Sistem</span>
                            @elseif (! empty($offer['rank']))
                                <span class="badge bg-brand-cream text-brand-slate shrink-0">Peringkat #{{ $offer['rank'] }}</span>
                            @endif
                        </div>

                        <p class="text-xs text-brand-slate mt-1.5">
                            <span class="font-bold text-brand-accent">&#9733; {{ number_format((float) $d->rating, 1) }}</span>
                            <span class="mx-1">&bull;</span> Kirim {{ $d->delivery_lead_time_days ?? '-' }} hari
                        </p>

                        <dl class="grid grid-cols-2 gap-2 mt-4 text-xs">
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
                            <div class="p-2.5 bg-brand-cream/70 rounded-xl">
                                <dt class="text-[0.6875rem] text-brand-slate">Skor sistem</dt>
                                <dd class="font-bold text-brand-dark mt-0.5 tabular-nums">{{ $offer['score'] !== null ? number_format((float) $offer['score'], 4) : '-' }}</dd>
                            </div>
                        </dl>

                        <div class="mt-4">
                            <p class="text-[0.6875rem] text-brand-slate">Terkumpul {{ number_format($offer['pooled_quantity'], 1) }} / MOQ {{ number_format($sp->moq, 0) }} — kurang {{ number_format($remaining, 1) }} lagi</p>
                            <div class="h-2 rounded-full bg-brand-cream mt-1.5 overflow-hidden">
                                <div class="h-full bg-brand-emerald rounded-full transition-all" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>

                        <div class="flex items-end justify-between gap-3 mt-4">
                            <div>
                                <p class="font-extrabold text-brand-primary text-2xl tabular-nums">Rp {{ number_format($offer['unit_price'], 0, ',', '.') }}</p>
                                <p class="text-[0.6875rem] text-brand-slate">/unit untuk {{ number_format($quantity, 1) }} {{ $product->unit }}</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('umkm.requests.store') }}" class="mt-4">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="quantity" value="{{ $quantity }}">
                            <input type="hidden" name="preferred_distributor_id" value="{{ $d->id }}">
                            <input type="hidden" name="hours_until_deadline" value="24">
                            <input type="hidden" name="urgency" value="sedang">
                            <input type="hidden" name="delivery_district" value="{{ $umkm->district }}">
                            <input type="hidden" name="delivery_address" value="{{ $umkm->address }}">
                            <button type="submit" class="btn-primary w-full justify-center py-3 text-sm font-bold">
                                Pilih &amp; Ajukan
                            </button>
                        </form>
                    </article>
                @endforeach
            </div>

            <section class="bg-white rounded-[20px] border border-brand-line p-5 sm:p-7 overflow-x-auto" data-reveal>
                <h2 class="text-sm font-bold text-brand-dark mb-3">Perbandingan ringkas</h2>
                <table class="table-premium min-w-[560px]">
                    <thead>
                        <tr>
                            <th>Distributor</th>
                            <th class="text-right">Harga/unit</th>
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
                                <td class="text-right tabular-nums">{{ number_format((float) $offer['distributor']->rating, 1) }}</td>
                                <td class="text-right tabular-nums">{{ $offer['distance_km'] !== null ? number_format($offer['distance_km'], 1).' km' : '-' }}</td>
                                <td class="text-right tabular-nums">{{ number_format($offer['supplier_product']->moq, 0) }}</td>
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
</x-layouts.app>
