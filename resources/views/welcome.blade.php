<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Kula'an &mdash; Platform Smart Collective Procurement UMKM</title>
    <meta name="description" content="Platform pengadaan bahan baku kolektif otomatis berbasis Sistem Pendukung Keputusan AHP dan TOPSIS untuk UMKM di Kalimantan Selatan.">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <noscript><style>[data-reveal] { opacity: 1 !important; transform: none !important; }</style></noscript>
</head>
<body class="min-h-[100dvh] flex flex-col font-sans" style="background-color: var(--color-brand-cream);">
    <!-- Floating Island Header -->
    <header class="sticky top-0 z-30 pt-3 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white/85 backdrop-blur-xl border border-black/[0.05] shadow-[0_2px_16px_-4px_rgba(27,67,50,0.10)] rounded-2xl h-16 px-4 sm:px-6 flex items-center justify-between">
                <div class="flex items-center gap-8">
                    <a href="/" class="flex items-center gap-2.5 group" aria-label="Kula'an beranda">
                        <span class="w-9 h-9 rounded-xl bg-brand-primary text-white flex items-center justify-center font-display font-semibold text-lg shadow-[0_2px_8px_-1px_rgba(45,106,79,0.35)] transition-transform duration-300 group-hover:scale-105">K</span>
                        <span class="font-display font-semibold text-[1.375rem] text-brand-dark tracking-tight">Kula'an</span>
                    </a>

                    <nav class="hidden md:flex items-center gap-6 text-[0.8125rem] font-medium text-brand-slate" aria-label="Navigasi utama">
                        <a href="#cara-kerja" class="hover:text-brand-dark transition-colors">Cara Kerja</a>
                        <a href="#kalkulator" class="hover:text-brand-dark transition-colors">Simulasi Hemat</a>
                        <a href="#katalog" class="hover:text-brand-dark transition-colors">Katalog Bahan</a>
                        <a href="#spk" class="hover:text-brand-dark transition-colors">Metode SPK</a>
                    </nav>
                </div>

                <div class="flex items-center gap-2.5 sm:gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn-primary text-xs py-2.5 px-4 font-semibold">
                            Buka Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn-ghost text-xs py-2.5 px-4 font-semibold text-brand-dark hidden sm:inline-flex">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="btn-primary text-xs py-2.5 px-4 font-semibold shadow-sm">
                            Daftar Sekarang
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Hero -->
    <section class="pt-14 pb-16 sm:pt-20 sm:pb-24 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-center">
                <div class="lg:col-span-7" data-reveal>
                    <span class="eyebrow">
                        <span class="w-1.5 h-1.5 rounded-full bg-brand-emerald"></span>
                        Smart Collective Procurement Hub
                    </span>

                    <h1 class="font-display font-semibold text-brand-dark tracking-tight text-balance leading-[1.08] text-[2.5rem] sm:text-6xl mt-5">
                        Beli bahan baku barengan, dapatkan <span class="text-brand-primary">harga grosir</span> tanpa minimum order besar.
                    </h1>

                    <p class="text-base sm:text-lg text-brand-slate max-w-xl leading-relaxed mt-5">
                        Kula'an menggabungkan kebutuhan bahan baku pelaku UMKM di sekitar Anda,
                        mencocokkan kuantitas secara otomatis, dan memilih distributor terbaik
                        dengan sistem pendukung keputusan <strong class="font-semibold text-brand-dark">AHP + TOPSIS</strong>.
                    </p>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-7">
                        <a href="{{ route('register') }}" class="btn-primary text-sm py-3.5 px-7 justify-center shadow-lg shadow-brand-primary/20">
                            Mulai Pengadaan Kolektif
                        </a>
                        <a href="#kalkulator" class="btn-ghost text-sm py-3.5 px-6 justify-center">
                            Hitung Penghematan
                        </a>
                    </div>

                    <!-- Trust stats -->
                    <dl class="grid grid-cols-3 gap-4 pt-8 mt-8 border-t border-brand-primary/10">
                        <div class="flex flex-col">
                            <dt class="order-2 text-[0.6875rem] text-brand-slate mt-1.5 font-medium">UMKM Bergabung</dt>
                            <dd class="order-1 font-display font-semibold text-brand-dark text-2xl sm:text-[2rem] leading-none tnum">{{ $stats['total_umkm'] }}</dd>
                        </div>
                        <div class="flex flex-col">
                            <dt class="order-2 text-[0.6875rem] text-brand-slate mt-1.5 font-medium">Distributor Terpilih</dt>
                            <dd class="order-1 font-display font-semibold text-brand-dark text-2xl sm:text-[2rem] leading-none tnum">{{ $stats['total_distributor'] }}</dd>
                        </div>
                        <div class="flex flex-col">
                            <dt class="order-2 text-[0.6875rem] text-brand-slate mt-1.5 font-medium">Efisiensi Biaya</dt>
                            <dd class="order-1 font-display font-semibold text-brand-brass text-2xl sm:text-[2rem] leading-none">Hingga 28%</dd>
                        </div>
                    </dl>
                </div>

                <!-- Collective snapshot card -->
                <div class="lg:col-span-5" data-reveal style="--reveal-delay: 120ms;">
                    <div class="card-shell">
                        <div class="card-core p-6 sm:p-7">
                            <div class="flex justify-between items-center gap-3 pb-4 border-b border-black/[0.05]">
                                <div>
                                    <p class="text-[0.6875rem] font-bold text-brand-slate uppercase tracking-[0.12em]">Simulasi Grup Matching</p>
                                    <p class="font-display font-semibold text-brand-dark text-lg mt-1 leading-snug">Beras Ramos Premium IR64</p>
                                </div>
                                <span class="badge bg-brand-mint text-brand-emerald shrink-0">
                                    Kuota Grosir Tercapai
                                </span>
                            </div>

                            <div class="py-5">
                                <div class="flex justify-between text-xs font-semibold text-brand-dark">
                                    <span class="tnum">Terkumpul: 10 kg</span>
                                    <span class="text-brand-slate tnum">Target MOQ: 10 kg</span>
                                </div>
                                <div class="w-full h-2.5 bg-brand-mint rounded-full overflow-hidden mt-2">
                                    <div class="h-full w-full bg-brand-primary rounded-full"></div>
                                </div>
                            </div>

                            <div class="space-y-2.5">
                                <div class="p-3 bg-brand-cream rounded-xl flex justify-between items-center gap-3 text-xs">
                                    <div class="min-w-0">
                                        <p class="font-bold text-brand-dark">Warung Berkah</p>
                                        <p class="text-[0.6875rem] text-brand-slate tnum">Kebutuhan: 4 kg &bull; Bayar Rp 26.000</p>
                                    </div>
                                    <span class="text-brand-brass font-bold tnum shrink-0">Hemat Rp 10.000</span>
                                </div>
                                <div class="p-3 bg-brand-cream rounded-xl flex justify-between items-center gap-3 text-xs">
                                    <div class="min-w-0">
                                        <p class="font-bold text-brand-dark">Katering Barokah</p>
                                        <p class="text-[0.6875rem] text-brand-slate tnum">Kebutuhan: 3 kg &bull; Bayar Rp 19.500</p>
                                    </div>
                                    <span class="text-brand-brass font-bold tnum shrink-0">Hemat Rp 7.500</span>
                                </div>
                                <div class="p-3 bg-brand-cream rounded-xl flex justify-between items-center gap-3 text-xs">
                                    <div class="min-w-0">
                                        <p class="font-bold text-brand-dark">Dapur Sejahtera</p>
                                        <p class="text-[0.6875rem] text-brand-slate tnum">Kebutuhan: 3 kg &bull; Bayar Rp 19.500</p>
                                    </div>
                                    <span class="text-brand-brass font-bold tnum shrink-0">Hemat Rp 7.500</span>
                                </div>
                            </div>

                            <div class="mt-5 p-4 savings-card flex justify-between items-center gap-3">
                                <div>
                                    <p class="text-[0.6875rem] font-bold text-brand-brass uppercase tracking-[0.12em]">Harga Grosir Diterapkan</p>
                                    <p class="text-xs text-brand-slate mt-0.5">Dari distributor peringkat #1 AHP-TOPSIS</p>
                                </div>
                                <p class="text-lg font-extrabold text-brand-primary tnum shrink-0">Rp 6.500 <span class="text-xs font-normal text-brand-slate">/ kg</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Savings calculator -->
    <section id="kalkulator" class="py-16 sm:py-20 px-4 sm:px-6 lg:px-8 border-t border-brand-primary/10 scroll-mt-24" x-data="{
        selectedProduct: 'beras',
        qty: 4,
        products: {
            'beras': { name: 'Beras Ramos Premium', unit: 'kg', retail: 9000, wholesale: 6500 },
            'minyak': { name: 'Minyak Goreng Sawit Kemasan', unit: 'liter', retail: 17500, wholesale: 14800 },
            'tepung': { name: 'Tepung Terigu Serbaguna', unit: 'kg', retail: 12500, wholesale: 9800 },
            'gula': { name: 'Gula Pasir Kristal Putih', unit: 'kg', retail: 18000, wholesale: 15200 }
        },
        get current() {
            return this.products[this.selectedProduct];
        },
        get normalCost() {
            return this.qty * this.current.retail;
        },
        get collectiveCost() {
            return this.qty * this.current.wholesale;
        },
        get savings() {
            return this.normalCost - this.collectiveCost;
        },
        get savingsPercent() {
            return Math.round((this.savings / this.normalCost) * 100);
        }
    }">
        <div class="max-w-4xl mx-auto">
            <div class="max-w-lg" data-reveal>
                <span class="eyebrow">Kalkulator Interaktif</span>
                <h2 class="font-display font-semibold text-brand-dark tracking-tight text-balance text-3xl sm:text-[2.75rem] leading-[1.1] mt-4">Hitung estimasi penghematan usaha Anda</h2>
                <p class="text-sm text-brand-slate mt-3 leading-relaxed">Geser kuantitas kebutuhan harian atau mingguan untuk melihat selisih harga grosir kolektif.</p>
            </div>

            <div class="card-shell mt-8" data-reveal>
                <div class="card-core p-6 sm:p-9">
                    <div>
                        <span class="field-label">Pilih bahan baku</span>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                            <template x-for="(item, key) in products" :key="key">
                                <button type="button" @click="selectedProduct = key"
                                    :class="selectedProduct === key ? 'border-brand-primary bg-brand-mint/60 text-brand-primary font-bold shadow-sm' : 'border-black/[0.06] bg-white text-brand-slate hover:border-black/[0.12] font-medium'"
                                    class="min-h-11 inline-flex items-center justify-center border rounded-xl px-3 py-2.5 text-center text-xs transition-colors cursor-pointer">
                                    <span x-text="item.name"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div class="pt-7 mt-7 border-t border-black/[0.05]">
                        <div class="flex justify-between items-end gap-3">
                            <label for="calc-qty" class="field-label mb-0">Kuantitas kebutuhan Anda</label>
                            <span class="font-display font-semibold text-brand-primary text-2xl leading-none tnum">
                                <span x-text="qty"></span> <span x-text="current.unit" class="text-base"></span>
                            </span>
                        </div>
                        <input id="calc-qty" type="range" min="1" max="50" step="1" x-model.number="qty"
                            class="w-full h-11 accent-[#2D6A4F] cursor-pointer" aria-label="Kuantitas kebutuhan">
                        <div class="flex justify-between text-[0.6875rem] text-brand-slate tnum">
                            <span>1 <span x-text="current.unit"></span></span>
                            <span>25 <span x-text="current.unit"></span></span>
                            <span>50 <span x-text="current.unit"></span></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-7 mt-2 border-t border-black/[0.05]">
                        <div class="p-4 bg-brand-cream rounded-2xl">
                            <p class="text-[0.6875rem] text-brand-slate font-medium">Beli Eceran Sendiri</p>
                            <p class="font-display font-semibold text-brand-dark text-[1.375rem] mt-1 tnum">Rp <span x-text="normalCost.toLocaleString('id-ID')"></span></p>
                            <p class="text-[0.6875rem] text-brand-slate mt-0.5 tnum">Rp <span x-text="current.retail.toLocaleString('id-ID')"></span> / <span x-text="current.unit"></span></p>
                        </div>
                        <div class="p-4 bg-brand-mint/70 rounded-2xl border border-brand-primary/10">
                            <p class="text-[0.6875rem] text-brand-primary font-bold">Harga Grosir Kula'an</p>
                            <p class="font-display font-semibold text-brand-primary text-[1.375rem] mt-1 tnum">Rp <span x-text="collectiveCost.toLocaleString('id-ID')"></span></p>
                            <p class="text-[0.6875rem] text-brand-slate mt-0.5 tnum">Rp <span x-text="current.wholesale.toLocaleString('id-ID')"></span> / <span x-text="current.unit"></span></p>
                        </div>
                        <div class="p-4 savings-card">
                            <p class="text-[0.6875rem] text-brand-brass font-bold uppercase tracking-[0.1em]">Total Hemat (<span x-text="savingsPercent"></span>%)</p>
                            <p class="font-display font-semibold text-brand-brass text-[1.375rem] mt-1 tnum">Rp <span x-text="savings.toLocaleString('id-ID')"></span></p>
                            <p class="text-[0.6875rem] text-brand-slate mt-0.5">Langsung hemat per pengadaan</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How it works: editorial index -->
    <section id="cara-kerja" class="py-16 sm:py-24 px-4 sm:px-6 lg:px-8 border-t border-brand-primary/10 scroll-mt-24">
        <div class="max-w-7xl mx-auto">
            <div class="max-w-xl" data-reveal>
                <span class="eyebrow">Alur Sistem</span>
                <h2 class="font-display font-semibold text-brand-dark tracking-tight text-balance text-3xl sm:text-[2.75rem] leading-[1.1] mt-4">Bagaimana Kula'an bekerja</h2>
                <p class="text-sm text-brand-slate mt-3 leading-relaxed">Pengadaan otomatis yang menyederhanakan pembelian bahan baku, dari pengajuan sampai tiba di tempat usaha.</p>
            </div>

            <ol class="mt-10 border-t border-brand-primary/15">
                <li class="grid grid-cols-[auto_1fr] sm:grid-cols-[7rem_1fr_1fr] gap-x-5 gap-y-2 py-7 border-b border-brand-primary/15 items-baseline" data-reveal>
                    <span class="font-display font-semibold text-brand-primary/30 text-4xl sm:text-5xl leading-none tnum">01</span>
                    <h3 class="font-bold text-lg text-brand-dark">Ajukan Kebutuhan</h3>
                    <p class="col-start-2 sm:col-start-3 text-sm text-brand-slate leading-relaxed">Tentukan jenis bahan baku, jumlah kilogram/liter, dan batas maksimal menunggu (deadline).</p>
                </li>
                <li class="grid grid-cols-[auto_1fr] sm:grid-cols-[7rem_1fr_1fr] gap-x-5 gap-y-2 py-7 border-b border-brand-primary/15 items-baseline" data-reveal>
                    <span class="font-display font-semibold text-brand-primary/30 text-4xl sm:text-5xl leading-none tnum">02</span>
                    <h3 class="font-bold text-lg text-brand-dark">Auto-Matching Otomatis</h3>
                    <p class="col-start-2 sm:col-start-3 text-sm text-brand-slate leading-relaxed">Sistem menggabungkan pesanan UMKM lain di sekitar area pengiriman hingga mencapai MOQ grosir.</p>
                </li>
                <li class="grid grid-cols-[auto_1fr] sm:grid-cols-[7rem_1fr_1fr] gap-x-5 gap-y-2 py-7 border-b border-brand-primary/15 items-baseline" data-reveal>
                    <span class="font-display font-semibold text-brand-primary/30 text-4xl sm:text-5xl leading-none tnum">03</span>
                    <h3 class="font-bold text-lg text-brand-dark">Rekomendasi SPK</h3>
                    <p class="col-start-2 sm:col-start-3 text-sm text-brand-slate leading-relaxed">Metode AHP dan TOPSIS menilai harga, rating kualitas, dan lead time untuk memilih distributor terbaik.</p>
                </li>
                <li class="grid grid-cols-[auto_1fr] sm:grid-cols-[7rem_1fr_1fr] gap-x-5 gap-y-2 py-7 border-b border-brand-primary/15 items-baseline" data-reveal>
                    <span class="font-display font-semibold text-brand-primary/30 text-4xl sm:text-5xl leading-none tnum">04</span>
                    <h3 class="font-bold text-lg text-brand-dark">Bayar &amp; Lacak GPS</h3>
                    <p class="col-start-2 sm:col-start-3 text-sm text-brand-slate leading-relaxed">Setiap UMKM menerima invoice individual, bayar via QRIS/VA, dan melacak posisi kurir realtime di peta.</p>
                </li>
            </ol>
        </div>
    </section>

    <!-- Catalog -->
    <section id="katalog" class="py-16 sm:py-20 px-4 sm:px-6 lg:px-8 border-t border-brand-primary/10 scroll-mt-24">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4" data-reveal>
                <div class="max-w-xl">
                    <span class="eyebrow">Katalog Pilihan</span>
                    <h2 class="font-display font-semibold text-brand-dark tracking-tight text-balance text-3xl sm:text-[2.75rem] leading-[1.1] mt-4">Bahan baku siap pengadaan</h2>
                    <p class="text-[0.8125rem] text-brand-slate mt-2">Komoditas pokok standar dengan spesifikasi mutu terjamin.</p>
                </div>
                <a href="{{ route('register') }}" class="btn-primary text-xs py-2.5 px-5 font-semibold w-full sm:w-auto justify-center shrink-0">
                    Daftar untuk Memesan
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mt-8">
                @foreach ($products as $product)
                    <div class="card-shell" data-reveal style="--reveal-delay: {{ $loop->index * 60 }}ms;">
                        <div class="card-core p-5 flex flex-col h-full">
                            <p class="text-[0.6875rem] font-bold text-brand-primary uppercase tracking-[0.12em]">{{ $product->category->name }}</p>
                            <h3 class="font-display font-semibold text-xl text-brand-dark mt-1.5 leading-snug">{{ $product->name }}</h3>
                            <p class="text-[0.6875rem] text-brand-slate mt-1.5 leading-relaxed line-clamp-2">{{ $product->standard_specification ?? 'Standar mutu terjamin.' }}</p>

                            <div class="pt-4 mt-auto border-t border-black/[0.05] space-y-1.5">
                                <div class="flex justify-between text-xs tnum">
                                    <span class="text-brand-slate">Patokan Eceran</span>
                                    <span class="line-through text-brand-slate">Rp {{ number_format($product->benchmark_retail_price, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between items-baseline tnum">
                                    <span class="text-xs font-semibold text-brand-primary">Estimasi Kolektif</span>
                                    <span class="font-display font-semibold text-lg text-brand-brass">Rp {{ number_format($product->benchmark_retail_price * 0.75, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- SPK methodology: forest panel -->
    <section id="spk" class="px-4 sm:px-6 lg:px-8 pb-16 sm:pb-24 pt-4 scroll-mt-24">
        <div class="max-w-7xl mx-auto">
            <div class="texture-grain bg-brand-forest rounded-[1.75rem] px-6 py-12 sm:p-14 shadow-[0_24px_64px_-24px_rgba(18,53,42,0.5)]" data-reveal>
                <div class="max-w-2xl">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[0.625rem] font-semibold bg-white/10 text-[#D8E8DD] uppercase tracking-[0.14em]">
                        Metodologi Multi-Kriteria
                    </span>
                    <h2 class="font-display font-semibold text-white tracking-tight text-balance text-3xl sm:text-[2.75rem] leading-[1.1] mt-5">Keputusan adil &amp; transparan melalui AHP + TOPSIS</h2>
                    <p class="text-sm text-white/75 leading-relaxed mt-4">
                        Sistem tidak memilih distributor secara acak. Metode
                        <strong class="font-semibold text-white">AHP (Analytical Hierarchy Process)</strong>
                        menetapkan bobot matematis untuk kriteria Harga, Kualitas, Kecepatan Pengiriman,
                        Jarak, dan MOQ. Selanjutnya,
                        <strong class="font-semibold text-white">TOPSIS</strong> mengukur kedekatan setiap
                        distributor terhadap solusi ideal untuk rekomendasi terbaik bagi seluruh UMKM.
                    </p>
                    <div class="pt-7">
                        <a href="{{ route('register') }}" class="btn-accent text-sm py-3 px-6 shadow-md shadow-black/20 w-full sm:w-auto justify-center">
                            Gabung Ekosistem Kula'an
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-brand-forest text-white/70 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex flex-col sm:flex-row justify-between sm:items-center gap-4 text-xs">
            <div class="flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-lg bg-white/10 text-white flex items-center justify-center font-display font-semibold text-sm">K</span>
                <span class="font-display font-semibold text-white text-base">Kula'an</span>
                <span>&middot; Smart Collective Procurement &copy; {{ date('Y') }}</span>
            </div>
            <p>Banjarmasin, Kalimantan Selatan &middot; Hak Cipta Dilindungi</p>
        </div>
    </footer>
</body>
</html>
