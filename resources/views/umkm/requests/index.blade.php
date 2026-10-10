<x-layouts.app>
    <x-slot name="title">Kebutuhan Bahan Baku Saya</x-slot>

    <div class="space-y-6 sm:space-y-8" x-data="{
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
                <h1 class="font-display font-semibold text-[1.75rem] sm:text-4xl text-brand-dark tracking-tight text-balance mt-2">Pengajuan Kebutuhan Bahan Baku</h1>
                <p class="text-[0.8125rem] text-brand-slate mt-1">Gabungkan kuantitas belanja Anda dengan UMKM lain di sekitar area untuk membuka diskon harga grosir.</p>
            </div>
            <button type="button" @click="openCreate = true" class="btn-primary text-sm py-3 px-6 shadow-md shadow-brand-primary/20 w-full sm:w-auto justify-center">
                Ajukan Kebutuhan Baru
            </button>
        </div>

        @php $hasRequests = $requests->isNotEmpty(); @endphp

        <!-- Request List -->
        @if ($hasRequests)
            <div class="card-shell">
                <div class="card-core p-4 sm:p-7 space-y-5">
                    <div>
                        <h2 class="text-base font-bold text-brand-dark">Daftar Pengajuan Kebutuhan Saya</h2>
                        <p class="text-xs text-brand-slate">Pantau progres auto-matching dan status grup pembelian kolektif Anda.</p>
                    </div>

                    <!-- Desktop: table -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="table-premium">
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
                                @foreach ($requests as $req)
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
                                            <span class="badge {{ $req->urgency->value === 'tinggi' ? 'bg-red-50 text-brand-brick' : ($req->urgency->value === 'sedang' ? 'bg-brand-ochre-soft text-brand-brass' : 'bg-brand-mint text-brand-emerald') }}">
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
                                                    <button type="submit" class="min-h-11 px-3 text-xs text-brand-brick font-semibold hover:underline cursor-pointer">
                                                        Batal
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-xs text-brand-slate">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile: cards -->
                    <div class="sm:hidden space-y-3">
                        @foreach ($requests as $req)
                            <div class="list-card space-y-3">
                                <div class="flex justify-between items-start gap-3">
                                    <div class="min-w-0">
                                        <p class="font-bold text-brand-dark text-sm">{{ $req->product->name }}</p>
                                        <p class="text-[0.6875rem] text-brand-slate mt-0.5">{{ $req->product->category->name }}</p>
                                    </div>
                                    <span class="badge {{ $req->status->badgeClass() }} shrink-0">{{ $req->status->label() }}</span>
                                </div>
                                <div class="grid grid-cols-2 gap-x-3 gap-y-2 text-[0.75rem]">
                                    <div>
                                        <p class="text-brand-slate">Kuantitas</p>
                                        <p class="font-bold text-brand-dark">{{ number_format($req->quantity, 0) }} {{ $req->unit }}</p>
                                    </div>
                                    <div>
                                        <p class="text-brand-slate">Urgensi</p>
                                        <span class="badge {{ $req->urgency->value === 'tinggi' ? 'bg-red-50 text-brand-brick' : ($req->urgency->value === 'sedang' ? 'bg-brand-ochre-soft text-brand-brass' : 'bg-brand-mint text-brand-emerald') }}">
                                            {{ $req->urgency->label() }}
                                        </span>
                                    </div>
                                    <div class="col-span-2">
                                        <p class="text-brand-slate">Batas menunggu</p>
                                        <p class="font-semibold text-brand-dark">{{ $req->deadline->format('d M Y, H:i') }} WITA</p>
                                        <p class="text-brand-slate mt-0.5">
                                            @if ($req->isExpired())
                                                <span class="text-brand-brick font-semibold">Batas waktu telah dicapai</span>
                                            @else
                                                Sisa waktu: {{ $req->deadline->diffForHumans() }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="col-span-2">
                                        <p class="text-brand-slate">Area pengiriman</p>
                                        <p class="font-semibold text-brand-dark">{{ $req->delivery_district }}</p>
                                        <p class="text-brand-slate truncate">{{ $req->delivery_address }}</p>
                                    </div>
                                </div>
                                @if ($req->status->value === 'PENDING_MATCH')
                                    <form method="POST" action="{{ route('umkm.requests.cancel', $req) }}" onsubmit="return confirm('Yakin ingin membatalkan pengajuan kebutuhan ini?');">
                                        @csrf
                                        <button type="submit" class="btn-ghost w-full text-xs font-semibold text-brand-brick border-brand-brick/25">
                                            Batalkan Pengajuan
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-3 border-t border-black/[0.04]">
                        {{ $requests->links() }}
                    </div>
                </div>
            </div>
        @else
            <!-- Empty state: says why + the one action that fills it -->
            <div class="card-shell">
                <div class="card-core px-6 py-12 text-center">
                    <p class="font-bold text-brand-dark">Belum ada pengajuan kebutuhan bahan baku.</p>
                    <p class="text-[0.8125rem] text-brand-slate mt-1.5 max-w-md mx-auto">Ajukan kebutuhan Anda, lalu sistem mencari UMKM sekitar untuk mencapai kuota harga grosir.</p>
                    <button type="button" @click="openCreate = true" class="btn-primary text-sm mt-5">
                        Ajukan Kebutuhan Baru
                    </button>
                </div>
            </div>
        @endif

        <!-- Create Request Modal -->
        <x-modal open="openCreate" size="xl"
                 eyebrow="Formulir Kebutuhan"
                 title="Ajukan Kebutuhan Bahan Baku"
                 subtitle="Sistem otomatis mencari UMKM terdekat untuk mencapai kuota MOQ grosir.">
            <form method="POST" action="{{ route('umkm.requests.store') }}" class="pt-5 space-y-5">
                @csrf

                <!-- Bahan baku -->
                <div>
                    <label for="product_id" class="field-label">Bahan baku</label>
                    <select id="product_id" name="product_id" required @change="updateProductMeta($event)"
                        class="input-field bg-white font-medium">
                        @foreach ($products as $prod)
                            <option value="{{ $prod->id }}" data-unit="{{ $prod->unit }}" data-price="{{ $prod->benchmark_retail_price }}" {{ $loop->first ? 'selected' : '' }}>
                                {{ $prod->name }} ({{ $prod->category->name }}) &bull; Patokan Pasar: Rp {{ number_format($prod->benchmark_retail_price, 0, ',', '.') }}/{{ $prod->unit }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Jumlah & deadline -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 bg-brand-cream/80 rounded-xl border border-black/[0.06] space-y-2">
                        <label for="quantity" class="field-label mb-0">Jumlah kebutuhan (<span x-text="unit"></span>)</label>
                        <div class="flex items-center gap-2">
                            <input type="number" id="quantity" name="quantity" step="0.5" x-model.number="qty" min="0.1" required
                                class="input-field py-2.5 text-base font-bold">
                            <span class="font-bold text-sm text-brand-slate px-3 py-2.5 bg-white rounded-lg border border-black/[0.06] min-w-14 text-center" x-text="unit"></span>
                        </div>
                        <p class="text-xs text-brand-slate">Estimasi harga eceran: <strong class="text-brand-dark">Rp <span x-text="(qty * benchmarkPrice).toLocaleString('id-ID')"></span></strong></p>
                    </div>

                    <div class="p-4 bg-brand-cream/80 rounded-xl border border-black/[0.06] space-y-2">
                        <label for="hours_until_deadline" class="field-label mb-0">Batas waktu menunggu</label>
                        <select id="hours_until_deadline" name="hours_until_deadline" required class="input-field bg-white font-medium">
                            <option value="6">6 jam (sangat mendesak)</option>
                            <option value="12">12 jam (kebutuhan hari ini)</option>
                            <option value="24" selected>24 jam (standar 1 hari)</option>
                            <option value="48">48 jam (2 hari)</option>
                            <option value="72">72 jam (3 hari)</option>
                        </select>
                        <p class="text-xs text-brand-slate">Lewat deadline tanpa kuota &rarr; jalur pembelian cepat.</p>
                    </div>
                </div>

                <!-- Urgensi -->
                <fieldset>
                    <legend class="field-label">Tingkat urgensi</legend>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <label class="flex items-center gap-3 border rounded-xl p-3 min-h-[3.5rem] cursor-pointer transition-colors border-black/[0.08] hover:border-black/20 has-[:checked]:border-brand-emerald has-[:checked]:bg-brand-mint has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-brand-primary/40 has-[:focus-visible]:ring-offset-2">
                            <input type="radio" name="urgency" value="rendah" class="peer sr-only">
                            <span aria-hidden="true" class="w-5 h-5 rounded-full border-2 border-black/25 bg-white shrink-0 transition-colors peer-checked:border-brand-emerald peer-checked:bg-brand-emerald"></span>
                            <span class="min-w-0">
                                <span class="block text-sm font-bold text-brand-dark">Biasa</span>
                                <span class="block text-[0.6875rem] text-brand-slate mt-0.5">Bisa menunggu grup</span>
                            </span>
                        </label>
                        <label class="flex items-center gap-3 border rounded-xl p-3 min-h-[3.5rem] cursor-pointer transition-colors border-black/[0.08] hover:border-black/20 has-[:checked]:border-brand-accent has-[:checked]:bg-brand-ochre-soft has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-brand-primary/40 has-[:focus-visible]:ring-offset-2">
                            <input type="radio" name="urgency" value="sedang" checked class="peer sr-only">
                            <span aria-hidden="true" class="w-5 h-5 rounded-full border-2 border-black/25 bg-white shrink-0 transition-colors peer-checked:border-brand-accent peer-checked:bg-brand-accent"></span>
                            <span class="min-w-0">
                                <span class="block text-sm font-bold text-brand-dark">Penting</span>
                                <span class="block text-[0.6875rem] text-brand-slate mt-0.5">Kebutuhan mingguan</span>
                            </span>
                        </label>
                        <label class="flex items-center gap-3 border rounded-xl p-3 min-h-[3.5rem] cursor-pointer transition-colors border-black/[0.08] hover:border-black/20 has-[:checked]:border-brand-brick has-[:checked]:bg-red-50 has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-brand-primary/40 has-[:focus-visible]:ring-offset-2">
                            <input type="radio" name="urgency" value="tinggi" class="peer sr-only">
                            <span aria-hidden="true" class="w-5 h-5 rounded-full border-2 border-black/25 bg-white shrink-0 transition-colors peer-checked:border-brand-brick peer-checked:bg-brand-brick"></span>
                            <span class="min-w-0">
                                <span class="block text-sm font-bold text-brand-dark">Mendesak</span>
                                <span class="block text-[0.6875rem] text-brand-slate mt-0.5">Stok hampir habis</span>
                            </span>
                        </label>
                    </div>
                </fieldset>

                <!-- Area pengiriman -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="delivery_district" class="field-label">Kecamatan pengiriman</label>
                        <select id="delivery_district" name="delivery_district" required class="input-field bg-white">
                            <option value="Banjarmasin Utara" {{ $umkm->district === 'Banjarmasin Utara' ? 'selected' : '' }}>Banjarmasin Utara</option>
                            <option value="Banjarmasin Barat" {{ $umkm->district === 'Banjarmasin Barat' ? 'selected' : '' }}>Banjarmasin Barat</option>
                            <option value="Banjarmasin Timur" {{ $umkm->district === 'Banjarmasin Timur' ? 'selected' : '' }}>Banjarmasin Timur</option>
                            <option value="Banjarmasin Selatan" {{ $umkm->district === 'Banjarmasin Selatan' ? 'selected' : '' }}>Banjarmasin Selatan</option>
                            <option value="Banjarmasin Tengah" {{ $umkm->district === 'Banjarmasin Tengah' ? 'selected' : '' }}>Banjarmasin Tengah</option>
                        </select>
                    </div>
                    <div>
                        <label for="delivery_address" class="field-label">Alamat lengkap usaha</label>
                        <input type="text" id="delivery_address" name="delivery_address" value="{{ $umkm->address }}" required class="input-field">
                    </div>
                </div>

                <!-- Catatan -->
                <div>
                    <label for="notes" class="field-label">Catatan tambahan (opsional)</label>
                    <textarea id="notes" name="notes" rows="2" placeholder="Petunjuk khusus atau patokan lokasi pengantaran..." class="input-field"></textarea>
                </div>

                <!-- Banner Info -->
                <div class="p-3.5 bg-brand-mint/60 rounded-xl text-xs text-brand-primary border border-brand-primary/10 flex items-start gap-2.5">
                    <span class="w-2 h-2 rounded-full bg-brand-emerald mt-1 flex-shrink-0"></span>
                    <p class="leading-relaxed">
                        Setelah pengajuan dikirim, sistem Kula'an mencari UMKM pasangan di wilayah yang sama hingga kuantitas mencapai batas MOQ distributor, sehingga Anda mendapatkan harga grosir otomatis.
                    </p>
                </div>

                <!-- Actions: submit full-width at the bottom on phones, right-aligned row on desktop. -->
                <div class="flex flex-col-reverse sm:flex-row-reverse sm:justify-end gap-3 pt-4 border-t border-black/[0.06]">
                    <button type="submit" class="btn-primary text-sm py-3 px-8 w-full sm:w-auto shadow-md shadow-brand-primary/20">
                        Kirim Pengajuan Kebutuhan
                    </button>
                    <button type="button" @click="openCreate = false" class="btn-ghost text-sm py-3 w-full sm:w-auto">
                        Batal
                    </button>
                </div>
            </form>
        </x-modal>
    </div>
</x-layouts.app>
