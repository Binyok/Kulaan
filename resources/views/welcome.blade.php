<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Kula'an — Platform Pengadaan Kolektif UMKM</title>
    <meta name="description" content="Belanja bahan baku barengan, hemat sampai 28% dengan sistem rekomendasi SPK AHP + TOPSIS.">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <noscript><style>[data-reveal] { opacity: 1 !important; transform: none !important; }</style></noscript>
</head>
<body class="bg-cream text-ink min-h-screen font-sans">
    {{-- ============================ MOBILE MOCKUP (< md) ============================ --}}
    <div class="md:hidden overflow-x-clip">
        <!-- floating header -->
        <div class="px-3 pt-2 sticky top-0 z-20" x-data="{ menuOpen: false }">
            <div class="bg-white/90 backdrop-blur border border-black/[0.05] shadow rounded-2xl">
                <div class="h-14 px-2.5 flex items-center justify-between gap-2">
                    <span class="flex items-center gap-2 min-w-0">
                        <span class="w-8 h-8 shrink-0 rounded-xl bg-forest text-white flex items-center justify-center font-extrabold">K</span>
                        <span class="font-extrabold text-base tracking-tight truncate">Kula'an</span>
                    </span>
                    <div class="flex items-center gap-1.5 shrink-0">
                        @auth
                            <a href="{{ route('dashboard') }}" class="text-[0.6875rem] py-2 px-3 font-bold bg-forest text-white rounded-xl whitespace-nowrap">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-[0.6875rem] py-2 px-3 font-bold text-ink border border-line rounded-xl whitespace-nowrap">Masuk</a>
                            <a href="{{ route('register') }}" class="text-[0.6875rem] py-2 px-3 font-bold bg-forest text-white rounded-xl whitespace-nowrap">Daftar</a>
                        @endauth
                        <button type="button" @click="menuOpen = !menuOpen" class="w-8 h-8 shrink-0 inline-flex items-center justify-center rounded-xl text-ink hover:bg-black/[0.04]" :aria-expanded="menuOpen ? 'true' : 'false'" aria-label="Buka menu navigasi">
                            <svg x-show="!menuOpen" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/></svg>
                            <svg x-show="menuOpen" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
                <nav x-show="menuOpen" x-cloak class="border-t border-black/[0.05] p-2">
                    <a href="#cara-kerja-mobile" @click="menuOpen = false" class="block px-3 py-2.5 rounded-xl text-[0.8125rem] font-medium text-muted hover:bg-black/[0.03] hover:text-ink">Cara Kerja</a>
                    <a href="#kalkulator" @click="menuOpen = false" class="block px-3 py-2.5 rounded-xl text-[0.8125rem] font-medium text-muted hover:bg-black/[0.03] hover:text-ink">Simulasi Hemat</a>
                    <a href="#katalog-mobile" @click="menuOpen = false" class="block px-3 py-2.5 rounded-xl text-[0.8125rem] font-medium text-muted hover:bg-black/[0.03] hover:text-ink">Katalog Bahan</a>
                    <a href="#spk-mobile" @click="menuOpen = false" class="block px-3 py-2.5 rounded-xl text-[0.8125rem] font-medium text-muted hover:bg-black/[0.03] hover:text-ink">Metode SPK</a>
                </nav>
            </div>
        </div>

        <!-- hero -->
        <div class="px-4 pt-8 pb-10">
            <span class="eyebrow"><span class="w-1.5 h-1.5 rounded-full bg-success"></span>Smart Collective Procurement Hub</span>
            <h1 class="font-extrabold tracking-tight text-[2rem] leading-[1.12] mt-4">Beli bahan baku barengan, dapatkan <span class="text-forest">harga grosir</span> tanpa minimum order besar.</h1>
            <p class="text-sm text-muted leading-relaxed mt-4">Kula'an menggabungkan kebutuhan bahan baku UMKM di sekitar Anda dan mencocokkan kuantitas otomatis — dengan rekomendasi distributor <strong class="font-bold text-ink">AHP + TOPSIS</strong>.</p>
            <div class="flex flex-col gap-2.5 pt-6">
                <a href="{{ auth()->check() ? route('dashboard') : route('register') }}" class="bg-forest text-white text-sm font-bold py-3.5 px-6 rounded-2xl text-center shadow-lg">Mulai Pengadaan Kolektif</a>
                <a href="#kalkulator" class="border border-line bg-white text-sm font-bold py-3.5 px-6 rounded-2xl text-center">Hitung Penghematan</a>
            </div>
            <dl class="grid grid-cols-3 gap-3 pt-7 mt-7 border-t border-forest/10">
                <div><dd class="font-extrabold text-ink text-xl leading-none">{{ number_format($stats['total_umkm'] ?? 0, 0, ',', '.') }}</dd><dt class="text-[0.625rem] text-muted mt-1 font-medium">UMKM Bergabung</dt></div>
                <div><dd class="font-extrabold text-ink text-xl leading-none">{{ number_format($stats['total_distributor'] ?? 0, 0, ',', '.') }}</dd><dt class="text-[0.625rem] text-muted mt-1 font-medium">Distributor Terpilih</dt></div>
                <div><dd class="font-extrabold text-brass text-xl leading-none">Hingga 28%</dd><dt class="text-[0.625rem] text-muted mt-1 font-medium">Efisiensi Biaya</dt></div>
            </dl>
        </div>

        <!-- snapshot card -->
        <div class="px-4 pb-10">
            <div class="bg-white rounded-3xl border border-line shadow-sm p-5">
                <div class="flex justify-between items-center gap-2 pb-4 border-b border-black/[0.05]">
                    <div>
                        <p class="text-[0.625rem] font-bold text-muted uppercase tracking-[0.12em]">Simulasi Grup Matching</p>
                        <p class="font-extrabold text-ink mt-1 leading-snug">{{ $products->first()->name ?? 'Beras Ramos Premium IR64' }}</p>
                    </div>
                    <span class="text-[0.625rem] font-bold bg-forest/10 text-forest px-2.5 py-1.5 rounded-full shrink-0">Kuota Tercapai</span>
                </div>
                <div class="py-4">
                    <div class="flex justify-between text-[0.6875rem] font-bold text-ink"><span>Terkumpul: 10 kg</span><span class="text-muted font-medium">MOQ: 10 kg</span></div>
                    <div class="w-full h-2.5 bg-forest/10 rounded-full overflow-hidden mt-2"><div class="h-full w-full bg-forest rounded-full"></div></div>
                </div>
                <div class="space-y-2">
                    <div class="p-2.5 bg-cream rounded-xl flex justify-between items-center gap-2 text-[0.6875rem]">
                        <div><p class="font-bold text-ink">Warung Berkah</p><p class="text-muted">4 kg &bull; Rp 26.000</p></div>
                        <span class="text-brass font-bold shrink-0">Hemat 10rb</span>
                    </div>
                    <div class="p-2.5 bg-cream rounded-xl flex justify-between items-center gap-2 text-[0.6875rem]">
                        <div><p class="font-bold text-ink">Katering Barokah</p><p class="text-muted">3 kg &bull; Rp 19.500</p></div>
                        <span class="text-brass font-bold shrink-0">Hemat 7,5rb</span>
                    </div>
                </div>
                <div class="mt-4 p-3.5 bg-brass-soft rounded-2xl border border-brass/20 flex justify-between items-center gap-2">
                    <p class="text-[0.625rem] font-bold text-brass uppercase tracking-[0.1em]">Harga Grosir Diterapkan</p>
                    <p class="font-extrabold text-forest shrink-0">Rp 6.500<span class="text-[0.625rem] font-normal text-muted">/kg</span></p>
                </div>
            </div>
        </div>

        <!-- kalkulator -->
        <div id="kalkulator" class="px-4 pb-10" x-data="{
            selectedProduct: 'beras',
            qty: 4,
            products: {
                'beras': { name: 'Beras Ramos Premium', unit: 'kg', retail: 9000, wholesale: 6500 },
                'minyak': { name: 'Minyak Goreng Sawit', unit: 'liter', retail: 17500, wholesale: 14800 },
                'tepung': { name: 'Tepung Terigu', unit: 'kg', retail: 12500, wholesale: 9800 },
                'gula': { name: 'Gula Pasir', unit: 'kg', retail: 18000, wholesale: 15200 }
            },
            get current() { return this.products[this.selectedProduct]; },
            get normalCost() { return this.qty * this.current.retail; },
            get collectiveCost() { return this.qty * this.current.wholesale; },
            get savings() { return this.normalCost - this.collectiveCost; },
            get savingsPercent() { return Math.round((this.savings / this.normalCost) * 100); },
            rb(v) { return Math.round(v / 1000) + 'rb'; }
        }">
            <span class="eyebrow">Kalkulator Interaktif</span>
            <h2 class="font-extrabold tracking-tight text-2xl leading-[1.15] mt-3">Hitung estimasi penghematan usaha Anda</h2>
            <div class="bg-white rounded-3xl border border-line shadow-sm mt-5 p-5">
                <div class="grid grid-cols-2 gap-2">
                    <template x-for="(item, key) in products" :key="key">
                        <button type="button" @click="selectedProduct = key"
                            :class="selectedProduct === key ? 'border-forest bg-forest/10 text-forest font-bold' : 'border-line text-muted font-medium'"
                            class="border rounded-xl px-2 py-2.5 text-[0.6875rem]"><span x-text="item.name"></span></button>
                    </template>
                </div>
                <div class="flex justify-between items-end mt-5">
                    <span class="text-[0.6875rem] font-bold text-ink">Kuantitas</span>
                    <span class="font-extrabold text-forest text-xl"><span x-text="qty"></span> <span x-text="current.unit"></span></span>
                </div>
                <input type="range" min="1" max="50" x-model.number="qty" class="w-full h-10">
                <div class="grid grid-cols-3 gap-2 mt-3">
                    <div class="p-3 bg-cream rounded-xl"><p class="text-[0.5625rem] text-muted font-medium">Eceran</p><p class="font-extrabold text-ink text-sm mt-0.5" x-text="'Rp ' + rb(normalCost)"></p></div>
                    <div class="p-3 bg-forest/10 rounded-xl"><p class="text-[0.5625rem] text-forest font-bold">Grosir</p><p class="font-extrabold text-forest text-sm mt-0.5" x-text="'Rp ' + rb(collectiveCost)"></p></div>
                    <div class="p-3 bg-brass-soft rounded-xl"><p class="text-[0.5625rem] text-brass font-bold" x-text="'Hemat ' + savingsPercent + '%'"></p><p class="font-extrabold text-brass text-sm mt-0.5" x-text="'Rp ' + rb(savings)"></p></div>
                </div>
            </div>
        </div>

        <!-- cara kerja -->
        <div id="cara-kerja-mobile" class="px-4 pb-10">
            <span class="eyebrow">Alur Sistem</span>
            <h2 class="font-extrabold tracking-tight text-2xl leading-[1.15] mt-3">Bagaimana Kula'an bekerja</h2>
            <ol class="mt-5 border-t border-forest/15">
                <li class="flex gap-4 py-5 border-b border-forest/15">
                    <span class="font-extrabold text-forest/30 text-3xl leading-none shrink-0">01</span>
                    <div><h3 class="font-bold text-ink">Pilih di Katalog</h3><p class="text-xs text-muted mt-1 leading-relaxed">Bandingkan penawaran distributor, ajukan jumlah dan deadline.</p></div>
                </li>
                <li class="flex gap-4 py-5 border-b border-forest/15">
                    <span class="font-extrabold text-forest/30 text-3xl leading-none shrink-0">02</span>
                    <div><h3 class="font-bold text-ink">Auto-Matching Otomatis</h3><p class="text-xs text-muted mt-1 leading-relaxed">Sistem menggabungkan pesanan UMKM sekitar hingga mencapai MOQ grosir.</p></div>
                </li>
                <li class="flex gap-4 py-5 border-b border-forest/15">
                    <span class="font-extrabold text-forest/30 text-3xl leading-none shrink-0">03</span>
                    <div><h3 class="font-bold text-ink">Rekomendasi SPK</h3><p class="text-xs text-muted mt-1 leading-relaxed">AHP + TOPSIS menilai harga, kualitas, dan lead time — keputusan tetap di tanganmu.</p></div>
                </li>
                <li class="flex gap-4 py-5 border-b border-forest/15">
                    <span class="font-extrabold text-forest/30 text-3xl leading-none shrink-0">04</span>
                    <div><h3 class="font-bold text-ink">Bayar &amp; Lacak GPS</h3><p class="text-xs text-muted mt-1 leading-relaxed">Invoice individual, bayar via QRIS/VA, lacak kurir realtime di peta.</p></div>
                </li>
            </ol>
        </div>

        <!-- katalog preview -->
        <div id="katalog-mobile" class="px-4 pb-10">
            <span class="eyebrow">Katalog Pilihan</span>
            <h2 class="font-extrabold tracking-tight text-2xl leading-[1.15] mt-3">Bahan baku siap pengadaan</h2>
            <div class="grid grid-cols-2 gap-3 mt-5">
                @foreach ($products->take(2) as $product)
                    <div class="bg-white rounded-2xl border border-line p-4">
                        <p class="text-[0.5625rem] font-bold text-forest uppercase tracking-wider">{{ $product->category->name }}</p>
                        <h3 class="font-bold text-sm text-ink mt-1 leading-snug">{{ $product->name }}</h3>
                        <p class="text-[0.625rem] text-muted line-through mt-2">Rp {{ number_format($product->benchmark_retail_price, 0, ',', '.') }}</p>
                        <p class="font-extrabold text-brass">Rp {{ number_format($product->benchmark_retail_price * 0.75, 0, ',', '.') }}</p>
                    </div>
                @endforeach
            </div>
            <a href="{{ auth()->check() ? route('dashboard') : route('register') }}" class="block text-center text-xs font-bold bg-forest text-white rounded-2xl py-3.5 mt-4">Daftar untuk Memesan</a>
        </div>

        <!-- SPK panel -->
        <div id="spk-mobile" class="px-4 pb-10">
            <div class="texture-grain bg-forest rounded-3xl p-6">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-[0.5625rem] font-bold bg-white/10 text-white uppercase tracking-[0.14em]">Metodologi Multi-Kriteria</span>
                <h2 class="font-extrabold text-white tracking-tight text-2xl leading-[1.15] mt-4">Keputusan adil &amp; transparan melalui AHP + TOPSIS</h2>
                <p class="text-xs text-white/75 leading-relaxed mt-3">AHP menetapkan bobot matematis untuk Harga, Kualitas, Kecepatan Pengiriman, Jarak, dan MOQ. TOPSIS mengukur kedekatan tiap distributor terhadap solusi ideal.</p>
                <a href="{{ auth()->check() ? route('dashboard') : route('register') }}" class="block text-center bg-brass text-white text-xs font-bold py-3 px-5 rounded-2xl mt-5">Gabung Ekosistem Kula'an</a>
            </div>
        </div>

        <!-- footer -->
        <footer class="bg-forest text-white/70 px-5 py-8 mt-2">
            <div class="flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-white/10 text-white flex items-center justify-center font-extrabold text-sm">K</span>
                <span class="font-extrabold text-white">Kula'an</span>
            </div>
            <p class="text-[0.6875rem] mt-3">Smart Collective Procurement &copy; {{ date('Y') }}</p>
            <p class="text-[0.6875rem] mt-1">Sidoarjo, Jawa Timur &middot; Hak Cipta Dilindungi</p>
        </footer>
    </div>

    {{-- ============================ DESKTOP MOCKUP (>= md) ============================ --}}
    <div class="hidden md:flex md:flex-col md:min-h-screen">
        <!-- Floating Island Header -->
        <header class="sticky top-0 z-30 pt-3 px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="bg-white/85 backdrop-blur-xl border border-black/[0.05] shadow rounded-2xl h-16 px-6 flex items-center justify-between">
                    <div class="flex items-center gap-8">
                        <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                            <span class="w-9 h-9 rounded-xl bg-forest text-white flex items-center justify-center font-extrabold text-lg">K</span>
                            <span class="font-extrabold text-[1.375rem] tracking-tight">Kula'an</span>
                        </a>
                        <nav class="hidden md:flex items-center gap-6 text-[0.8125rem] font-medium text-muted">
                            <a href="#cara-kerja" class="hover:text-ink">Cara Kerja</a>
                            <a href="#kalkulator" class="hover:text-ink">Simulasi Hemat</a>
                            <a href="#katalog" class="hover:text-ink">Katalog Bahan</a>
                            <a href="#spk" class="hover:text-ink">Metode SPK</a>
                        </nav>
                    </div>
                    <div class="flex items-center gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}" class="text-xs py-2.5 px-4 font-bold bg-forest text-white rounded-xl shadow-sm hover:bg-forest-dark">Buka Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-xs py-2.5 px-4 font-bold text-ink border border-line rounded-xl hover:bg-black/[0.03]">Masuk</a>
                            <a href="{{ route('register') }}" class="text-xs py-2.5 px-4 font-bold bg-forest text-white rounded-xl shadow-sm hover:bg-forest-dark">Daftar Sekarang</a>
                        @endauth
                    </div>
                </div>
            </div>
        </header>

        <!-- Hero -->
        <section class="pt-14 pb-16 sm:pt-20 sm:pb-24 px-6 lg:px-8">
            <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-center">
                <div class="lg:col-span-7">
                    <span class="eyebrow"><span class="w-1.5 h-1.5 rounded-full bg-success"></span>Smart Collective Procurement Hub</span>
                    <h1 class="font-extrabold tracking-tight text-[2.5rem] sm:text-6xl leading-[1.08] mt-5">Beli bahan baku barengan, dapatkan <span class="text-forest">harga grosir</span> tanpa minimum order besar.</h1>
                    <p class="text-base sm:text-lg text-muted max-w-xl leading-relaxed mt-5">Kula'an menggabungkan kebutuhan bahan baku pelaku UMKM di sekitar Anda, mencocokkan kuantitas secara otomatis, dan Anda yang memilih distributor terbaik berbekal rekomendasi sistem pendukung keputusan <strong class="font-bold text-ink">AHP + TOPSIS</strong>.</p>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-7">
                        <a href="{{ auth()->check() ? route('dashboard') : route('register') }}" class="bg-forest text-white text-sm font-bold py-3.5 px-7 rounded-2xl text-center shadow-lg hover:bg-forest-dark">Mulai Pengadaan Kolektif</a>
                        <a href="#kalkulator" class="border border-line bg-white text-sm font-bold py-3.5 px-6 rounded-2xl text-center hover:border-forest/40">Hitung Penghematan</a>
                    </div>
                    <dl class="grid grid-cols-3 gap-4 pt-8 mt-8 border-t border-forest/10">
                        <div class="flex flex-col">
                            <dt class="order-2 text-[0.6875rem] text-muted mt-1.5 font-medium">UMKM Bergabung</dt>
                            <dd class="order-1 font-extrabold text-ink text-2xl sm:text-[2rem] leading-none">{{ number_format($stats['total_umkm'] ?? 0, 0, ',', '.') }}</dd>
                        </div>
                        <div class="flex flex-col">
                            <dt class="order-2 text-[0.6875rem] text-muted mt-1.5 font-medium">Distributor Terpilih</dt>
                            <dd class="order-1 font-extrabold text-ink text-2xl sm:text-[2rem] leading-none">{{ number_format($stats['total_distributor'] ?? 0, 0, ',', '.') }}</dd>
                        </div>
                        <div class="flex flex-col">
                            <dt class="order-2 text-[0.6875rem] text-muted mt-1.5 font-medium">Efisiensi Biaya</dt>
                            <dd class="order-1 font-extrabold text-brass text-2xl sm:text-[2rem] leading-none">Hingga 28%</dd>
                        </div>
                    </dl>
                </div>

                <!-- Collective snapshot card -->
                <div class="lg:col-span-5">
                    <div class="bg-white rounded-3xl border border-line shadow-sm p-6 sm:p-7">
                        <div class="flex justify-between items-center gap-3 pb-4 border-b border-black/[0.05]">
                            <div>
                                <p class="text-[0.6875rem] font-bold text-muted uppercase tracking-[0.12em]">Simulasi Grup Matching</p>
                                <p class="font-extrabold text-ink text-lg mt-1 leading-snug">{{ $products->first()->name ?? 'Beras Ramos Premium IR64' }}</p>
                            </div>
                            <span class="text-[0.6875rem] font-bold bg-forest/10 text-forest px-3 py-1.5 rounded-full shrink-0">Kuota Grosir Tercapai</span>
                        </div>
                        <div class="py-5">
                            <div class="flex justify-between text-xs font-bold text-ink">
                                <span>Terkumpul: 10 kg</span>
                                <span class="text-muted font-medium">Target MOQ: 10 kg</span>
                            </div>
                            <div class="w-full h-2.5 bg-forest/10 rounded-full overflow-hidden mt-2">
                                <div class="h-full w-full bg-forest rounded-full"></div>
                            </div>
                        </div>
                        <div class="space-y-2.5">
                            <div class="p-3 bg-cream rounded-xl flex justify-between items-center gap-3 text-xs">
                                <div><p class="font-bold text-ink">Warung Berkah</p><p class="text-[0.6875rem] text-muted">Kebutuhan: 4 kg &bull; Bayar Rp 26.000</p></div>
                                <span class="text-brass font-bold shrink-0">Hemat Rp 10.000</span>
                            </div>
                            <div class="p-3 bg-cream rounded-xl flex justify-between items-center gap-3 text-xs">
                                <div><p class="font-bold text-ink">Katering Barokah</p><p class="text-[0.6875rem] text-muted">Kebutuhan: 3 kg &bull; Bayar Rp 19.500</p></div>
                                <span class="text-brass font-bold shrink-0">Hemat Rp 7.500</span>
                            </div>
                            <div class="p-3 bg-cream rounded-xl flex justify-between items-center gap-3 text-xs">
                                <div><p class="font-bold text-ink">Dapur Sejahtera</p><p class="text-[0.6875rem] text-muted">Kebutuhan: 3 kg &bull; Bayar Rp 19.500</p></div>
                                <span class="text-brass font-bold shrink-0">Hemat Rp 7.500</span>
                            </div>
                        </div>
                        <div class="mt-5 p-4 bg-brass-soft rounded-2xl border border-brass/20 flex justify-between items-center gap-3">
                            <div>
                                <p class="text-[0.6875rem] font-bold text-brass uppercase tracking-[0.12em]">Harga Grosir Diterapkan</p>
                                <p class="text-xs text-muted mt-0.5">Dari distributor peringkat #1 AHP-TOPSIS</p>
                            </div>
                            <p class="text-lg font-extrabold text-forest shrink-0">Rp 6.500 <span class="text-xs font-normal text-muted">/ kg</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Savings calculator -->
        <section id="kalkulator" class="py-16 sm:py-20 px-6 lg:px-8 border-t border-forest/10" x-data="{
            selectedProduct: 'beras',
            qty: 4,
            products: {
                'beras': { name: 'Beras Ramos Premium', unit: 'kg', retail: 9000, wholesale: 6500 },
                'minyak': { name: 'Minyak Goreng Sawit', unit: 'liter', retail: 17500, wholesale: 14800 },
                'tepung': { name: 'Tepung Terigu', unit: 'kg', retail: 12500, wholesale: 9800 },
                'gula': { name: 'Gula Pasir Kristal', unit: 'kg', retail: 18000, wholesale: 15200 }
            },
            get current() { return this.products[this.selectedProduct]; },
            get normalCost() { return this.qty * this.current.retail; },
            get collectiveCost() { return this.qty * this.current.wholesale; },
            get savings() { return this.normalCost - this.collectiveCost; },
            get savingsPercent() { return Math.round((this.savings / this.normalCost) * 100); },
            idr(v) { return v.toLocaleString('id-ID'); }
        }">
            <div class="max-w-4xl mx-auto">
                <div class="max-w-lg">
                    <span class="eyebrow">Kalkulator Interaktif</span>
                    <h2 class="font-extrabold tracking-tight text-3xl sm:text-[2.75rem] leading-[1.1] mt-4">Hitung estimasi penghematan usaha Anda</h2>
                    <p class="text-sm text-muted mt-3 leading-relaxed">Geser kuantitas kebutuhan harian atau mingguan untuk melihat selisih harga grosir kolektif.</p>
                </div>
                <div class="bg-white rounded-3xl border border-line shadow-sm mt-8 p-6 sm:p-9">
                    <div>
                        <span class="text-xs font-bold text-ink block mb-2.5">Pilih bahan baku</span>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                            <template x-for="(item, key) in products" :key="key">
                                <button type="button" @click="selectedProduct = key"
                                    :class="selectedProduct === key ? 'border-forest bg-forest/10 text-forest font-bold' : 'border-line bg-white text-muted hover:border-forest/40'"
                                    class="min-h-11 border rounded-xl px-3 py-2.5 text-xs font-medium cursor-pointer"><span x-text="item.name"></span></button>
                            </template>
                        </div>
                    </div>
                    <div class="pt-7 mt-7 border-t border-black/[0.05]">
                        <div class="flex justify-between items-end gap-3">
                            <label class="text-xs font-bold text-ink">Kuantitas kebutuhan Anda</label>
                            <span class="font-extrabold text-forest text-2xl leading-none"><span x-text="qty"></span> <span class="text-base" x-text="current.unit"></span></span>
                        </div>
                        <input type="range" min="1" max="50" x-model.number="qty" class="w-full h-11 cursor-pointer">
                        <div class="flex justify-between text-[0.6875rem] text-muted"><span>1 kg</span><span>25 kg</span><span>50 kg</span></div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-7 mt-2 border-t border-black/[0.05]">
                        <div class="p-4 bg-cream rounded-2xl">
                            <p class="text-[0.6875rem] text-muted font-medium">Beli Eceran Sendiri</p>
                            <p class="font-extrabold text-ink text-[1.375rem] mt-1">Rp <span x-text="idr(normalCost)"></span></p>
                            <p class="text-[0.6875rem] text-muted mt-0.5">Rp <span x-text="idr(current.retail)"></span> / <span x-text="current.unit"></span></p>
                        </div>
                        <div class="p-4 bg-forest/10 rounded-2xl border border-forest/10">
                            <p class="text-[0.6875rem] text-forest font-bold">Harga Grosir Kula'an</p>
                            <p class="font-extrabold text-forest text-[1.375rem] mt-1">Rp <span x-text="idr(collectiveCost)"></span></p>
                            <p class="text-[0.6875rem] text-muted mt-0.5">Rp <span x-text="idr(current.wholesale)"></span> / <span x-text="current.unit"></span></p>
                        </div>
                        <div class="p-4 bg-brass-soft rounded-2xl border border-brass/20">
                            <p class="text-[0.6875rem] text-brass font-bold uppercase tracking-[0.1em]">Total Hemat (<span x-text="savingsPercent"></span>%)</p>
                            <p class="font-extrabold text-brass text-[1.375rem] mt-1">Rp <span x-text="idr(savings)"></span></p>
                            <p class="text-[0.6875rem] text-muted mt-0.5">Langsung hemat per pengadaan</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- How it works -->
        <section id="cara-kerja" class="py-16 sm:py-24 px-6 lg:px-8 border-t border-forest/10">
            <div class="max-w-7xl mx-auto">
                <div class="max-w-xl">
                    <span class="eyebrow">Alur Sistem</span>
                    <h2 class="font-extrabold tracking-tight text-3xl sm:text-[2.75rem] leading-[1.1] mt-4">Bagaimana Kula'an bekerja</h2>
                    <p class="text-sm text-muted mt-3 leading-relaxed">Pengadaan otomatis yang menyederhanakan pembelian bahan baku, dari pengajuan sampai tiba di tempat usaha.</p>
                </div>
                <ol class="mt-10 border-t border-forest/15">
                    <li class="grid grid-cols-[auto_1fr] sm:grid-cols-[7rem_1fr_1fr] gap-x-5 gap-y-2 py-7 border-b border-forest/15 items-baseline">
                        <span class="font-extrabold text-forest/30 text-4xl sm:text-5xl leading-none">01</span>
                        <h3 class="font-bold text-lg text-ink">Pilih di Katalog</h3>
                        <p class="col-start-2 sm:col-start-3 text-sm text-muted leading-relaxed">Buka katalog, bandingkan penawaran distributor, dan ajukan ke pilihanmu beserta jumlah dan deadline.</p>
                    </li>
                    <li class="grid grid-cols-[auto_1fr] sm:grid-cols-[7rem_1fr_1fr] gap-x-5 gap-y-2 py-7 border-b border-forest/15 items-baseline">
                        <span class="font-extrabold text-forest/30 text-4xl sm:text-5xl leading-none">02</span>
                        <h3 class="font-bold text-lg text-ink">Auto-Matching Otomatis</h3>
                        <p class="col-start-2 sm:col-start-3 text-sm text-muted leading-relaxed">Sistem menggabungkan pesanan UMKM lain di sekitar area pengiriman hingga mencapai MOQ grosir.</p>
                    </li>
                    <li class="grid grid-cols-[auto_1fr] sm:grid-cols-[7rem_1fr_1fr] gap-x-5 gap-y-2 py-7 border-b border-forest/15 items-baseline">
                        <span class="font-extrabold text-forest/30 text-4xl sm:text-5xl leading-none">03</span>
                        <h3 class="font-bold text-lg text-ink">Rekomendasi SPK</h3>
                        <p class="col-start-2 sm:col-start-3 text-sm text-muted leading-relaxed">Metode AHP dan TOPSIS menilai harga, rating kualitas, dan lead time sebagai bahan rekomendasi — keputusan akhir tetap di tanganmu.</p>
                    </li>
                    <li class="grid grid-cols-[auto_1fr] sm:grid-cols-[7rem_1fr_1fr] gap-x-5 gap-y-2 py-7 border-b border-forest/15 items-baseline">
                        <span class="font-extrabold text-forest/30 text-4xl sm:text-5xl leading-none">04</span>
                        <h3 class="font-bold text-lg text-ink">Bayar &amp; Lacak GPS</h3>
                        <p class="col-start-2 sm:col-start-3 text-sm text-muted leading-relaxed">Setiap UMKM menerima invoice individual, bayar via QRIS/VA, dan melacak posisi kurir realtime di peta.</p>
                    </li>
                </ol>
            </div>
        </section>

        <!-- Catalog -->
        <section id="katalog" class="py-16 sm:py-20 px-6 lg:px-8 border-t border-forest/10">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
                    <div class="max-w-xl">
                        <span class="eyebrow">Katalog Pilihan</span>
                        <h2 class="font-extrabold tracking-tight text-3xl sm:text-[2.75rem] leading-[1.1] mt-4">Bahan baku siap pengadaan</h2>
                        <p class="text-[0.8125rem] text-muted mt-2">Komoditas pokok standar dengan spesifikasi mutu terjamin.</p>
                    </div>
                    <a href="{{ auth()->check() ? route('dashboard') : route('register') }}" class="text-xs py-2.5 px-5 font-bold bg-forest text-white rounded-xl shrink-0">Daftar untuk Memesan</a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mt-8">
                    @foreach ($products->take(4) as $product)
                        <div class="bg-white rounded-3xl border border-line shadow-sm p-5 flex flex-col h-full">
                            <p class="text-[0.6875rem] font-bold text-forest uppercase tracking-[0.12em]">{{ $product->category->name }}</p>
                            <h3 class="font-extrabold text-xl text-ink mt-1.5 leading-snug">{{ $product->name }}</h3>
                            <p class="text-[0.6875rem] text-muted mt-1.5 leading-relaxed">{{ $product->standard_specification ?? 'Spesifikasi mutu terjamin untuk kebutuhan usaha.' }}</p>
                            <div class="pt-4 mt-auto border-t border-black/[0.05] space-y-1.5">
                                <div class="flex justify-between text-xs"><span class="text-muted">Patokan Eceran</span><span class="line-through text-muted">Rp {{ number_format($product->benchmark_retail_price, 0, ',', '.') }}</span></div>
                                <div class="flex justify-between items-baseline"><span class="text-xs font-bold text-forest">Estimasi Kolektif</span><span class="font-extrabold text-lg text-brass">Rp {{ number_format($product->benchmark_retail_price * 0.75, 0, ',', '.') }}</span></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- SPK panel -->
        <section id="spk" class="px-6 lg:px-8 pb-16 sm:pb-24 pt-4">
            <div class="max-w-7xl mx-auto">
                <div class="texture-grain bg-forest rounded-[1.75rem] px-6 py-12 sm:p-14">
                    <div class="max-w-2xl">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[0.625rem] font-bold bg-white/10 text-white uppercase tracking-[0.14em]">Metodologi Multi-Kriteria</span>
                        <h2 class="font-extrabold text-white tracking-tight text-3xl sm:text-[2.75rem] leading-[1.1] mt-5">Keputusan adil &amp; transparan melalui AHP + TOPSIS</h2>
                        <p class="text-sm text-white/75 leading-relaxed mt-4">Sistem tidak memilih distributor secara acak. Metode <strong class="font-bold text-white">AHP (Analytical Hierarchy Process)</strong> menetapkan bobot matematis untuk kriteria Harga, Kualitas, Kecepatan Pengiriman, Jarak, dan MOQ. Selanjutnya, <strong class="font-bold text-white">TOPSIS</strong> mengukur kedekatan setiap distributor terhadap solusi ideal untuk rekomendasi terbaik bagi seluruh UMKM.</p>
                        <div class="pt-7">
                            <a href="{{ auth()->check() ? route('dashboard') : route('register') }}" class="inline-block bg-brass text-white text-sm font-bold py-3 px-6 rounded-2xl shadow-md hover:brightness-110">Gabung Ekosistem Kula'an</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-forest text-white/70 mt-auto">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 py-10 flex flex-col sm:flex-row justify-between sm:items-center gap-4 text-xs">
                <div class="flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-lg bg-white/10 text-white flex items-center justify-center font-extrabold text-sm">K</span>
                    <span class="font-extrabold text-white text-base">Kula'an</span>
                    <span>&middot; Smart Collective Procurement &copy; {{ date('Y') }}</span>
                </div>
                <p>Sidoarjo, Jawa Timur &middot; Hak Cipta Dilindungi</p>
            </div>
        </footer>
    </div>
</body>
</html>
