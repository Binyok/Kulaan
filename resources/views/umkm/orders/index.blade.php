<x-layouts.app>
    <x-slot name="title">Riwayat Pesanan &amp; Tagihan</x-slot>

    <div x-data="{
        showModal: false,
        distributorName: '',
        reviewAction: '',
        rating: 5,
        comment: '',
        openRatingModal(id, name, action) {
            this.distributorName = name;
            this.reviewAction = action;
            this.rating = 5;
            this.comment = '';
            this.showModal = true;
        }
    }" class="space-y-6 sm:space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <span class="eyebrow">Pesanan Kolektif</span>
                <h1 class="font-display font-semibold text-[1.75rem] sm:text-4xl text-brand-dark tracking-tight text-balance mt-2">Riwayat Pesanan &amp; Tagihan</h1>
                <p class="text-[0.8125rem] text-brand-slate mt-1">Daftar lengkap invoice individual, status pesanan, dan rating distributor mitra pengadaan.</p>
            </div>
        </div>

        @php $hasOrders = $orderItems->isNotEmpty(); @endphp

        <!-- Table of Order Items & Invoices -->
        @if ($hasOrders)
            <div class="card-shell">
                <div class="card-core p-4 sm:p-7 space-y-5">
                    <!-- Desktop: table -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="table-premium">
                            <thead>
                                <tr>
                                    <th>No. Pesanan / Tanggal</th>
                                    <th>Bahan Baku</th>
                                    <th class="text-center">Kuantitas</th>
                                    <th class="text-right">Harga Grosir</th>
                                    <th class="text-right">Total Hemat</th>
                                    <th class="text-center">Status Pembayaran</th>
                                    <th class="text-center">Rating Mitra</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orderItems as $item)
                                    <tr>
                                        <td>
                                            <div class="font-mono font-bold text-brand-dark">{{ $item->order->order_number }}</div>
                                            <div class="text-[11px] text-brand-slate">{{ $item->created_at->format('d M Y, H:i') }} WITA</div>
                                        </td>
                                        <td>
                                            <div class="font-bold text-brand-dark">{{ $item->product->name }}</div>
                                            <div class="text-[11px] text-brand-slate">Distributor: {{ $item->order->distributor->company_name }}</div>
                                        </td>
                                        <td class="text-center font-bold text-brand-dark">
                                            {{ number_format($item->quantity, 0) }} {{ $item->product->unit }}
                                        </td>
                                        <td class="text-right font-extrabold text-brand-primary">
                                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                            <div class="text-[10px] text-brand-slate font-normal">@ Rp {{ number_format($item->unit_price, 0, ',', '.') }}/{{ $item->product->unit }}</div>
                                        </td>
                                        <td class="text-right font-bold text-brand-brass">
                                            Hemat Rp {{ number_format($item->savings_amount, 0, ',', '.') }}
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $item->payment_status->badgeClass() }}">
                                                {{ $item->payment_status->label() }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if ($item->review)
                                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200" title="{{ $item->review->comment }}">
                                                    ★ {{ $item->review->rating }}/5
                                                </span>
                                            @elseif ($item->payment_status->value === 'PAID')
                                                <button type="button"
                                                    @click="openRatingModal({{ $item->id }}, '{{ addslashes($item->order->distributor->company_name) }}', '{{ route('umkm.orders.review', $item) }}')"
                                                    class="min-h-11 px-3 text-[11px] font-semibold text-amber-700 hover:text-amber-800 bg-amber-50 hover:bg-amber-100 rounded-md border border-amber-200 transition-colors cursor-pointer">
                                                    ★ Beri Ulasan
                                                </button>
                                            @else
                                                <span class="text-xs text-brand-slate">-</span>
                                            @endif
                                        </td>
                                        <td class="text-right whitespace-nowrap">
                                            <a href="{{ route('payment.show', $item) }}"
                                                class="{{ $item->payment_status->value === 'UNPAID' ? 'btn-primary' : 'btn-ghost text-brand-primary font-bold' }} text-xs py-1.5 px-3.5 shadow-sm">
                                                {{ $item->payment_status->value === 'UNPAID' ? 'Bayar Invoice' : 'Lihat Invoice' }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile: cards -->
                    <div class="sm:hidden space-y-3">
                        @foreach ($orderItems as $item)
                            <div class="list-card space-y-3">
                                <div class="flex justify-between items-start gap-3">
                                    <div class="min-w-0">
                                        <p class="font-bold text-brand-dark text-sm">{{ $item->product->name }}</p>
                                        <p class="font-mono text-[0.6875rem] text-brand-slate mt-0.5">{{ $item->order->order_number }} &bull; {{ $item->created_at->format('d M Y') }}</p>
                                    </div>
                                    <span class="badge {{ $item->payment_status->badgeClass() }} shrink-0">{{ $item->payment_status->label() }}</span>
                                </div>
                                <p class="text-[0.6875rem] text-brand-slate">Distributor: {{ $item->order->distributor->company_name }}</p>
                                <div class="grid grid-cols-2 gap-x-3 gap-y-2 text-[0.75rem]">
                                    <div>
                                        <p class="text-brand-slate">Kuantitas</p>
                                        <p class="font-bold text-brand-dark">{{ number_format($item->quantity, 0) }} {{ $item->product->unit }}</p>
                                    </div>
                                    <div>
                                        <p class="text-brand-slate">Harga grosir</p>
                                        <p class="font-extrabold text-brand-primary">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                                        <p class="text-brand-slate">@ Rp {{ number_format($item->unit_price, 0, ',', '.') }}/{{ $item->product->unit }}</p>
                                    </div>
                                    <div class="col-span-2">
                                        <p class="text-brand-slate">Total hemat</p>
                                        <p class="font-bold text-brand-brass">Hemat Rp {{ number_format($item->savings_amount, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between gap-3 pt-2 border-t border-black/[0.04]">
                                    <div>
                                        @if ($item->review)
                                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200" title="{{ $item->review->comment }}">
                                                ★ {{ $item->review->rating }}/5
                                            </span>
                                        @elseif ($item->payment_status->value === 'PAID')
                                            <button type="button"
                                                @click="openRatingModal({{ $item->id }}, '{{ addslashes($item->order->distributor->company_name) }}', '{{ route('umkm.orders.review', $item) }}')"
                                                class="min-h-11 px-3 text-[11px] font-semibold text-amber-700 bg-amber-50 rounded-md border border-amber-200 transition-colors cursor-pointer">
                                                ★ Beri Ulasan
                                            </button>
                                        @else
                                            <span class="text-xs text-brand-slate">-</span>
                                        @endif
                                    </div>
                                    <a href="{{ route('payment.show', $item) }}"
                                        class="{{ $item->payment_status->value === 'UNPAID' ? 'btn-primary' : 'btn-ghost text-brand-primary font-bold' }} text-xs py-1.5 px-3.5">
                                        {{ $item->payment_status->value === 'UNPAID' ? 'Bayar Invoice' : 'Lihat Invoice' }}
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-3 border-t border-black/[0.04]">
                        {{ $orderItems->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="card-shell">
                <div class="card-core px-6 py-12 text-center">
                    <p class="font-bold text-brand-dark">Belum ada riwayat pesanan kolektif.</p>
                    <p class="text-[0.8125rem] text-brand-slate mt-1.5 max-w-md mx-auto">Setelah pengajuan kebutuhan ter-matching dan dibayar, invoice dan rating mitra tampil di sini.</p>
                    <a href="{{ route('umkm.requests.index') }}" class="btn-primary text-sm mt-5">Ajukan Kebutuhan</a>
                </div>
            </div>
        @endif

        <!-- Rating Modal -->
        <x-modal open="showModal" size="sm" title="Beri Penilaian Distributor">
            <p class="text-xs text-brand-slate mt-4" x-text="distributorName"></p>

            <form :action="reviewAction" method="POST" class="pt-4 space-y-4">
                @csrf
                <div>
                    <label class="field-label">Rating bintang (kualitas layanan &amp; produk)</label>
                    <div class="flex items-center gap-1">
                        <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                            <button type="button" @click="rating = star"
                                class="text-2xl min-w-11 min-h-11 flex items-center justify-center transition-transform hover:scale-110 rounded-lg"
                                :class="star <= rating ? 'text-amber-400' : 'text-slate-300'"
                                :aria-label="star + ' bintang'">
                                ★
                            </button>
                        </template>
                        <span class="text-xs font-bold text-brand-slate ml-2" x-text="rating + ' / 5 Bintang'"></span>
                    </div>
                    <input type="hidden" name="rating" :value="rating">
                </div>

                <div>
                    <label for="review-comment" class="field-label">Catatan / ulasan tambahan (opsional)</label>
                    <textarea id="review-comment" name="comment" rows="3" x-model="comment" placeholder="Bagikan pengalaman transaksi, kualitas bahan baku, dan ketepatan waktu pengiriman..." class="input-field"></textarea>
                </div>

                <div class="flex flex-col-reverse sm:flex-row-reverse sm:justify-end gap-3 pt-2 border-t border-black/[0.06]">
                    <button type="submit" class="btn-primary text-sm py-3 px-6 w-full sm:w-auto">Kirim Penilaian</button>
                    <button type="button" @click="showModal = false" class="btn-ghost text-sm py-3 w-full sm:w-auto">Batal</button>
                </div>
            </form>
        </x-modal>
    </div>
</x-layouts.app>
