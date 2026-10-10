<x-layouts.app>
    <x-slot name="title">Dashboard Administrator</x-slot>

    <div class="space-y-8">
        <div data-reveal>
            <span class="eyebrow">Kontrol Pusat</span>
            <h1 class="font-display font-semibold text-[1.75rem] sm:text-4xl text-brand-dark tracking-tight text-balance mt-2">Dashboard Administrator</h1>
            <p class="text-sm text-brand-slate mt-1">Monitoring pengadaan kolektif, kriteria SPK, dan tata kelola ekosistem Kula'an.</p>
        </div>

        <!-- Metrics Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
            <div class="card-shell">
                <div class="card-core p-5">
                    <p class="metric-label">Total UMKM</p>
                    <p class="metric-value text-brand-dark mt-2">{{ $totalUmkm }}</p>
                    <p class="text-[0.6875rem] text-brand-primary font-medium mt-1">Terdaftar aktif</p>
                </div>
            </div>
            <div class="card-shell">
                <div class="card-core p-5">
                    <p class="metric-label">Distributor Rekanan</p>
                    <p class="metric-value text-brand-dark mt-2">{{ $totalDistributors }}</p>
                    <p class="text-[0.6875rem] text-brand-primary font-medium mt-1">Penyedia terverifikasi</p>
                </div>
            </div>
            <div class="card-shell">
                <div class="card-core p-5">
                    <p class="metric-label">Armada Kurir</p>
                    <p class="metric-value text-brand-dark mt-2">{{ $totalCouriers }}</p>
                    <p class="text-[0.6875rem] text-brand-slate mt-1">Pengantaran aktif</p>
                </div>
            </div>
            <div class="card-shell">
                <div class="card-core p-5 savings-card">
                    <p class="metric-label text-brand-brass">Total Hemat UMKM</p>
                    <p class="text-xl sm:text-2xl font-extrabold text-brand-brass mt-2">Rp {{ number_format($totalSavingsSystem, 0, ',', '.') }}</p>
                    <p class="text-[0.6875rem] text-brand-slate mt-1">Efisiensi biaya pengadaan</p>
                </div>
            </div>
        </div>

        <!-- Action Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <a href="{{ route('admin.categories.index') }}" class="card-shell group block">
                <div class="card-core p-6 transition-all duration-300 ease-[cubic-bezier(0.32,0.72,0,1)] group-hover:-translate-y-1">
                    <span class="w-10 h-10 rounded-xl bg-brand-mint text-brand-primary flex items-center justify-center font-bold text-sm mb-4">01</span>
                    <h3 class="font-bold text-base text-brand-dark group-hover:text-brand-primary transition-colors">Master Kategori</h3>
                    <p class="text-[0.75rem] text-brand-slate mt-1">Kelola kategori produk baku dan spesifikasi standar mutu.</p>
                </div>
            </a>
            <a href="{{ route('admin.products.index') }}" class="card-shell group block">
                <div class="card-core p-6 transition-all duration-300 ease-[cubic-bezier(0.32,0.72,0,1)] group-hover:-translate-y-1">
                    <span class="w-10 h-10 rounded-xl bg-brand-mint text-brand-primary flex items-center justify-center font-bold text-sm mb-4">02</span>
                    <h3 class="font-bold text-base text-brand-dark group-hover:text-brand-primary transition-colors">Master Produk Baku</h3>
                    <p class="text-[0.75rem] text-brand-slate mt-1">Daftar produk acuan, harga benchmark pasar, dan unit standar.</p>
                </div>
            </a>
            <a href="{{ route('admin.spk.index') }}" class="card-shell group block">
                <div class="card-core p-6 transition-all duration-300 ease-[cubic-bezier(0.32,0.72,0,1)] group-hover:-translate-y-1">
                    <span class="w-10 h-10 rounded-xl bg-brand-ochre-soft text-brand-brass flex items-center justify-center font-bold text-sm mb-4">03</span>
                    <h3 class="font-bold text-base text-brand-dark group-hover:text-brand-brass transition-colors">Kriteria SPK (AHP)</h3>
                    <p class="text-[0.75rem] text-brand-slate mt-1">Konfigurasi bobot perbandingan berpasangan dan uji konsistensi.</p>
                </div>
            </a>
        </div>
    </div>
</x-layouts.app>
