<x-layouts.app>
    <x-slot name="title">Katalog Distributor</x-slot>

    <div class="-mx-3 sm:-mx-6 lg:-mx-8 -mt-6 sm:-mt-8 mb-6 sm:mb-8">
        <div class="texture-grain bg-brand-forest text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
                <div class="max-w-2xl" data-reveal>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[0.625rem] font-semibold bg-white/10 text-[#D8E8DD] uppercase tracking-[0.14em">
                        Marketplace Kula'an
                    </span>
                    <h1 class="font-display font-semibold tracking-tight text-balance text-4xl sm:text-5xl mt-4">
                        Katalog
                    </h1>
                    <p class="text-sm text-white/70 leading-relaxed mt-3">
                        Pilih bahan baku, bandingkan penawaran distributor, dan ajukan pembelian ke pilihanmu. Sistem hanya memberi rekomendasi.
                    </p>
                </div>
                <form method="GET" action="{{ route('umkm.catalog.index') }}" class="mt-6 max-w-xl" data-reveal>
                    <div class="flex gap-2">
                        <input type="search" name="q" value="{{ $search }}" placeholder="Cari bahan baku..." class="input-field bg-white/95 flex-1">
                        <button type="submit" class="btn-accent text-sm px-6 shrink-0">Cari</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="space-y-6 sm:space-y-8">
        @if ($products->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" data-reveal>
                @foreach ($products as $prod)
                    @php
                        $available = $prod->supplierProducts->where('is_available', true);
                        $minPrice = $available->min('base_retail_price') ?? $prod->benchmark_retail_price;
                    @endphp
                    <a href="{{ route('umkm.catalog.show', $prod) }}"
                        class="bg-white rounded-2xl border border-brand-line overflow-hidden shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all block">
                        <div class="aspect-[4/3] bg-brand-mint flex items-center justify-center">
                            <span class="font-display font-semibold text-4xl text-brand-primary">{{ strtoupper(substr($prod->name, 0, 1)) }}</span>
                        </div>
                        <div class="p-4 sm:p-5">
                            <p class="font-display font-bold text-brand-dark text-base leading-snug">{{ $prod->name }}</p>
                            <span class="badge bg-brand-cream text-brand-slate mt-2">{{ $prod->category->name ?? 'Tanpa kategori' }}</span>
                            <div class="mt-3 flex items-end justify-between gap-2">
                                <div>
                                    <p class="text-[0.6875rem] text-brand-slate">mulai dari</p>
                                    <p class="font-extrabold text-brand-primary tabular-nums text-lg">Rp {{ number_format($minPrice, 0, ',', '.') }}</p>
                                </div>
                                <span class="text-xs font-bold text-brand-primary shrink-0">Lihat Penawaran &rarr;</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="pt-2">{{ $products->links() }}</div>
        @else
            <section class="bg-white rounded-[20px] border border-brand-line px-6 py-12 text-center" data-reveal>
                <p class="font-bold text-brand-dark mt-2">Belum ada produk tersedia.</p>
                <p class="text-[0.8125rem] text-brand-slate mt-1.5">Coba kata kunci lain atau kembali lagi nanti.</p>
                <a href="{{ route('umkm.catalog.index') }}" class="btn-primary text-sm mt-5">Lihat Semua</a>
            </section>
        @endif
    </div>
</x-layouts.app>
