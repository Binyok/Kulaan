<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Kula'an &mdash; Platform Smart Collective Procurement UMKM</title>
    <meta name="description" content="Platform pengadaan bahan baku kolektif otomatis berbasis Sistem Pendukung Keputusan AHP dan TOPSIS untuk UMKM di Kalimantan Selatan.">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-[100dvh] flex flex-col font-sans" style="background-color: var(--color-brand-cream);">
    <!-- Fluid Floating Island Header -->
    <header class="sticky top-0 z-30 pt-3 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white/80 backdrop-blur-xl border border-black/[0.05] shadow-[0_2px_16px_-4px_rgba(0,0,0,0.06)] rounded-2xl h-16 px-4 sm:px-6 flex items-center justify-between transition-all duration-500 ease-[cubic-bezier(0.32,0.72,0,1)]">
                <div class="flex items-center gap-8">
                    <a href="/" class="flex items-center gap-2.5 group">
                        <span class="w-9 h-9 rounded-xl bg-brand-primary text-white flex items-center justify-center font-bold text-base shadow-[0_2px_8px_-1px_rgba(45,106,79,0.3)] transition-all duration-500 ease-[cubic-bezier(0.32,0.72,0,1)] group-hover:scale-105">K</span>
                        <span class="text-lg font-bold text-brand-dark tracking-tight">Kula'an</span>
                    </a>

                    <nav class="hidden md:flex items-center gap-6 text-[0.8125rem] font-medium text-brand-slate">
                        <a href="#cara-kerja" class="hover:text-brand-dark transition-colors">Cara Kerja</a>
                        <a href="#kalkulator" class="hover:text-brand-dark transition-colors">Simulasi Hemat</a>
                        <a href="#katalog" class="hover:text-brand-dark transition-colors">Katalog Bahan</a>
                        <a href="#spk" class="hover:text-brand-dark transition-colors">Metode SPK</a>
                    </nav>
                </div>

                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn-primary text-xs py-2 px-4 font-semibold">
                            Buka Dashboard &rarr;
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn-ghost text-xs py-2 px-4 font-semibold text-brand-dark">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="btn-primary text-xs py-2 px-4 font-semibold shadow-sm">
                            Daftar Sekarang
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section: Soft Structuralism with Depth -->
    <section class="py-16 sm:py-24 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                <div class="lg:col-span-7 space-y-6 animate-fade-up">
                    <span class="eyebrow">
                        <span class="w-1.5 h-1.5 rounded-full bg-brand-emerald animate-pulse"></span>
                        Smart Collective Procurement Hub
                    </span>

                    <h1 class="text-3xl sm:text-5xl font-extrabold text-brand-dark tracking-tight leading-[1.15]">
                        Beli Bahan Baku Barengan, <span class="text-brand-primary">Dapatkan Harga Grosir</span> Tanpa Minimum Order Besar.
                    </h1>

                    <p class="text-base sm:text-lg text-brand-slate max-w-xl leading-relaxed">
                        Kula'an menggabungkan kebutuhan bahan baku dari pelaku UMKM di area sekitar, mencocokkan kuantitas secara otomatis, dan memilih distributor terbaik menggunakan sistem pendukung keputusan <strong>AHP + TOPSIS</strong>.
                    </p>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-2">
                        <a href="{{ route('register') }}" class="btn-primary text-sm py-3.5 px-7 justify-center shadow-lg shadow-brand-primary/20">
                            Mulai Pengadaan Kolektif &rarr;
                        </a>
                        <a href="#kalkulator" class="btn-ghost text-sm py-3.5 px-6 justify-center">
                            Hitung Penghematan
                        </a>
                    </div>

                    <!-- Trust Stats Bar -->
                    <div class="grid grid-cols-3 gap-4 pt-6 border-t border-black/[0.04]">
                        <div>
                            <p class="text-xl sm:text-2xl font-extrabold text-brand-dark">{{ $stats['total_umkm'] }}</p>
                            <p class="text-[0.6875rem] text-brand-slate mt-0.5 font-medium">UMKM Bergabung</p>
                        </div>
                        <div>
                            <p class="text-xl sm:text-2xl font-extrabold text-brand-dark">{{ $stats['total_distributor'] }}</p>
                            <p class="text-[0.6875rem] text-brand-slate mt-0.5 font-medium">Distributor Terpilih</p>
                        </div>
                        <div>
                            <p class="text-xl sm:text-2xl font-extrabold text-brand-accent">Hingga 28%</p>
                            <p class="text-[0.6875rem] text-brand-slate mt-0.5 font-medium">Efisiensi Biaya</p>
                        </div>
                    </div>
                </div>

                <!-- Visual Interactive Card: Collective Procurement Snapshot -->
                <div class="lg:col-span-5 animate-fade-up">
                    <div class="card-shell">
                        <div class="card-core p-6 sm:p-7 space-y-6">
                            <div class="flex justify-between items-center pb-4 border-b border-black/[0.04]">
                                <div>
                                    <span class="text-[0.6875rem] font-bold text-brand-slate uppercase tracking-wider">Simulasi Grup Matching</span>
                                    <p class="font-bold text-brand-dark text-sm mt-0.5">Beras Ramos Premium IR64</p>
                                </div>
                                <span class="badge bg-brand-mint text-brand-emerald">
                                    <span class="w-1.5 h-1.5 rounded-full bg-brand-emerald animate-pulse"></span>
                                    Kuota Grosir Tercapai
                                </span>
                            </div>

                            <!-- Progress Bar Aggregate -->
                            <div class="space-y-2">
                                <div class="flex justify-between text-xs font-semibold text-brand-dark">
                                    <span>Terkumpul: 10 kg</span>
                                    <span class="text-brand-slate">Target MOQ: 10 kg</span>
                                </div>
                                <div class="w-full h-3 bg-brand-cream rounded-full overflow-hidden p-0.5 border border-black/[0.04]">
                                    <div class="h-full bg-brand-primary rounded-full transition-all duration-700 w-full"></div>
                                </div>
                            </div>

                            <!-- UMKM Contributors List -->
                            <div class="space-y-2.5">
                                <div class="p-3 bg-brand-cream/60 rounded-xl flex justify-between items-center text-xs">
                                    <div>
                                        <p class="font-bold text-brand-dark">Warung Berkah</p>
                                        <p class="text-[0.6875rem] text-brand-slate">Kebutuhan: 4 kg &bull; Bayar Rp 26.000</p>
                                    </div>
                                    <span class="text-brand-accent font-bold">Hemat Rp 10.000</span>
                                </div>
                                <div class="p-3 bg-brand-cream/60 rounded-xl flex justify-between items-center text-xs">
                                    <div>
                                        <p class="font-bold text-brand-dark">Katering Barokah</p>
                                        <p class="text-[0.6875rem] text-brand-slate">Kebutuhan: 3 kg &bull; Bayar Rp 19.500</p>
                                    </div>
                                    <span class="text-brand-accent font-bold">Hemat Rp 7.500</span>
                                </div>
                                <div class="p-3 bg-brand-cream/60 rounded-xl flex justify-between items-center text-xs">
                                    <div>
                                        <p class="font-bold text-brand-dark">Dapur Sejahtera</p>
                                        <p class="text-[0.6875rem] text-brand-slate">Kebutuhan: 3 kg &bull; Bayar Rp 19.500</p>
                                    </div>
                                    <span class="text-brand-accent font-bold">Hemat Rp 7.500</span>
                                </div>
                            </div>

                            <div class="p-4 savings-card flex justify-between items-center">
                                <div>
                                    <p class="text-[0.6875rem] font-bold text-brand-accent uppercase tracking-wider">Harga Grosir Diterapkan</p>
                                    <p class="text-xs text-brand-slate">Dari distributor peringkat #1 AHP-TOPSIS</p>
                                </div>
                                <p class="text-base font-extrabold text-brand-primary">Rp 6.500 <span class="text-xs font-normal text-brand-slate">/ kg</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Interactive Savings Calculator -->
    <section id="kalkulator" class="py-16 px-4 sm:px-6 lg:px-8 border-t border-black/[0.04]" x-data="{
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
        <div class="max-w-4xl mx-auto space-y-8">
            <div class="text-center space-y-2">
                <span class="eyebrow">Kalkulator Interaktif</span>
                <h2 class="text-2xl sm:text-4xl font-extrabold text-brand-dark tracking-tight">Hitung Estimasi Penghematan Usaha Anda</h2>
                <p class="text-sm text-brand-slate max-w-lg mx-auto">Geser kuantitas kebutuhan harian atau mingguan Anda untuk melihat selisih harga grosir kolektif.</p>
            </div>

            <div class="card-shell">
                <div class="card-core p-6 sm:p-9 space-y-8">
                    <!-- Commodity Selector Pills -->
                    <div>
                        <label class="block text-xs font-bold text-brand-dark uppercase tracking-wider mb-3">Pilih Bahan Baku</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                            <template x-for="(item, key) in products" :key="key">
                                <button type="button" @click="selectedProduct = key"
                                    :class="selectedProduct === key ? 'border-brand-primary bg-brand-mint/60 text-brand-primary font-bold shadow-sm' : 'border-black/[0.06] bg-white text-brand-slate hover:border-black/[0.12] font-medium'"
                                    class="border rounded-xl p-3 text-center text-xs transition-all duration-300 cursor-pointer">
                                    <span x-text="item.name"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Quantity Range Slider -->
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <label class="text-xs font-bold text-brand-dark uppercase tracking-wider">Kuantitas Kebutuhan Anda</label>
                            <span class="font-extrabold text-brand-primary text-lg">
                                <span x-text="qty"></span> <span x-text="current.unit"></span>
                            </span>
                        </div>
                        <input type="range" min="1" max="50" step="1" x-model.number="qty"
                            class="w-full accent-[#2D6A4F] h-2 bg-brand-cream rounded-lg cursor-pointer">
                        <div class="flex justify-between text-[0.6875rem] text-brand-slate">
                            <span>1 <span x-text="current.unit"></span></span>
                            <span>25 <span x-text="current.unit"></span></span>
                            <span>50 <span x-text="current.unit"></span></span>
                        </div>
                    </div>

                    <!-- Result Breakdown Card -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 border-t border-black/[0.04]">
                        <div class="p-4 bg-brand-cream/60 rounded-xl">
                            <p class="text-[0.6875rem] text-brand-slate font-medium">Beli Eceran Sendiri</p>
                            <p class="text-lg font-bold text-brand-dark mt-1">Rp <span x-text="normalCost.toLocaleString('id-ID')"></span></p>
                            <p class="text-[0.6875rem] text-brand-slate mt-0.5">Rp <span x-text="current.retail.toLocaleString('id-ID')"></span> / <span x-text="current.unit"></span></p>
                        </div>
                        <div class="p-4 bg-brand-mint/60 rounded-xl border border-brand-primary/10">
                            <p class="text-[0.6875rem] text-brand-primary font-bold">Harga Grosir Kula'an</p>
                            <p class="text-lg font-extrabold text-brand-primary mt-1">Rp <span x-text="collectiveCost.toLocaleString('id-ID')"></span></p>
                            <p class="text-[0.6875rem] text-brand-slate mt-0.5">Rp <span x-text="current.wholesale.toLocaleString('id-ID')"></span> / <span x-text="current.unit"></span></p>
                        </div>
                        <div class="p-4 savings-card">
                            <p class="text-[0.6875rem] text-brand-accent font-bold uppercase tracking-wider">Total Hemat (<span x-text="savingsPercent"></span>%)</p>
                            <p class="text-xl font-extrabold text-brand-accent mt-1">Rp <span x-text="savings.toLocaleString('id-ID')"></span></p>
                            <p class="text-[0.6875rem] text-brand-slate mt-0.5">Langsung hemat per pengadaan</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How it Works: Step by Step Architecture -->
    <section id="cara-kerja" class="py-16 sm:py-24 px-4 sm:px-6 lg:px-8 border-t border-black/[0.04]">
        <div class="max-w-7xl mx-auto space-y-12">
            <div class="text-center space-y-2">
                <span class="eyebrow">Alur Sistem</span>
                <h2 class="text-2xl sm:text-4xl font-extrabold text-brand-dark tracking-tight">Bagaimana Kula'an Bekerja</h2>
                <p class="text-sm text-brand-slate max-w-lg mx-auto">Pengadaan otomatis yang menyederhanakan proses pembelian bahan baku UMKM dari awal hingga sampai di tempat.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="card-shell">
                    <div class="card-core p-6 space-y-3">
                        <span class="w-8 h-8 rounded-lg bg-brand-mint text-brand-primary font-extrabold text-sm flex items-center justify-center">1</span>
                        <h3 class="font-bold text-base text-brand-dark">Ajukan Kebutuhan</h3>
                        <p class="text-xs text-brand-slate leading-relaxed">Tentukan jenis produk bahan baku, jumlah kilogram/liter, dan batas maksimal menunggu (deadline).</p>
                    </div>
                </div>

                <div class="card-shell">
                    <div class="card-core p-6 space-y-3">
                        <span class="w-8 h-8 rounded-lg bg-brand-mint text-brand-primary font-extrabold text-sm flex items-center justify-center">2</span>
                        <h3 class="font-bold text-base text-brand-dark">Auto-Matching Otomatis</h3>
                        <p class="text-xs text-brand-slate leading-relaxed">Sistem menggabungkan pesanan UMKM lain di sekitar area pengiriman hingga kuantitas mencapai MOQ grosir.</p>
                    </div>
                </div>

                <div class="card-shell">
                    <div class="card-core p-6 space-y-3">
                        <span class="w-8 h-8 rounded-lg bg-brand-ochre-soft text-brand-accent font-extrabold text-sm flex items-center justify-center">3</span>
                        <h3 class="font-bold text-base text-brand-dark">Rekomendasi SPK</h3>
                        <p class="text-xs text-brand-slate leading-relaxed">Metode AHP dan TOPSIS mengevaluasi harga, kualitas rating, dan lead time untuk memilih distributor terbaik.</p>
                    </div>
                </div>

                <div class="card-shell">
                    <div class="card-core p-6 space-y-3">
                        <span class="w-8 h-8 rounded-lg bg-brand-mint text-brand-primary font-extrabold text-sm flex items-center justify-center">4</span>
                        <h3 class="font-bold text-base text-brand-dark">Bayar & Lacak GPS</h3>
                        <p class="text-xs text-brand-slate leading-relaxed">Masing-masing UMKM menerima invoice individual, bayar via QRIS/VA, dan lacak posisi kurir di peta secara realtime.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Catalog Preview Section -->
    <section id="katalog" class="py-16 px-4 sm:px-6 lg:px-8 border-t border-black/[0.04]">
        <div class="max-w-7xl mx-auto space-y-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
                <div>
                    <span class="eyebrow">Katalog Pilihan</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-brand-dark tracking-tight mt-1.5">Bahan Baku Siap Pengadaan</h2>
                    <p class="text-[0.8125rem] text-brand-slate mt-1">Katalog standar komoditas pokok dengan spesifikasi mutu terjamin.</p>
                </div>
                <a href="{{ route('register') }}" class="btn-primary text-xs py-2.5 px-5 font-semibold">
                    Daftar untuk Memesan &rarr;
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach ($products as $product)
                    <div class="card-shell">
                        <div class="card-core p-5 space-y-4 flex flex-col justify-between h-full">
                            <div>
                                <span class="badge bg-brand-mint text-brand-primary text-[10px]">
                                    {{ $product->category->name }}
                                </span>
                                <h3 class="font-bold text-base text-brand-dark mt-2">{{ $product->name }}</h3>
                                <p class="text-[0.6875rem] text-brand-slate mt-1 line-clamp-2">{{ $product->standard_specification ?? 'Standar mutu terjamin.' }}</p>
                            </div>

                            <div class="pt-3 border-t border-black/[0.04] space-y-1">
                                <div class="flex justify-between text-xs">
                                    <span class="text-brand-slate">Patokan Eceran:</span>
                                    <span class="line-through text-brand-slate">Rp {{ number_format($product->benchmark_retail_price, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between items-center text-sm font-extrabold">
                                    <span class="text-brand-primary">Estimasi Kolektif:</span>
                                    <span class="text-brand-accent">Rp {{ number_format($product->benchmark_retail_price * 0.75, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- SPK Decision Method Explanation -->
    <section id="spk" class="py-16 sm:py-24 px-4 sm:px-6 lg:px-8 border-t border-black/[0.04]">
        <div class="max-w-7xl mx-auto">
            <div class="hero-gradient text-white p-8 sm:p-12 shadow-[0_16px_48px_-16px_rgba(45,106,79,0.35)]">
                <div class="relative z-10 max-w-2xl space-y-4">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[0.625rem] font-semibold bg-white/10 text-white/90 backdrop-blur-md uppercase tracking-wider">
                        Metodologi Multi-Kriteria
                    </span>
                    <h2 class="text-2xl sm:text-4xl font-extrabold tracking-tight">Keputusan Adil & Transparan Melalui AHP + TOPSIS</h2>
                    <p class="text-sm text-white/80 leading-relaxed">
                        Sistem tidak memilih distributor secara acak. Metode <strong>AHP (Analytical Hierarchy Process)</strong> menetapkan bobot matematis untuk kriteria Harga, Kualitas, Kecepatan Pengiriman, Jarak, dan MOQ. Selanjutnya, <strong>TOPSIS</strong> mengukur kedekatan setiap distributor terhadap solusi ideal untuk memberikan rekomendasi terbaik bagi seluruh UMKM.
                    </p>
                    <div class="pt-4">
                        <a href="{{ route('register') }}" class="btn-accent text-sm py-3 px-6 shadow-md shadow-black/10">
                            Gabung Ekosistem Kula'an &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-black/[0.04] bg-white py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-6 text-xs text-brand-slate">
            <div class="flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-lg bg-brand-primary text-white flex items-center justify-center font-bold text-xs">K</span>
                <span class="font-bold text-brand-dark text-sm">Kula'an</span>
                <span>&bull; Smart Collective Procurement Platform &copy; {{ date('Y') }}</span>
            </div>
            <p>Banjarmasin, Kalimantan Selatan &bull; Hak Cipta Dilindungi</p>
        </div>
    </footer>
</body>
</html>
