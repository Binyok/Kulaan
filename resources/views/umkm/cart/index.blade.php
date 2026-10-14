<x-layouts.app>
    <x-slot name="title">Keranjang Belanja</x-slot>

    <div class="mb-6">
        <div class="bg-gradient-to-r from-brand-forest to-brand-primary text-white rounded-2xl p-5 sm:p-7 shadow-sm">
            <div class="max-w-2xl">
                <a href="{{ route('umkm.catalog.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-white/80 hover:text-white transition-colors">
                    &larr; Lanjut Belanja di Katalog
                </a>
                <h1 class="font-display font-bold text-2xl sm:text-3xl mt-2 tracking-tight">
                    Keranjang Belanja
                </h1>
                <p class="text-xs sm:text-sm text-white/80 mt-1">
                    Kebutuhan belanja Anda akan digabungkan secara otomatis dengan UMKM lain untuk mencapai kuota grosir distributor.
                </p>
            </div>
        </div>
    </div>

    @if (! empty($items))
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start" id="cartContainer">
            <div class="lg:col-span-2 space-y-3" id="cartItemsList">
                @foreach ($items as $item)
                    @php
                        $prod = $item['product'];
                        $dist = $item['distributor'];
                        $sp = $item['supplier_product'];
                    @endphp
                    <article class="bg-white rounded-2xl border border-brand-line p-4 sm:p-5 shadow-sm flex flex-col sm:flex-row items-start justify-between gap-4 transition-all"
                             id="cart-card-{{ $item['key'] }}"
                             data-item-key="{{ $item['key'] }}">
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="badge bg-brand-cream text-brand-slate text-[0.625rem]">
                                    {{ $prod->category->name ?? 'Bahan Baku' }}
                                </span>
                                <span class="badge bg-brand-mint text-brand-emerald text-[0.625rem] font-bold"
                                      id="tier-badge-{{ $item['key'] }}">
                                    {{ $item['tier_label'] }}
                                </span>
                                @if (! $item['is_available'])
                                    <span class="badge bg-brand-brick/10 text-brand-brick text-[0.625rem] font-bold">
                                        Distributor Tidak Aktif
                                    </span>
                                @endif
                            </div>

                            <h2 class="font-display font-bold text-brand-dark text-base sm:text-lg mt-1.5 leading-snug">
                                <a href="{{ route('umkm.catalog.show', $prod) }}" class="hover:text-brand-primary transition-colors">
                                    {{ $prod->name }}
                                </a>
                            </h2>

                            <p class="text-xs text-brand-slate mt-0.5 flex items-center gap-1.5 flex-wrap">
                                <span>Distributor: <strong class="text-brand-dark">{{ $dist->company_name ?? '-' }}</strong></span>
                                @if ($dist && $dist->rating)
                                    <span class="text-brand-brass font-bold">&#9733; {{ number_format((float) $dist->rating, 1) }}</span>
                                @endif
                                <span>&bull; Kuota target {{ number_format($item['moq'], 0) }} {{ $prod->unit }} (diurunkan bersama)</span>
                            </p>

                            <div class="mt-3 pt-2.5 border-t border-brand-line/60 flex flex-wrap items-baseline gap-3 text-xs">
                                <div>
                                    <span class="text-[0.625rem] text-brand-slate block">Harga Satuan (Estimasi)</span>
                                    <span class="font-extrabold text-brand-primary text-sm sm:text-base tabular-nums">
                                        Rp <span id="unit-price-{{ $item['key'] }}">{{ number_format($item['unit_price'], 0, ',', '.') }}</span>
                                    </span>
                                    <span class="text-[0.6875rem] text-brand-slate">/{{ $prod->unit }}</span>
                                </div>
                                <div class="px-2 py-0.5 rounded-lg bg-brand-mint text-brand-primary text-[0.6875rem] font-bold self-center {{ $item['savings'] > 0 ? '' : 'hidden' }}"
                                     id="savings-badge-{{ $item['key'] }}">
                                    Hemat Rp <span id="savings-amount-{{ $item['key'] }}">{{ number_format($item['savings'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="w-full sm:w-auto flex flex-row sm:flex-col items-center sm:items-end justify-between gap-3 pt-3 sm:pt-0 border-t sm:border-t-0 border-brand-line">
                            <div class="flex items-center gap-1.5 bg-brand-cream/80 p-1 rounded-xl border border-brand-line/70">
                                <button type="button"
                                        onclick="changeCartQty('{{ $item['product_id'] }}', '{{ $item['distributor_id'] }}', '{{ $item['key'] }}', -1)"
                                        class="w-8 h-8 rounded-lg bg-white hover:bg-black/[0.04] text-brand-dark font-extrabold text-base flex items-center justify-center transition-colors shadow-2xs">
                                    &minus;
                                </button>
                                <span class="px-2 font-bold text-sm text-brand-dark tabular-nums min-w-[3rem] text-center"
                                      id="qty-label-{{ $item['key'] }}">
                                    {{ $item['quantity'] == floor($item['quantity']) ? number_format($item['quantity'], 0) : number_format($item['quantity'], 1) }}
                                </span>
                                <button type="button"
                                        onclick="changeCartQty('{{ $item['product_id'] }}', '{{ $item['distributor_id'] }}', '{{ $item['key'] }}', 1)"
                                        class="w-8 h-8 rounded-lg bg-white hover:bg-black/[0.04] text-brand-dark font-extrabold text-base flex items-center justify-center transition-colors shadow-2xs">
                                    &plus;
                                </button>
                                <span class="text-[0.6875rem] font-bold text-brand-slate pr-1.5">{{ $prod->unit }}</span>
                            </div>

                            <div class="text-right">
                                <span class="text-[0.625rem] text-brand-slate block">Subtotal (Estimasi)</span>
                                <span class="font-extrabold text-brand-dark text-sm sm:text-base tabular-nums">
                                    Rp <span id="subtotal-{{ $item['key'] }}">{{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                                </span>
                            </div>

                            <form method="POST" action="{{ route('umkm.cart.remove') }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $item['product_id'] }}">
                                <input type="hidden" name="distributor_id" value="{{ $item['distributor_id'] }}">
                                <button type="submit" class="text-xs text-brand-brick hover:underline font-semibold" onclick="return confirm('Hapus item ini dari keranjang?')">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="lg:col-span-1 space-y-4">
                <section class="bg-white rounded-2xl border border-brand-line p-5 sm:p-6 shadow-sm sticky top-24">
                    <h3 class="font-display font-bold text-brand-dark text-base border-b border-brand-line pb-3">
                        Ringkasan Belanja
                    </h3>

                    <dl class="space-y-3 mt-4 text-xs">
                        <div class="flex items-center justify-between">
                            <dt class="text-brand-slate">Total Jenis Bahan</dt>
                            <dd class="font-bold text-brand-dark tabular-nums">
                                <span id="summary-count">{{ count($items) }}</span> produk
                            </dd>
                        </div>

                        <div class="flex items-center justify-between text-brand-primary" id="summary-savings-row">
                            <dt class="font-semibold">Estimasi Hemat vs Eceran</dt>
                            <dd class="font-bold tabular-nums">
                                Rp <span id="summary-savings">{{ number_format($totalSavings, 0, ',', '.') }}</span>
                            </dd>
                        </div>

                        <div class="pt-3 border-t border-brand-line flex items-baseline justify-between">
                            <dt class="text-sm font-bold text-brand-dark">Total Estimasi</dt>
                            <dd class="font-extrabold text-brand-primary text-xl tabular-nums">
                                Rp <span id="summary-total">{{ number_format($total, 0, ',', '.') }}</span>
                            </dd>
                        </div>
                    </dl>

                    <p class="text-[0.6875rem] text-brand-slate mt-4 leading-relaxed bg-brand-cream/60 p-3 rounded-xl border border-brand-line/50">
                        <strong>Catatan Kulaan:</strong> Anda bebas memesan berapa pun kuantitasnya. Pesanan akan otomatis digabung dengan UMKM lain di kecamatan Anda sampai kuota minimum distributor terpenuhi.
                    </p>

                    <form method="POST" action="{{ route('umkm.cart.checkout') }}" class="mt-5">
                        @csrf
                        <button type="submit" class="btn-primary w-full justify-center py-3.5 text-sm font-bold min-h-12 shadow-sm">
                            Checkout Sekarang &rarr;
                        </button>
                    </form>

                    <a href="{{ route('umkm.catalog.index') }}" class="btn-ghost w-full justify-center py-2.5 text-xs font-semibold mt-2.5 min-h-11">
                        + Tambah Produk Lain
                    </a>
                </section>
            </div>
        </div>
    @else
        <section class="bg-white rounded-2xl border border-brand-line p-10 sm:p-16 text-center max-w-lg mx-auto shadow-sm" data-reveal>
            <div class="w-16 h-16 rounded-2xl bg-brand-mint text-brand-primary flex items-center justify-center mx-auto font-display font-bold text-2xl">
                K
            </div>
            <h2 class="font-display font-bold text-brand-dark text-xl sm:text-2xl mt-4">
                Keranjang Belanja Masih Kosong
            </h2>
            <p class="text-xs sm:text-sm text-brand-slate mt-2 leading-relaxed">
                Anda belum menambahkan bahan baku ke keranjang. Jelajahi katalog distributor untuk mulai mengumpulkan kuota kulaan bersama UMKM lain.
            </p>
            <a href="{{ route('umkm.catalog.index') }}" class="btn-primary inline-flex text-sm font-bold px-6 py-3 mt-6">
                Lihat Katalog Bahan Baku &rarr;
            </a>
        </section>
    @endif

    <script>
        const cartCurrentQtys = {
            @foreach ($items as $item)
                '{{ $item['key'] }}': {{ (float) $item['quantity'] }},
            @endforeach
        };

        function formatRupiah(num) {
            return Math.round(num || 0).toLocaleString('id-ID');
        }

        async function changeCartQty(productId, distributorId, key, delta) {
            const current = cartCurrentQtys[key] || 1;
            const nextQty = Math.max(0, Math.round(current + delta));

            if (nextQty <= 0) {
                if (!confirm('Hapus item ini dari keranjang?')) {
                    return;
                }
            }

            try {
                const response = await fetch("{{ route('umkm.cart.update') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        distributor_id: distributorId,
                        quantity: nextQty
                    })
                });

                const data = await response.json();
                if (!data.success) {
                    alert('Gagal memperbarui kuantitas');
                    return;
                }

                if (data.removed) {
                    const card = document.getElementById(`cart-card-${key}`);
                    if (card) card.remove();
                    delete cartCurrentQtys[key];
                    if (data.count === 0) {
                        location.reload();
                        return;
                    }
                } else {
                    cartCurrentQtys[key] = nextQty;
                    const qtyLabel = document.getElementById(`qty-label-${key}`);
                    if (qtyLabel) qtyLabel.textContent = nextQty;

                    const unitPriceSpan = document.getElementById(`unit-price-${key}`);
                    if (unitPriceSpan) unitPriceSpan.textContent = formatRupiah(data.item.unit_price);

                    const subtotalSpan = document.getElementById(`subtotal-${key}`);
                    if (subtotalSpan) subtotalSpan.textContent = formatRupiah(data.item.subtotal);

                    const tierBadge = document.getElementById(`tier-badge-${key}`);
                    if (tierBadge && data.item.tier_label) tierBadge.textContent = data.item.tier_label;

                    const savingsBadge = document.getElementById(`savings-badge-${key}`);
                    const savingsAmountSpan = document.getElementById(`savings-amount-${key}`);
                    if (savingsBadge && savingsAmountSpan) {
                        if (data.item.savings > 0) {
                            savingsBadge.classList.remove('hidden');
                            savingsAmountSpan.textContent = formatRupiah(data.item.savings);
                        } else {
                            savingsBadge.classList.add('hidden');
                        }
                    }
                }

                const summaryCount = document.getElementById('summary-count');
                if (summaryCount) summaryCount.textContent = data.count;

                const summaryTotal = document.getElementById('summary-total');
                if (summaryTotal) summaryTotal.textContent = formatRupiah(data.total);

                const summarySavings = document.getElementById('summary-savings');
                if (summarySavings) summarySavings.textContent = formatRupiah(data.total_savings);

            } catch (err) {
                console.error(err);
            }
        }
    </script>
</x-layouts.app>
