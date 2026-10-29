<x-layouts.public title="Peta Distributor">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
        <!-- JUDUL -->
        <div class="max-w-2xl">
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-ink">Peta Distributor</h1>
            <p class="mt-2 text-xs sm:text-sm text-muted leading-relaxed">Cari distributor terdekat dari lokasi usahamu. Lihat dulu siapa saja yang melayani areamu, produk apa yang mereka jual, dan seberapa dekat jaraknya — tanpa perlu login.</p>
        </div>

        <!-- LAYOUT DUA KOLOM -->
        <div class="mt-6 grid lg:grid-cols-[380px_1fr] gap-6 items-start">

            <!-- PANEL DAFTAR -->
            <section class="order-2 lg:order-1 bg-white border border-line rounded-3xl p-5 shadow-xs">
                <div class="flex items-center gap-2.5 bg-cream border border-line rounded-xl px-3.5 py-3 focus-within:border-forest">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#5A6B60" stroke-width="2.2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                    <input type="text" id="searchInput" placeholder="Cari distributor..." class="bg-transparent outline-none w-full text-xs sm:text-sm placeholder:text-muted/70">
                </div>

                <button type="button" id="btnMyLocation" class="mt-4 w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border-2 border-forest text-forest font-bold text-xs sm:text-sm hover:bg-forest hover:text-white transition cursor-pointer">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="12" cy="12" r="7"/><circle cx="12" cy="12" r="2.4" fill="currentColor" stroke="none"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg>
                    Lokasi Saya
                </button>
                <p id="locationNote" class="mt-2 text-[11px] text-muted text-center">Tekan "Lokasi Saya" untuk menghitung jarak langsung dari posisimu.</p>

                <div class="mt-5 space-y-3 max-h-[500px] overflow-y-auto pr-1" id="distList">
                    @foreach($distributorList as $d)
                        <div id="card-{{ $d['id'] }}" onclick="selectDistributor({{ $d['id'] }})" class="dist-card bg-white border border-line rounded-2xl p-4 cursor-pointer hover:border-forest/50 transition">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="font-extrabold text-ink text-sm">{{ $d['name'] }}</h3>
                                <span class="dist-distance bg-brass-soft text-brass text-[11px] font-bold px-2 py-0.5 rounded-full shrink-0">
                                    {{ $d['lat'] ? '★ '.$d['rating'] : 'Belum dipin' }}
                                </span>
                            </div>
                            <p class="text-xs text-muted mt-1">{{ $d['area'] }}</p>
                            <div class="mt-3 flex items-center justify-between text-xs text-muted">
                                <span>{{ $d['categories'] }} · {{ $d['product_count'] }} produk</span>
                                <a href="{{ route('public.distributors.show', $d['id']) }}" class="text-forest font-bold hover:underline" onclick="event.stopPropagation()">Profil →</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <!-- PETA LEAFLET -->
            <section>
                <div id="map" class="relative rounded-3xl overflow-hidden border border-line shadow-xs z-10" style="height:600px;"></div>

                <!-- Legenda -->
                <div class="mt-3 flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-muted px-1">
                    <span class="flex items-center gap-2"><span class="w-3.5 h-3.5 rounded-full bg-forest inline-block"></span> Distributor mitra Kulaan</span>
                    <span class="flex items-center gap-2"><span class="w-3.5 h-3.5 rounded-full bg-brass inline-block"></span> Lokasi Saya</span>
                    <span class="flex items-center gap-2"><span class="w-5 h-3.5 rounded-full inline-block" style="background:rgba(45,106,79,.12);border:1.5px dashed rgba(45,106,79,.5);"></span> Radius jangkauan (±5 km)</span>
                </div>
            </section>
        </div>

        <!-- STRIP INSIGHT -->
        <section class="mt-12">
            <h2 class="text-xl sm:text-2xl font-extrabold text-ink">Kenapa lokasi dekat distributor penting</h2>
            <div class="mt-5 grid md:grid-cols-3 gap-5">
                <div class="bg-white border border-line rounded-3xl p-6 shadow-xs">
                    <span class="w-11 h-11 rounded-2xl bg-brass-soft flex items-center justify-center text-brass font-bold">01</span>
                    <h3 class="mt-4 font-extrabold text-base text-ink">Ongkir lebih murah</h3>
                    <p class="mt-1.5 text-xs sm:text-sm text-muted leading-relaxed">Jarak kirim yang pendek menekan biaya pengiriman per order, jadi margin usahamu tidak habis di jalan.</p>
                </div>
                <div class="bg-white border border-line rounded-3xl p-6 shadow-xs">
                    <span class="w-11 h-11 rounded-2xl bg-brass-soft flex items-center justify-center text-brass font-bold">02</span>
                    <h3 class="mt-4 font-extrabold text-base text-ink">Stok lebih cepat sampai</h3>
                    <p class="mt-1.5 text-xs sm:text-sm text-muted leading-relaxed">Distributor dekat bisa mengirim di hari yang sama, stok warung atau dapurmu tidak perlu kosong menunggu kiriman jauh.</p>
                </div>
                <div class="bg-white border border-line rounded-3xl p-6 shadow-xs">
                    <span class="w-11 h-11 rounded-2xl bg-brass-soft flex items-center justify-center text-brass font-bold">03</span>
                    <h3 class="mt-4 font-extrabold text-base text-ink">MOQ lebih mudah tercapai</h3>
                    <p class="mt-1.5 text-xs sm:text-sm text-muted leading-relaxed">Di area yang ramai distributor biasanya juga ramai UMKM yang ikut kulaan, jadi quantity kelompok lebih cepat terkumpul.</p>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="mt-10 bg-forest-deep rounded-3xl px-6 sm:px-8 py-8 md:flex items-center justify-between gap-8">
            <div>
                <h2 class="text-xl sm:text-2xl font-extrabold text-white">Sudah menemukan distributor terdekatmu?</h2>
                <p class="mt-1.5 text-xs sm:text-sm text-white/75 leading-relaxed">Daftar sebagai UMKM untuk mulai kulakan kolektif, mendapatkan harga kelompok, dan melihat rekomendasi SPK berdasarkan lokasimu.</p>
            </div>
            <a href="{{ route('register') }}" class="mt-4 md:mt-0 shrink-0 inline-block px-6 py-3 rounded-xl bg-brass text-white font-extrabold text-xs sm:text-sm hover:brightness-110 shadow-xs transition">
                Daftar Jadi UMKM
            </a>
        </section>
    </main>

    <x-modal-gate-login />

    <script>
        const rawDistributors = {!! $distributorsJson !!};
        let map, userMarker, activeCircle;
        const markers = {};
        let userPos = null;

        // Default center: Jember (-8.17211, 113.70011)
        const defaultLat = -8.17211;
        const defaultLng = 113.70011;

        document.addEventListener('DOMContentLoaded', function() {
            map = L.map('map').setView([defaultLat, defaultLng], 12);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            // Add pins for distributors with coordinates
            rawDistributors.forEach(d => {
                if (d.lat && d.lng) {
                    const marker = L.marker([d.lat, d.lng]).addTo(map);
                    
                    const popupContent = `
                        <div class="p-1 max-w-[220px]">
                            <h3 class="font-bold text-sm text-gray-900">${d.name}</h3>
                            <p class="text-xs text-gray-600 mt-0.5">★ ${d.rating} (${d.review_count} ulasan) · ${d.area}</p>
                            <p class="text-xs text-gray-500 mt-1">${d.categories} · ${d.product_count} produk</p>
                            <div class="mt-3 flex gap-2">
                                <a href="/distributor/${d.id}" class="flex-1 text-center bg-forest text-white text-xs font-bold py-1.5 rounded-lg">Profil</a>
                            </div>
                        </div>
                    `;
                    marker.bindPopup(popupContent);

                    marker.on('click', function() {
                        selectDistributor(d.id, false);
                    });

                    markers[d.id] = marker;
                }
            });

            // Search filter JS
            document.getElementById('searchInput').addEventListener('input', function(e) {
                const query = e.target.value.toLowerCase();
                rawDistributors.forEach(d => {
                    const card = document.getElementById('card-' + d.id);
                    if (card) {
                        const match = d.name.toLowerCase().includes(query) || d.area.toLowerCase().includes(query) || d.categories.toLowerCase().includes(query);
                        card.style.display = match ? 'block' : 'none';
                    }
                });
            });

            // Location button JS
            document.getElementById('btnMyLocation').addEventListener('click', function() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(pos) {
                        userPos = { lat: pos.coords.latitude, lng: pos.coords.longitude };
                        
                        if (userMarker) { map.removeLayer(userMarker); }
                        userMarker = L.marker([userPos.lat, userPos.lng], {
                            icon: L.divIcon({
                                className: 'user-pin',
                                html: '<div class="bg-brass text-white text-[10px] font-extrabold px-2 py-1 rounded-full shadow border-2 border-white">LOKASI SAYA</div>'
                            })
                        }).addTo(map);

                        map.setView([userPos.lat, userPos.lng], 13);
                        updateDistances();
                        document.getElementById('locationNote').innerText = 'Jarak terhitung dari posisi lokasi Anda saat ini.';
                    }, function(err) {
                        alert('Izin lokasi tidak diberikan atau tidak tersedia.');
                    });
                }
            });
        });

        function selectDistributor(id, panTo = true) {
            document.querySelectorAll('.dist-card').forEach(c => c.classList.remove('border-forest', 'bg-cream/40'));
            const card = document.getElementById('card-' + id);
            if (card) {
                card.classList.add('border-forest', 'bg-cream/40');
                card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            const d = rawDistributors.find(item => item.id === id);
            if (d && d.lat && d.lng) {
                if (activeCircle) { map.removeLayer(activeCircle); }
                activeCircle = L.circle([d.lat, d.lng], {
                    color: '#2D6A4F',
                    fillColor: '#2D6A4F',
                    fillOpacity: 0.12,
                    radius: 5000
                }).addTo(map);

                if (panTo && markers[id]) {
                    map.setView([d.lat, d.lng], 13);
                    markers[id].openPopup();
                }
            }
        }

        function calculateHaversine(lat1, lon1, lat2, lon2) {
            const R = 6371;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return (R * c).toFixed(1);
        }

        function updateDistances() {
            if (!userPos) return;
            rawDistributors.forEach(d => {
                if (d.lat && d.lng) {
                    const distKm = calculateHaversine(userPos.lat, userPos.lng, d.lat, d.lng);
                    const card = document.getElementById('card-' + d.id);
                    if (card) {
                        const badge = card.querySelector('.dist-distance');
                        if (badge) {
                            badge.innerText = distKm + ' km';
                        }
                    }
                }
            });
        }
    </script>
</x-layouts.public>
