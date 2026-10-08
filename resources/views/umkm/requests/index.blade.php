<x-layouts.app>
    <x-slot name="title">Kebutuhan Bahan Baku Saya</x-slot>

    <div class="space-y-6 sm:space-y-8 animate-fade-up" x-data="{ 
        openCreate: false,
        selectedProductId: '{{ $products->first()?->id ?? '' }}',
        qty: 4,
        unit: '{{ $products->first()?->unit ?? 'kg' }}',
        benchmarkPrice: {{ $products->first()?->benchmark_retail_price ?? 9000 }},
        updateProductMeta(event) {
            const opt = event.target.options[event.target.selectedIndex];
            this.unit = opt.getAttribute('data-unit') || 'kg';
            this.benchmarkPrice = parseFloat(opt.getAttribute('data-price') || 0);
        }
    }">
        <!-- Top Bar -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <span class="eyebrow">Pengadaan Kolektif</span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-brand-dark tracking-tight mt-1.5">Pengajuan Kebutuhan Bahan Baku</h1>
                <p class="text-[0.8125rem] text-brand-slate mt-1">Gabungkan kuantitas belanja Anda dengan UMKM lain di sekitar area untuk membuka diskon harga grosir.</p>
            </div>
            <button @click="openCreate = true" class="btn-primary text-sm py-3 px-6 shadow-md shadow-brand-primary/20 w-full sm:w-auto justify-center">
                + Ajukan Kebutuhan Baru
            </button>
        </div>

        <!-- Request List Table (Double-Bezel) -->
        <div class="card-shell">
            <div class="card-core p-4 sm:p-7 space-y-5">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-base font-bold text-brand-dark">Daftar Pengajuan Kebutuhan Saya</h2>
                        <p class="text-xs text-brand-slate">Pantau progres auto-matching dan status grup pembelian kolektif Anda.</p>
                    </div>
                </div>

                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <table class="table-premium min-w-[700px]">
                        <thead>
                            <tr>
                                <th>Bahan Baku</th>
                                <th class="text-center">Kuantitas</th>
                                <th>Batas Menunggu (Deadline)</th>
                                <th>Urgensi</th>
                                <th>Area Pengiriman</th>
                                <th class="text-center">Status Matching</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($requests as $req)
                                <tr>
                                    <td>
                                        <div class="font-bold text-brand-dark">{{ $req->product->name }}</div>
                                        <div class="text-[0.6875rem] text-brand-slate">{{ $req->product->category->name }}</div>
                                    </td>
                                    <td class="text-center font-bold text-brand-dark">
                                        {{ number_format($req->quantity, 0) }} {{ $req->unit }}
                                    </td>
                                    <td>
                                        <div class="font-semibold text-brand-dark">{{ $req->deadline->format('d M Y, H:i') }} WITA</div>
                                        <div class="text-[0.6875rem] text-brand-slate mt-0.5">
                                            @if ($req->isExpired())
                                                <span class="text-brand-brick font-semibold">Batas waktu telah dicapai</span>
                                            @else
                                                <span>Sisa waktu: {{ $req->deadline->diffForHumans() }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $req->urgency->value === 'tinggi' ? 'bg-red-50 text-brand-brick' : ($req->urgency->value === 'sedang' ? 'bg-brand-ochre-soft text-brand-accent' : 'bg-brand-mint text-brand-emerald') }}">
                                            {{ $req->urgency->label() }}
                                        </span>
                                    </td>
                                    <td class="text-[0.75rem] text-brand-slate">
                                        <div class="font-semibold text-brand-dark">{{ $req->delivery_district }}</div>
                                        <div class="truncate max-w-[200px]">{{ $req->delivery_address }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $req->status->badgeClass() }}">
                                            {{ $req->status->label() }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        @if ($req->status->value === 'PENDING_MATCH')
                                            <form method="POST" action="{{ route('umkm.requests.cancel', $req) }}" onsubmit="return confirm('Yakin ingin membatalkan pengajuan kebutuhan ini?');">
                                                @csrf
                                                <button type="submit" class="text-xs text-brand-brick font-semibold hover:underline cursor-pointer">
                                                    Batal
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-brand-slate">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-brand-slate text-[0.8125rem]">
                                        Belum ada pengajuan kebutuhan bahan baku. Klik tombol <strong>+ Ajukan Kebutuhan Baru</strong> di atas untuk memulai.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pt-3 border-t border-black/[0.04]">
                    {{ $requests->links() }}
                </div>
            </div>
        </div>

        <!-- Create Request Modal (Clean, Accessible & Mobile-Friendly) -->
        <div x-show="openCreate" x-cloak class="modal-overlay">
            <div @click.away="openCreate = false" class="modal-content max-w-xl space-y-6 animate-fade-up max-h-[92vh] overflow-y-auto">
                <!-- Modal Header -->
                <div class="flex justify-between items-start pb-4 border-b border-black/[0.06]">
                    <div>
                        <span class="eyebrow mb-1">Formulir Kebutuhan</span>
                        <h2 class="text-xl font-bold text-brand-dark tracking-tight">Ajukan Kebutuhan Bahan Baku</h2>
                        <p class="text-xs text-brand-slate mt-0.5">Sistem otomatis mencari UMKM terdekat untuk mencapai kuota MOQ grosir.</p>
                    </div>
                    <button @click="openCreate = false" type="button" class="text-brand-slate hover:text-brand-dark p-1 rounded-lg text-2xl font-bold leading-none cursor-pointer">&times;</button>
                </div>

                <form method="POST" action="{{ route('umkm.requests.store') }}" class="space-y-5">
                    @csrf

                    <!-- 1. Pilih Bahan Baku -->
                    <div>
                        <label for="product_id" class="block text-xs font-bold text-brand-dark uppercase tracking-wider mb-2">1. Pilih Produk Bahan Baku</label>
                        <select id="product_id" name="product_id" required @change="updateProductMeta($event)"
                            class="input-field bg-white py-3 text-sm font-medium">
                            @foreach ($products as $prod)
                                <option value="{{ $prod->id }}" data-unit="{{ $prod->unit }}" data-price="{{ $prod->benchmark_retail_price }}" {{ $loop->first ? 'selected' : '' }}>
                                    {{ $prod->name }} ({{ $prod->category->name }}) &bull; Patokan Pasar: Rp {{ number_format($prod->benchmark_retail_price, 0, ',', '.') }}/{{ $prod->unit }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 2. Kuantitas & Deadline -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-4 bg-brand-cream/60 rounded-xl border border-black/[0.04] space-y-2">
                            <label for="quantity" class="block text-xs font-bold text-brand-dark uppercase tracking-wider">
                                2. Jumlah Kebutuhan (<span x-text="unit"></span>)
                            </label>
                            <div class="flex items-center gap-2">
                                <input type="number" id="quantity" name="quantity" step="0.5" x-model.number="qty" min="0.1" required
                                    class="input-field py-2.5 text-base font-bold text-brand-dark">
                                <span class="font-bold text-xs text-brand-slate px-2 py-2 bg-white rounded-lg border border-black/[0.06]" x-text="unit"></span>
                            </div>
                            <p class="text-[11px] text-brand-slate">Estimasi Eceran: <strong class="text-brand-dark">Rp <span x-text="(qty * benchmarkPrice).toLocaleString('id-ID')"></span></strong></p>
                        </div>

                        <div class="p-4 bg-brand-cream/60 rounded-xl border border-black/[0.04] space-y-2">
                            <label for="hours_until_deadline" class="block text-xs font-bold text-brand-dark uppercase tracking-wider">
                                3. Batas Waktu Menunggu
                            </label>
                            <select id="hours_until_deadline" name="hours_until_deadline" required class="input-field bg-white py-2.5 text-xs font-medium">
                                <option value="6">6 Jam (Sangat Mendesak)</option>
                                <option value="12">12 Jam (Kebutuhan Hari Ini)</option>
                                <option value="24" selected>24 Jam (Standar 1 Hari)</option>
                                <option value="48">48 Jam (2 Hari)</option>
                                <option value="72">72 Jam (3 Hari)</option>
                            </select>
                            <p class="text-[11px] text-brand-slate">Jika lewat deadline tanpa kuota &rarr; jalur cepat.</p>
                        </div>
                    </div>

                    <!-- 3. Tingkat Urgensi -->
                    <div>
                        <label class="block text-xs font-bold text-brand-dark uppercase tracking-wider mb-2">4. Tingkat Urgensi</label>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="border border-black/[0.06] rounded-xl p-2.5 text-center cursor-pointer hover:border-brand-primary has-[:checked]:bg-brand-mint/60 has-[:checked]:border-brand-primary has-[:checked]:text-brand-primary">
                                <input type="radio" name="urgency" value="rendah" class="sr-only">
                                <span class="block text-xs font-bold">Biasa</span>
                                <span class="block text-[10px] text-brand-slate mt-0.5">Bisa tunggu grup</span>
                            </label>
                            <label class="border border-black/[0.06] rounded-xl p-2.5 text-center cursor-pointer hover:border-brand-primary has-[:checked]:bg-brand-ochre-soft has-[:checked]:border-brand-accent has-[:checked]:text-brand-accent">
                                <input type="radio" name="urgency" value="sedang" checked class="sr-only">
                                <span class="block text-xs font-bold">Penting</span>
                                <span class="block text-[10px] text-brand-slate mt-0.5">Kebutuhan mingguan</span>
                            </label>
                            <label class="border border-black/[0.06] rounded-xl p-2.5 text-center cursor-pointer hover:border-brand-primary has-[:checked]:bg-red-50 has-[:checked]:border-brand-brick has-[:checked]:text-brand-brick">
                                <input type="radio" name="urgency" value="tinggi" class="sr-only">
                                <span class="block text-xs font-bold">Mendesak</span>
                                <span class="block text-[10px] text-brand-slate mt-0.5">Stok hampir habis</span>
                            </label>
                        </div>
                    </div>

                    <!-- 4. Area Pengiriman -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="delivery_district" class="block text-xs font-bold text-brand-dark uppercase tracking-wider mb-1.5">Kecamatan Pengiriman</label>
                            <select id="delivery_district" name="delivery_district" required class="input-field bg-white py-2.5 text-xs">
                                <option value="Banjarmasin Utara" {{ $umkm->district === 'Banjarmasin Utara' ? 'selected' : '' }}>Banjarmasin Utara</option>
                                <option value="Banjarmasin Barat" {{ $umkm->district === 'Banjarmasin Barat' ? 'selected' : '' }}>Banjarmasin Barat</option>
                                <option value="Banjarmasin Timur" {{ $umkm->district === 'Banjarmasin Timur' ? 'selected' : '' }}>Banjarmasin Timur</option>
                                <option value="Banjarmasin Selatan" {{ $umkm->district === 'Banjarmasin Selatan' ? 'selected' : '' }}>Banjarmasin Selatan</option>
                                <option value="Banjarmasin Tengah" {{ $umkm->district === 'Banjarmasin Tengah' ? 'selected' : '' }}>Banjarmasin Tengah</option>
                            </select>
                        </div>
                        <div>
                            <label for="delivery_address" class="block text-xs font-bold text-brand-dark uppercase tracking-wider mb-1.5">Alamat Lengkap Usaha</label>
                            <input type="text" id="delivery_address" name="delivery_address" value="{{ $umkm->address }}" required class="input-field py-2.5 text-xs">
                        </div>
                    </div>

                    <!-- 5. Catatan Tambahan -->
                    <div>
                        <label for="notes" class="block text-xs font-bold text-brand-dark uppercase tracking-wider mb-1.5">Catatan Tambahan (Opsional)</label>
                        <textarea id="notes" name="notes" rows="2" placeholder="Petunjuk khusus atau patokan lokasi pengantaran..." class="input-field text-xs"></textarea>
                    </div>

                    <!-- Banner Info -->
                    <div class="p-3.5 bg-brand-mint/60 rounded-xl text-xs text-brand-primary border border-brand-primary/10 flex items-start gap-2.5">
                        <span class="w-2 h-2 rounded-full bg-brand-emerald mt-1 flex-shrink-0"></span>
                        <p class="leading-relaxed">
                            Setelah pengajuan dikirim, sistem Kula'an akan mencari UMKM pasangan di wilayah yang sama hingga kuantitas mencapai batas MOQ distributor (otomatis mendapatkan harga grosir).
                        </p>
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-black/[0.06]">
                        <button type="button" @click="openCreate = false" class="btn-ghost text-xs py-2.5 px-5">
                            Batal
                        </button>
                        <button type="submit" class="btn-primary text-xs py-2.5 px-6 font-bold shadow-md shadow-brand-primary/20">
                            Kirim Pengajuan Kebutuhan &rarr;
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
