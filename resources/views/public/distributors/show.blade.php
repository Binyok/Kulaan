<x-layouts.public :title="$distributor->company_name">
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

        <!-- BREADCRUMB -->
        <nav class="text-xs sm:text-sm text-muted flex items-center gap-2">
            <a href="{{ route('home') }}" class="hover:text-ink">Beranda</a>
            <span>/</span>
            <a href="{{ route('public.distributors.map') }}" class="font-semibold text-forest hover:underline">Peta Distributor</a>
            <span>/</span>
            <span class="font-semibold text-ink">{{ $distributor->company_name }}</span>
        </nav>

        <!-- HEADER PROFIL -->
        <section class="mt-5 bg-white border border-line rounded-3xl p-6 sm:p-8 shadow-xs">
            <div class="flex flex-wrap items-start gap-6">
                @php $initials = strtoupper(substr($distributor->company_name, 0, 2)); @endphp
                <span class="w-20 h-20 sm:w-24 sm:h-24 rounded-3xl bg-forest text-white text-2xl sm:text-3xl font-extrabold flex items-center justify-center shrink-0">
                    {{ $initials }}
                </span>
                <div class="flex-1 min-w-[240px]">
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-ink">{{ $distributor->company_name }}</h1>
                        <span class="inline-flex items-center gap-1.5 bg-forest/10 text-forest-dark text-xs font-extrabold px-3 py-1 rounded-full">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                            Terverifikasi
                        </span>
                    </div>
                    <p class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs sm:text-sm">
                        <svg width="16" height="16" viewBox="0 0 24 24"><path d="M12 2l2.9 6.3 6.9.8-5.1 4.7 1.4 6.8L12 17.3 5.9 20.6l1.4-6.8L2.2 9.1l6.9-.8z" fill="#D4A017"/></svg>
                        <b class="text-ink">{{ number_format((float)($distributor->rating ?? 5.0), 1) }}</b>
                        <span class="text-muted">({{ $distributor->reviews->count() }} ulasan)</span>
                    </p>
                    <div class="mt-3 flex flex-wrap gap-x-6 gap-y-2 text-xs sm:text-sm text-muted">
                        <span class="flex items-center gap-1.5">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-5.6-7-11a7 7 0 1 1 14 0c0 5.4-7 11-7 11Z"/><circle cx="12" cy="10" r="2.6"/></svg>
                            {{ $distributor->district ? $distributor->district.', ' : '' }}{{ $distributor->city ?? 'Sidoarjo' }}
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
                            Mitra sejak {{ $distributor->created_at ? $distributor->created_at->format('Y') : '2021' }}
                        </span>
                    </div>
                </div>
                <div class="flex flex-col gap-2.5 shrink-0 w-full sm:w-auto">
                    @auth
                        @if(auth()->user()->hasRole('umkm'))
                            <a href="{{ route('umkm.catalog.index') }}" class="text-center px-6 py-3 rounded-xl bg-forest text-white font-extrabold text-xs sm:text-sm hover:bg-forest-dark shadow-xs transition">
                                Lihat Produk di Katalog
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}" class="text-center px-6 py-3 rounded-xl bg-forest text-white font-extrabold text-xs sm:text-sm hover:bg-forest-dark shadow-xs transition">
                                Dashboard
                            </a>
                        @endif
                    @else
                        <button type="button" onclick="document.getElementById('gateModal').classList.remove('hidden')" class="text-center px-6 py-3 rounded-xl bg-forest text-white font-extrabold text-xs sm:text-sm hover:bg-forest-dark shadow-xs transition cursor-pointer">
                            Login untuk Memesan
                        </button>
                    @endauth
                    <a href="{{ route('public.distributors.map') }}" class="text-center px-6 py-3 rounded-xl border border-line text-ink font-bold text-xs sm:text-sm hover:bg-cream transition">
                        Lihat di Peta
                    </a>
                </div>
            </div>

            <!-- STATISTIK -->
            <div class="mt-7 grid grid-cols-2 sm:grid-cols-3 divide-x divide-line border-t border-line pt-6 text-center">
                <div>
                    <p class="text-xl sm:text-2xl font-extrabold text-ink">{{ count($availableProducts) }}</p>
                    <p class="text-xs text-muted mt-0.5">Produk aktif</p>
                </div>
                <div>
                    <p class="text-xl sm:text-2xl font-extrabold text-ink">{{ number_format((float)($distributor->rating ?? 5.0), 1) }}</p>
                    <p class="text-xs text-muted mt-0.5">Rating rata-rata</p>
                </div>
                <div class="col-span-2 sm:col-span-1 border-t sm:border-t-0 border-line pt-4 sm:pt-0">
                    <p class="text-xl sm:text-2xl font-extrabold text-ink">{{ $distributor->reviews->count() }}</p>
                    <p class="text-xs text-muted mt-0.5">Ulasan pembeli</p>
                </div>
            </div>
        </section>

        <!-- KONTEN DUA KOLOM -->
        <div class="mt-6 grid lg:grid-cols-[340px_1fr] gap-6 items-start">

            <!-- KOLOM KIRI: LOKASI & MINI PETA -->
            <div class="space-y-6">
                <section class="bg-white border border-line rounded-3xl p-6 shadow-xs">
                    <h2 class="font-extrabold text-base text-ink">Lokasi Distributor</h2>
                    <p class="text-xs text-muted mt-1">{{ $distributor->district ? $distributor->district.', ' : '' }}{{ $distributor->city ?? 'Sidoarjo' }}</p>

                    @if($distributor->latitude && $distributor->longitude)
                        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
                        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
                        <div id="miniMap" class="relative mt-4 rounded-2xl overflow-hidden border border-line z-10" style="height:200px;"></div>
                        <script>
                            function initMiniMap() {
                                const container = document.getElementById('miniMap');
                                if (!container || typeof L === 'undefined' || container.dataset.initialized) return;
                                container.dataset.initialized = 'true';
                                const lat = {{ (float)$distributor->latitude }};
                                const lng = {{ (float)$distributor->longitude }};
                                const miniMap = L.map(container, { zoomControl: false, dragging: false }).setView([lat, lng], 13);
                                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(miniMap);
                                L.marker([lat, lng]).addTo(miniMap);
                            }
                            document.addEventListener('turbo:load', initMiniMap);
                            document.addEventListener('DOMContentLoaded', initMiniMap);
                        </script>
                    @else
                        <div class="mt-4 bg-cream border border-line rounded-2xl p-6 text-center text-xs text-muted">
                            Koordinat lokasi belum dipin di peta.
                        </div>
                    @endif
                </section>
            </div>

            <!-- KOLOM KANAN: DAFTAR PRODUK DITAWARKAN -->
            <section class="bg-white border border-line rounded-3xl p-6 shadow-xs">
                <h2 class="font-extrabold text-lg text-ink">Produk yang Dijual</h2>
                <p class="text-xs sm:text-sm text-muted mt-0.5">Daftar produk aktif yang siap dipesan dari {{ $distributor->company_name }}.</p>

                <div class="grid sm:grid-cols-2 gap-4 mt-5">
                    @forelse($availableProducts as $item)
                        @php $prod = $item['product']; @endphp
                        <div class="bg-cream/50 border border-line rounded-2xl p-4 flex flex-col justify-between">
                            <div>
                                <div class="h-24 rounded-xl bg-forest/10 flex items-center justify-center font-extrabold text-3xl text-forest mb-3">
                                    {{ strtoupper(substr($prod->name, 0, 1)) }}
                                </div>
                                <h3 class="font-extrabold text-ink text-sm">{{ $prod->name }}</h3>
                                <p class="text-xs text-muted mt-0.5">{{ $prod->unit }}</p>
                                <p class="text-[11px] text-muted mt-2">mulai dari · <span class="font-semibold text-brass">Estimasi</span></p>
                                <p class="font-extrabold text-base text-forest">Rp {{ number_format($item['min_price'], 0, ',', '.') }}</p>
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('public.catalog.show', $prod) }}" class="block text-center border border-forest text-forest text-xs font-bold py-2 rounded-xl hover:bg-forest/5 transition">
                                    Lihat Detail Produk
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="sm:col-span-2 text-center py-8 text-xs sm:text-sm text-muted">
                            Belum ada produk aktif yang dijual distributor ini.
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

    </main>

    <x-modal-gate-login />
</x-layouts.public>
