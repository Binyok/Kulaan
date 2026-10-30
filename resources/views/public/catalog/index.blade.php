<x-layouts.public title="Jelajahi Produk">
    <main class="max-w-7xl mx-auto px-4 sm:px-6 pb-16">

        <!-- HERO RINGKAS + SEARCH -->
        <section class="mt-6 relative overflow-hidden rounded-[2rem] bg-gradient-to-r from-forest-deep via-forest to-forest-dark text-white p-6 sm:p-8 lg:p-10">
            <div class="absolute -right-16 -top-20 w-72 h-72 rounded-full bg-white/10 pointer-events-none"></div>
            <div class="absolute right-40 -bottom-24 w-56 h-56 rounded-full bg-brass/20 pointer-events-none"></div>
            <div class="relative max-w-3xl">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-brass-soft">Katalog Publik · Tanpa Login</p>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold leading-tight mt-2">Jelajahi Produk</h1>
                <p class="text-white/75 text-xs sm:text-sm mt-3 max-w-xl">Lihat-lihat dulu semua produk dan harga grosirnya, bebas tanpa akun. Login hanya diperlukan saat kamu mau memasukkan ke keranjang dan memesan.</p>
                <form method="GET" action="{{ route('public.catalog.index') }}" class="flex items-center gap-2 bg-white rounded-2xl p-2 mt-6 max-w-xl shadow-lg">
                    <svg class="w-5 h-5 text-muted shrink-0 ml-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21 21-5.2-5.2m0 0A7.5 7.5 0 1 0 5.2 5.2a7.5 7.5 0 0 0 10.6 10.6Z"/></svg>
                    <input type="text" name="q" value="{{ $search }}" class="flex-1 bg-transparent text-sm text-ink outline-none placeholder:text-muted/70" placeholder="Cari beras, minyak goreng, gula...">
                    @if($categoryId)
                        <input type="hidden" name="category" value="{{ $categoryId }}">
                    @endif
                    <button type="submit" class="bg-forest hover:bg-forest-dark text-white text-xs sm:text-sm font-bold px-4 sm:px-6 py-2.5 rounded-xl cursor-pointer">Cari</button>
                </form>
            </div>
        </section>

        <!-- STRIP STATISTIK PUBLIK -->
        <section class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="bg-white border border-line rounded-3xl px-6 py-5 flex items-center gap-4 shadow-xs">
                <span class="w-12 h-12 rounded-2xl bg-forest/10 text-forest flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.25 7.5l-.63 10.63a2.25 2.25 0 0 1-2.25 2.12H6.63a2.25 2.25 0 0 1-2.25-2.12L3.75 7.5M10 11.25h4M3.38 7.5h17.25c.62 0 1.12-.5 1.12-1.12v-1.5C21.75 4.26 21.25 3.75 20.63 3.75H3.38c-.62 0-1.13.5-1.13 1.12v1.5c0 .63.5 1.13 1.12 1.13Z"/></svg>
                </span>
                <div>
                    <p class="text-2xl font-extrabold leading-none text-ink">{{ $stats['total_products'] }}+</p>
                    <p class="text-xs text-muted mt-1">Produk sembako</p>
                </div>
            </div>
            <div class="bg-white border border-line rounded-3xl px-6 py-5 flex items-center gap-4 shadow-xs">
                <span class="w-12 h-12 rounded-2xl bg-brass-soft text-brass flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path d="M19.5 10.5c0 7.14-7.5 11.25-7.5 11.25S4.5 17.64 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                </span>
                <div>
                    <p class="text-2xl font-extrabold leading-none text-ink">{{ $stats['total_distributor'] }}</p>
                    <p class="text-xs text-muted mt-1">Distributor mitra</p>
                </div>
            </div>
            <div class="bg-white border border-line rounded-3xl px-6 py-5 flex items-center gap-4 shadow-xs">
                <span class="w-12 h-12 rounded-2xl bg-forest/10 text-forest flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 18.72a9.09 9.09 0 0 0 3.74-.83A9.9 9.9 0 0 0 21.75 12c0-5.37-4.35-9.75-9.75-9.75S2.25 6.6 2.25 12c0 2.22.74 4.27 1.99 5.92a9.1 9.1 0 0 0 3.74.83m9.03-2.53c-.78-1.18-1.83-2.26-3.03-3.17m3.03 3.17A9.06 9.06 0 0 1 12 20.25a9.06 9.06 0 0 1-5.03-1.53m5.97-2.49c.55.4 1.06.85 1.53 1.34M7.5 10.5a2.25 2.25 0 1 1 4.5 0 2.25 2.25 0 0 1-4.5 0Zm5.25 6.75a4.5 4.5 0 0 0-6.47 1.28 9.05 9.05 0 0 0 1.19.7 9.02 9.02 0 0 0 9.06 0c.3-.22.59-.46.87-.7a4.5 4.5 0 0 0-4.65-1.28Z"/></svg>
                </span>
                <div>
                    <p class="text-2xl font-extrabold leading-none text-ink">{{ $stats['total_umkm'] }}+</p>
                    <p class="text-xs text-muted mt-1">UMKM bergabung</p>
                </div>
            </div>
        </section>

        <!-- CHIPS KATEGORI -->
        <section class="mt-8 flex flex-wrap gap-2">
            <a href="{{ route('public.catalog.index', array_filter(['q' => $search])) }}"
               class="{{ ! $categoryId ? 'bg-forest text-white' : 'bg-white border border-line text-ink hover:border-forest/40' }} text-xs sm:text-sm font-bold px-4 sm:px-5 py-2 sm:py-2.5 rounded-full transition">
               Semua
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('public.catalog.index', array_filter(['category' => $cat->id, 'q' => $search])) }}"
                   class="{{ (string)$categoryId === (string)$cat->id ? 'bg-forest text-white' : 'bg-white border border-line text-ink hover:border-forest/40' }} text-xs sm:text-sm font-semibold px-4 sm:px-5 py-2 sm:py-2.5 rounded-full transition">
                   {{ $cat->name }}
                </a>
            @endforeach
        </section>

        <!-- FILTER + GRID -->
        <section class="mt-6">

            <!-- HEADER GRID -->
            <div class="flex items-end justify-between mb-4 flex-wrap gap-2">
                <div>
                    <h2 class="text-xl font-extrabold text-ink">Semua Produk</h2>
                    <p class="text-xs sm:text-sm text-muted mt-0.5">Menampilkan {{ $products->count() }} dari {{ $products->total() }} produk · Sidoarjo &amp; sekitarnya</p>
                </div>
                <p class="text-xs text-muted">Harga berlabel <span class="font-bold text-brass">Estimasi</span></p>
            </div>

            @if($products->isEmpty())
                <div class="bg-white border border-line rounded-3xl p-12 text-center">
                    <p class="text-base font-bold text-ink">Tidak ada produk ditemukan</p>
                    <p class="text-xs text-muted mt-1">Coba kata kunci atau kategori lain.</p>
                </div>
            @else
                <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($products as $prod)
                        @php
                            $available = $prod->supplierProducts->where('is_available', true);
                            $availablePrices = $available->flatMap(fn ($sp) => collect([(float) $sp->base_retail_price])->merge($sp->priceTiers->pluck('unit_price')->map(fn ($p) => (float) $p)));
                            $minPrice = $availablePrices->min() ?? (float) $prod->benchmark_retail_price;
                            $discountPct = (float) $prod->benchmark_retail_price > 0 && $minPrice < (float) $prod->benchmark_retail_price
                                ? (int) round((1 - $minPrice / (float) $prod->benchmark_retail_price) * 100)
                                : null;
                            $distributorCount = $available->pluck('distributor_id')->unique()->count();
                            $initialLetter = strtoupper(substr($prod->name, 0, 1));
                            $tones = ['bg-forest/10 text-forest', 'bg-brass-soft text-brass', 'bg-cream border border-line text-muted'];
                            $tone = $tones[$loop->index % 3];
                        @endphp
                        <article class="bg-white border border-line rounded-3xl p-3 sm:p-4 hover:border-forest/40 hover:shadow-lg hover:shadow-forest/5 transition flex flex-col justify-between">
                            <div>
                                <div class="relative h-24 sm:h-32 rounded-2xl {{ $tone }} flex items-center justify-center">
                                    <span class="text-4xl sm:text-5xl font-extrabold">{{ $initialLetter }}</span>
                                    @if($discountPct !== null && $discountPct > 0)
                                        <span class="absolute top-2 left-2 bg-danger text-white text-[10px] font-bold px-2 py-0.5 rounded-full">-{{ $discountPct }}%</span>
                                    @endif
                                </div>
                                <h3 class="font-extrabold mt-2.5 text-ink text-sm leading-snug">{{ $prod->name }}</h3>
                                <p class="text-[11px] text-muted mt-0.5">{{ $prod->unit }} · {{ $distributorCount }} distributor</p>
                                <p class="text-[10px] text-muted mt-2.5">mulai dari · <span class="font-semibold text-brass">Estimasi</span></p>
                                <p class="font-extrabold text-base leading-tight text-ink">
                                    Rp {{ number_format($minPrice, 0, ',', '.') }}
                                    @if((float)$prod->benchmark_retail_price > $minPrice)
                                        <span class="text-[11px] font-semibold text-muted line-through">Rp {{ number_format($prod->benchmark_retail_price, 0, ',', '.') }}</span>
                                    @endif
                                </p>
                            </div>
                            <div class="flex gap-1.5 sm:gap-2 mt-3">
                                <a href="{{ route('public.catalog.show', $prod) }}" class="flex-1 text-center border border-forest text-forest text-xs font-bold px-2 sm:px-3 py-2 rounded-xl hover:bg-forest/5 transition">Lihat</a>
                                @auth
                                    @if(auth()->user()->hasRole('umkm'))
                                        <a href="{{ route('umkm.catalog.show', $prod) }}" class="bg-forest hover:bg-forest-dark text-white px-3 py-2 rounded-xl flex items-center justify-center text-xs font-bold transition">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2.25 3h1.39c.51 0 .96.34 1.09.84l.38 1.44M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.22c1.12-2.3 2.1-4.68 2.92-7.14a60.11 60.11 0 0 0-16.54-1.84M7.5 14.25 5.11 5.27M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                                        </a>
                                    @else
                                        <a href="{{ route('dashboard') }}" class="bg-forest hover:bg-forest-dark text-white px-3 py-2 rounded-xl flex items-center justify-center text-xs font-bold transition">+</a>
                                    @endif
                                @else
                                    <button type="button" onclick="document.getElementById('gateModal').classList.remove('hidden')" aria-label="Tambah {{ $prod->name }} ke keranjang" class="bg-forest hover:bg-forest-dark text-white px-3 py-2 rounded-xl flex items-center justify-center text-xs font-bold cursor-pointer transition">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2.25 3h1.39c.51 0 .96.34 1.09.84l.38 1.44M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.22c1.12-2.3 2.1-4.68 2.92-7.14a60.11 60.11 0 0 0-16.54-1.84M7.5 14.25 5.11 5.27M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                                    </button>
                                @endauth
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="mt-6">
                    {{ $products->links() }}
                </div>
            @endif
            <p class="text-xs text-muted mt-4">Harga estimasi — harga final mengikuti total quantity kelompok kulaan.</p>
        </section>

        <!-- BANNER TEASER SPK -->
        <section class="mt-10 bg-brass-soft border border-brass/25 rounded-[2rem] p-6 lg:p-8 flex flex-col md:flex-row md:items-center gap-5">
            <span class="w-14 h-14 shrink-0 rounded-2xl bg-white text-brass flex items-center justify-center">
                <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z"/></svg>
            </span>
            <div class="flex-1">
                <h2 class="font-extrabold text-lg text-ink">Mau distributor yang paling cocok buat usahamu?</h2>
                <p class="text-sm text-ink/75 mt-1">Login sebagai UMKM untuk melihat rekomendasi SPK (AHP · TOPSIS · SAW) berdasarkan lokasi dan kebutuhanmu.</p>
            </div>
            <a href="{{ route('login') }}" class="bg-forest hover:bg-forest-dark text-white text-xs sm:text-sm font-bold px-6 py-3 rounded-xl shrink-0 text-center transition">Masuk untuk Lihat Rekomendasi</a>
        </section>

    </main>

    <x-modal-gate-login />
</x-layouts.public>
