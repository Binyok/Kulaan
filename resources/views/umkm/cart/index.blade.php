<x-layouts.app>
    <x-slot name="title">Keranjang Belanja</x-slot>

    <div class="-mx-3 sm:-mx-6 lg:-mx-8 -mt-6 sm:-mt-8 mb-6 sm:mb-8">
        <div class="texture-grain bg-brand-forest text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
                <div class="max-w-2xl" data-reveal>
                    <a href="{{ route('umkm.catalog.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-white/70 hover:text-white transition-colors">
                        &larr; Kembali ke Katalog
                    </a>
                    <h1 class="font-display font-semibold tracking-tight text-3xl sm:text-4xl mt-3">
                        Keranjang Belanja
                    </h1>
                    <p class="text-xs sm:text-sm text-white/70 mt-1.5">
                        Kelola kebutuhan bahan baku sebelum checkout. Sistem akan mengumpulkan kuota bersama UMKM lain.
                    </p>
                </div>
            </div>
        </div>
    </div>

    @if (! empty($items))
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start" data-reveal>
            <div class="lg:col-span-2 space-y-4">
                @foreach ($items as $item)
                    @php
                        $prod = $item['product'];
                        $dist = $item['distributor'];
                        $sp = $item['supplier_product'];
                    @endphp
                    <article class="bg-white rounded-2xl border border-brand-line p-5 sm:p-6 shadow-sm flex flex-col sm:flex-row items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="badge bg-brand-cream text-brand-slate text-[0.625rem]">
                                    {{ $prod->category->name ?? 'Bahan Baku' }}
                                </span>
                                <span class="badge bg-brand-mint text-brand-emerald text-[0.625rem] font-bold">
                                    {{ $item['tier_label'] }}
                                </span>
                                @if (! $item['is_available'])
                                    <span class="badge bg-brand-brick/10 text-brand-brick text-[0.625rem] font-bold">
                                        Distributor Tidak Aktif
                                    </span>
                                @endif
                            </div>

                            <h2 class="font-display font-bold text-brand-dark text-lg sm:text-xl mt-2 leading-snug">
                                <a href="{{ route('umkm.catalog.show', $prod) }}" class="hover:text-brand-primary transition-colors">
                                    {{ $prod->name }}
                                </a>
                            </h2>

                            <p class="text-xs text-brand-slate mt-1 flex items-center gap-1.5 flex-wrap">
                                <span>Distributor: <strong class="text-brand-dark">{{ $dist->company_name ?? '-' }}</strong></span>
                                @if ($dist && $dist->rating)
                                    <span class="text-brand-brass font-bold">&#9733; {{ number_format((float) $dist->rating, 1) }}</span>
                                @endif
                                <span>&bull; MOQ {{ number_format($item['moq'], 0) }} {{ $prod->unit }}</span>
                            </p>

                            <div class="mt-4 pt-3 border-t border-brand-line flex flex-wrap items-baseline gap-3 text-xs">
                                <div>
                                    <span class="text-[0.6875rem] text-brand-slate block">Harga Satuan (Estimasi)</span>
                                    <span class="font-extrabold text-brand-primary text-base tabular-nums">
                                        Rp {{ number_format($item['unit_price'], 0, ',', '.') }}
                                    </span>
                                    <span class="text-[0.6875rem] text-brand-slate">/{{ $prod->unit }}</span>
                                </div>
                                @if ($item['savings'] > 0)
                                    <div class="px-2.5 py-1 rounded-lg bg-brand-mint text-brand-primary text-[0.6875rem] font-bold self-center">
                                        Hemat Rp {{ number_format($item['savings'], 0, ',', '.') }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="w-full sm:w-auto flex flex-row sm:flex-col items-center sm:items-end justify-between gap-3 pt-3 sm:pt-0 border-t sm:border-t-0 border-brand-line">
                            <form method="POST" action="{{ route('umkm.cart.update') }}" class="flex items-center gap-1.5">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $item['product_id'] }}">
                                <input type="hidden" name="distributor_id" value="{{ $item['distributor_id'] }}">
                                <button type="submit" name="quantity" value="{{ max(0, round($item['quantity'] - 0.5, 2)) }}" class="w-9 h-9 rounded-xl border border-brand-line bg-brand-cream hover:bg-black/[0.04] text-brand-dark font-bold text-sm flex items-center justify-center transition-colors" title="Kurangi">
                                    &minus;
                                </button>
                                <span class="px-2.5 py-1 font-bold text-sm text-brand-dark tabular-nums min-w-[3.5rem] text-center">
                                    {{ number_format($item['quantity'], 1) }} <span class="text-[0.6875rem] text-brand-slate font-normal">{{ $prod->unit }}</span>
                                </span>
                                <button type="submit" name="quantity" value="{{ round($item['quantity'] + 0.5, 2) }}" class="w-9 h-9 rounded-xl border border-brand-line bg-brand-cream hover:bg-black/[0.04] text-brand-dark font-bold text-sm flex items-center justify-center transition-colors" title="Tambah">
                                    &plus;
                                </button>
                            </form>

                            <div class="text-right">
                                <span class="text-[0.6875rem] text-brand-slate block">Subtotal (Estimasi)</span>
                                <span class="font-extrabold text-brand-dark text-base tabular-nums">
                                    Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
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
                            <dt class="text-brand-slate">Total Jenis Barang</dt>
                            <dd class="font-bold text-brand-dark tabular-nums">{{ count($items) }} produk</dd>
                        </div>

                        @if ($totalSavings > 0)
                            <div class="flex items-center justify-between text-brand-primary">
                                <dt class="font-semibold">Estimasi Hemat vs Eceran</dt>
                                <dd class="font-bold tabular-nums">Rp {{ number_format($totalSavings, 0, ',', '.') }}</dd>
                            </div>
                        @endif

                        <div class="pt-3 border-t border-brand-line flex items-baseline justify-between">
                            <dt class="text-sm font-bold text-brand-dark">Total Estimasi</dt>
                            <dd class="font-extrabold text-brand-primary text-xl tabular-nums">
                                Rp {{ number_format($total, 0, ',', '.') }}
                            </dd>
                        </div>
                    </dl>

                    <p class="text-[0.6875rem] text-brand-slate mt-4 leading-relaxed bg-brand-cream/60 p-3 rounded-xl border border-brand-line/50">
                        <strong>Catatan Kulaan:</strong> Harga di atas adalah <em>Estimasi</em> berdasarkan kuantitas kebutuhan Anda. Harga final mengikuti total kuota kelompok kulaan saat matching berhasil — bisa lebih murah.
                    </p>

                    <form method="POST" action="{{ route('umkm.cart.checkout') }}" class="mt-5">
                        @csrf
                        <button type="submit" class="btn-primary w-full justify-center py-3.5 text-sm font-bold min-h-12 shadow-sm">
                            Checkout Sekarang ({{ count($items) }}) &rarr;
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
</x-layouts.app>
