<x-layouts.app>
    <x-slot name="title">Katalog Bahan Baku</x-slot>

    <div class="mb-6">
        <div class="bg-gradient-to-r from-brand-forest to-brand-primary text-white rounded-2xl p-5 sm:p-7 shadow-sm">
            <div class="max-w-2xl">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[0.6875rem] font-semibold bg-white/15 text-white">
                    Pengadaan Bersama UMKM
                </span>
                <h1 class="font-display font-bold text-2xl sm:text-3xl mt-2 tracking-tight">
                    Katalog Bahan Baku
                </h1>
                <p class="text-xs sm:text-sm text-white/80 mt-1">
                    Beli eceran dapat harga grosir melalui urunan kuota dengan UMKM lain.
                </p>
            </div>

            <form method="GET" action="{{ route('umkm.catalog.index') }}" class="mt-4 flex gap-2 max-w-xl">
                <div class="relative flex-1">
                    <input type="search"
                           name="q"
                           value="{{ $search }}"
                           placeholder="Cari beras, minyak, tepung, gula..."
                           class="w-full bg-white text-brand-dark placeholder-brand-slate/60 text-sm rounded-xl px-4 py-3 pl-10 border-0 focus:ring-2 focus:ring-brand-accent shadow-sm">
                    <svg class="w-4 h-4 text-brand-slate absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <button type="submit" class="btn-accent px-5 py-3 text-sm font-bold shrink-0 rounded-xl">
                    Cari
                </button>
            </form>
        </div>
    </div>

    @if ($products->isNotEmpty())
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 gap-3 sm:gap-4">
            @foreach ($products as $prod)
                @php
                    $available = $prod->supplierProducts->where('is_available', true);
                    $availablePrices = $available->flatMap(fn ($sp) => collect([(float) $sp->base_retail_price])->merge($sp->priceTiers->pluck('unit_price')->map(fn ($p) => (float) $p)));
                    $minPrice = $availablePrices->min() ?? (float) $prod->benchmark_retail_price;
                    $topRating = $topRatingByProduct[$prod->id] ?? null;
                    $distributorCount = $available->pluck('distributor_id')->unique()->count();
                @endphp
                <a href="{{ route('umkm.catalog.show', $prod) }}"
                   class="group bg-white rounded-2xl border border-brand-line/70 overflow-hidden shadow-sm hover:shadow-md hover:border-brand-primary/40 transition-all flex flex-col justify-between">
                    <div>
                        <div class="aspect-square bg-gradient-to-br from-brand-mint/60 to-brand-cream flex flex-col items-center justify-center p-4 relative overflow-hidden">
                            <span class="font-display font-extrabold text-5xl text-brand-primary/25 group-hover:scale-110 group-hover:text-brand-primary/40 transition-all">
                                {{ strtoupper(substr($prod->name, 0, 1)) }}
                            </span>
                            <span class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded-full text-[0.625rem] font-semibold bg-white/90 text-brand-dark shadow-xs backdrop-blur-xs">
                                {{ $prod->category->name ?? 'Bahan Baku' }}
                            </span>
                            @if ($distributorCount > 0)
                                <span class="absolute bottom-2.5 right-2.5 px-2 py-0.5 rounded-full text-[0.625rem] font-bold bg-brand-forest text-white">
                                    {{ $distributorCount }} distributor
                                </span>
                            @endif
                        </div>

                        <div class="p-3 sm:p-4">
                            <h3 class="font-display font-bold text-brand-dark text-sm sm:text-base line-clamp-2 leading-snug group-hover:text-brand-primary transition-colors">
                                {{ $prod->name }}
                            </h3>

                            <div class="flex items-center gap-1.5 mt-1.5 text-[0.6875rem] text-brand-slate">
                                @if ($topRating && $topRating['rating'] !== null)
                                    <span class="font-bold text-brand-brass flex items-center gap-0.5">
                                        &#9733; {{ number_format($topRating['rating'], 1) }}
                                    </span>
                                    <span>&bull;</span>
                                @endif
                                <span>per {{ $prod->unit }}</span>
                            </div>

                            <div class="mt-3 pt-2.5 border-t border-brand-line/50">
                                <span class="text-[0.625rem] uppercase tracking-wider text-brand-slate font-medium block">Harga Mulai</span>
                                <div class="flex items-baseline gap-1 mt-0.5">
                                    <span class="font-extrabold text-brand-primary text-base sm:text-lg tabular-nums">
                                        Rp {{ number_format($minPrice, 0, ',', '.') }}
                                    </span>
                                </div>
                                @if ($prod->benchmark_retail_price > $minPrice)
                                    <div class="text-[0.625rem] text-brand-slate/70 line-through tabular-nums mt-0.5">
                                        Eceran Rp {{ number_format($prod->benchmark_retail_price, 0, ',', '.') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="p-3 sm:p-4 pt-0">
                        <div class="w-full py-2 px-3 rounded-xl bg-brand-cream group-hover:bg-brand-primary group-hover:text-white text-brand-primary font-bold text-xs text-center transition-colors">
                            Lihat Penawaran &rarr;
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="pt-6">
            {{ $products->links() }}
        </div>
    @else
        <div class="bg-white rounded-2xl border border-brand-line p-10 text-center max-w-md mx-auto my-12">
            <h3 class="font-display font-bold text-brand-dark text-lg">Produk tidak ditemukan</h3>
            <p class="text-xs text-brand-slate mt-1">Coba kata kunci lain atau lihat semua produk.</p>
            <a href="{{ route('umkm.catalog.index') }}" class="btn-primary text-xs font-bold px-4 py-2 mt-4 inline-flex">
                Tampilkan Semua
            </a>
        </div>
    @endif
</x-layouts.app>
