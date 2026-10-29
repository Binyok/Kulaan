<x-layouts.public :title="$product->name">
    <main class="max-w-7xl mx-auto px-4 sm:px-6 pb-16">

        <!-- BREADCRUMB -->
        <nav class="text-xs sm:text-sm text-muted mt-6 flex items-center gap-2 flex-wrap">
            <a href="{{ route('home') }}" class="hover:text-ink">Beranda</a><span>/</span>
            <a href="{{ route('public.catalog.index') }}" class="hover:text-ink">Jelajahi Produk</a><span>/</span>
            @if($product->category)
                <a href="{{ route('public.catalog.index', ['category' => $product->category->id]) }}" class="hover:text-ink">{{ $product->category->name }}</a><span>/</span>
            @endif
            <span class="text-ink font-bold">{{ $product->name }}</span>
        </nav>

        <section class="grid lg:grid-cols-2 gap-8 mt-5 items-start">
            <!-- GALERI + DESKRIPSI -->
            <div>
                <div class="relative bg-forest/10 border border-line rounded-[2rem] h-72 sm:h-96 flex items-center justify-center">
                    <span class="text-8xl sm:text-[10rem] leading-none font-extrabold text-forest">{{ strtoupper(substr($product->name, 0, 1)) }}</span>
                    <span class="absolute bottom-5 right-5 bg-white/90 text-ink text-xs font-bold px-3 py-1.5 rounded-full shadow-xs">{{ $product->unit }}</span>
                </div>

                <div class="bg-white border border-line rounded-3xl p-6 mt-5 shadow-xs">
                    <h2 class="font-extrabold text-lg text-ink">Deskripsi Produk</h2>
                    <p class="text-xs sm:text-sm text-muted leading-relaxed mt-2">
                        {{ $product->standard_specification ?? 'Bahan baku berkualitas tinggi untuk kebutuhan usaha kuliner dan toko kelontong. Dipasok langsung dari distributor terpercaya.' }}
                    </p>
                    <div class="grid grid-cols-3 gap-3 mt-5 text-center">
                        <div class="bg-cream border border-line rounded-2xl py-3">
                            <p class="text-[11px] text-muted font-semibold">Kemasan</p>
                            <p class="font-extrabold text-xs sm:text-sm text-ink mt-0.5">{{ $product->unit }}</p>
                        </div>
                        <div class="bg-cream border border-line rounded-2xl py-3">
                            <p class="text-[11px] text-muted font-semibold">Kategori</p>
                            <p class="font-extrabold text-xs sm:text-sm text-ink mt-0.5">{{ $product->category->name ?? 'Sembako' }}</p>
                        </div>
                        <div class="bg-cream border border-line rounded-2xl py-3">
                            <p class="text-[11px] text-muted font-semibold">Distributor</p>
                            <p class="font-extrabold text-xs sm:text-sm text-ink mt-0.5">{{ count($offers) }} Opsi</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- INFO + HARGA + AKSI -->
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-ink mt-1">{{ $product->name }}</h1>
                <p class="text-xs sm:text-sm text-muted mt-1">Kategori {{ $product->category->name ?? 'Sembako' }} · Dijual oleh {{ count($offers) }} distributor</p>

                <!-- PROGRES KUOTA KOLEKTIF -->
                <div class="bg-white border border-line rounded-3xl p-5 mt-5 shadow-xs">
                    <div class="flex items-center justify-between text-xs font-bold text-ink">
                        <span>Progress Kelompok Kulaan</span>
                        <span class="text-forest">{{ number_format($collectedQty, 0) }}/{{ number_format($moq, 0) }} {{ $product->unit }}</span>
                    </div>
                    <div class="w-full bg-cream rounded-full h-3 mt-2 overflow-hidden border border-line">
                        @php $progressPct = min(100, $moq > 0 ? ($collectedQty / $moq) * 100 : 0); @endphp
                        <div class="bg-forest h-full rounded-full transition-all duration-300" style="width: {{ $progressPct }}%"></div>
                    </div>
                    <p class="text-xs text-muted mt-2">
                        Terkumpul {{ number_format($collectedQty, 0) }}/{{ number_format($moq, 0) }} {{ $product->unit }} — kurang {{ number_format($remainingMoq, 0) }} {{ $product->unit }} lagi menuju minimum pemesanan
                    </p>
                </div>

                <div class="bg-white border border-line rounded-3xl p-6 mt-5 shadow-xs">
                    <h2 class="font-extrabold text-ink">Harga bertingkat <span class="text-brass text-sm font-bold">(Estimasi)</span></h2>
                    
                    <div class="space-y-2 mt-4 text-xs sm:text-sm">
                        <div class="flex justify-between items-center border border-line rounded-xl px-4 py-3">
                            <span class="text-muted font-semibold">Harga Eceran Patokan</span>
                            <span class="font-bold text-ink">Rp {{ number_format((float)$product->benchmark_retail_price, 0, ',', '.') }}<span class="text-muted font-medium">/{{ $product->unit }}</span></span>
                        </div>
                        @if(count($offers) > 0)
                            @php $lowestOffer = $offers[0]; @endphp
                            <div class="flex justify-between items-center bg-brass-soft/70 border border-brass/25 rounded-xl px-4 py-3">
                                <span class="font-bold text-brass">Harga Grosir Terbaik</span>
                                <span class="font-extrabold text-brass">Rp {{ number_format($lowestOffer['unit_price'], 0, ',', '.') }}<span class="font-medium">/{{ $product->unit }}</span></span>
                            </div>
                        @endif
                    </div>
                    <p class="text-[11px] text-muted mt-3">Harga estimasi — harga final mengikuti total quantity kelompok kulaan.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-6">
                        @auth
                            @if(auth()->user()->hasRole('umkm'))
                                <a href="{{ route('umkm.catalog.show', $product) }}" class="border border-forest text-forest font-bold px-5 py-3.5 rounded-xl hover:bg-forest/5 flex items-center justify-center gap-2 transition text-sm">
                                    + Keranjang UMKM
                                </a>
                                <a href="{{ route('umkm.catalog.show', $product) }}" class="bg-forest hover:bg-forest-dark text-white font-bold px-5 py-3.5 rounded-xl text-center transition text-sm">
                                    Beli Sekarang
                                </a>
                            @else
                                <a href="{{ route('dashboard') }}" class="border border-forest text-forest font-bold px-5 py-3.5 rounded-xl hover:bg-forest/5 text-center transition text-sm">
                                    Dashboard
                                </a>
                                <a href="{{ route('dashboard') }}" class="bg-forest hover:bg-forest-dark text-white font-bold px-5 py-3.5 rounded-xl text-center transition text-sm">
                                    Dashboard
                                </a>
                            @endif
                        @else
                            <button type="button" onclick="document.getElementById('gateModal').classList.remove('hidden')" class="border border-forest text-forest font-bold px-5 py-3.5 rounded-xl hover:bg-forest/5 flex items-center justify-center gap-2 cursor-pointer transition text-sm">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2.25 3h1.39c.51 0 .96.34 1.09.84l.38 1.44M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.22c1.12-2.3 2.1-4.68 2.92-7.14a60.11 60.11 0 0 0-16.54-1.84M7.5 14.25 5.11 5.27M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                                Tambah ke Keranjang
                            </button>
                            <button type="button" onclick="document.getElementById('gateModal').classList.remove('hidden')" class="bg-forest hover:bg-forest-dark text-white font-bold px-5 py-3.5 rounded-xl cursor-pointer transition text-sm">
                                Beli Sekarang
                            </button>
                        @endauth
                    </div>
                    <p class="text-[11px] text-muted text-center mt-3">Pembayaran aman via Midtrans · Transaksi terlindungi</p>
                </div>
            </div>
        </section>

        <!-- PENAWARAN DISTRIBUTOR -->
        <section class="mt-10">
            <h2 class="text-xl font-extrabold text-ink">Penawaran Distributor Mitra</h2>
            <p class="text-xs sm:text-sm text-muted mt-1">Daftar distributor yang menyediakan produk ini di wilayah Jember &amp; sekitarnya.</p>

            <div class="grid gap-4 mt-4">
                @forelse($offers as $offer)
                    @php $dist = $offer['distributor']; @endphp
                    <div class="bg-white border border-line rounded-3xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-extrabold text-ink text-base">{{ $dist->business_name }}</h3>
                                @if($dist->rating)
                                    <span class="inline-flex items-center gap-1 bg-brass-soft text-brass text-xs font-bold px-2 py-0.5 rounded-full">
                                        ★ {{ number_format((float)$dist->rating, 1) }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-muted mt-1">{{ $dist->subdistrict ?? 'Kaliwates' }}, {{ $dist->city ?? 'Jember' }}</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="text-right">
                                <p class="text-[11px] text-muted">Estimasi Harga Satuan</p>
                                <p class="text-base font-extrabold text-forest">Rp {{ number_format($offer['unit_price'], 0, ',', '.') }}</p>
                            </div>
                            @auth
                                @if(auth()->user()->hasRole('umkm'))
                                    <a href="{{ route('umkm.catalog.show', $product) }}" class="bg-forest text-white text-xs font-bold px-4 py-2.5 rounded-xl">Pilih</a>
                                @else
                                    <a href="{{ route('dashboard') }}" class="bg-forest text-white text-xs font-bold px-4 py-2.5 rounded-xl">Dashboard</a>
                                @endif
                            @else
                                <button type="button" onclick="document.getElementById('gateModal').classList.remove('hidden')" class="bg-forest hover:bg-forest-dark text-white text-xs font-bold px-4 py-2.5 rounded-xl cursor-pointer transition">
                                    Pilih Distributor
                                </button>
                            @endauth
                        </div>
                    </div>
                @empty
                    <div class="bg-white border border-line rounded-3xl p-8 text-center text-muted text-xs sm:text-sm">
                        Belum ada penawaran distributor aktif untuk produk ini.
                    </div>
                @endforelse
            </div>
        </section>

        <!-- BANNER TEASER SPK -->
        <section class="mt-10 bg-brass-soft border border-brass/25 rounded-[2rem] p-6 lg:p-8 flex flex-col md:flex-row md:items-center gap-5">
            <span class="w-14 h-14 shrink-0 rounded-2xl bg-white text-brass flex items-center justify-center">
                <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z"/></svg>
            </span>
            <div class="flex-1">
                <h2 class="font-extrabold text-lg text-ink">Bingung pilih distributor yang mana?</h2>
                <p class="text-sm text-ink/75 mt-1">Login sebagai UMKM untuk melihat rekomendasi SPK (AHP · TOPSIS · SAW) berdasarkan lokasi dan kebutuhanmu.</p>
            </div>
            <a href="{{ route('login') }}" class="bg-forest hover:bg-forest-dark text-white text-xs sm:text-sm font-bold px-6 py-3 rounded-xl shrink-0 text-center transition">Masuk untuk Lihat Rekomendasi</a>
        </section>

    </main>

    <x-modal-gate-login />
</x-layouts.public>
