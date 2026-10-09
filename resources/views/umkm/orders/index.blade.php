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
    }" class="space-y-6 sm:space-y-8 animate-fade-up">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <span class="eyebrow">Pesanan Kolektif</span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-brand-dark tracking-tight mt-1.5">Riwayat Pesanan &amp; Tagihan</h1>
                <p class="text-[0.8125rem] text-brand-slate mt-1">Daftar lengkap invoice individual, status pesanan, dan rating distributor mitra pengadaan.</p>
            </div>
        </div>

        <!-- Table of Order Items & Invoices (Double-Bezel) -->
        <div class="card-shell">
            <div class="card-core p-4 sm:p-7 space-y-5">
                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <table class="table-premium min-w-[750px]">
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
                            @forelse ($orderItems as $item)
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
                                    <td class="text-right font-bold text-brand-accent">
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
                                                class="text-[11px] font-semibold text-amber-600 hover:text-amber-700 bg-amber-50 hover:bg-amber-100 px-2.5 py-1 rounded-md border border-amber-200 transition-colors">
                                                ★ Beri Ulasan
                                            </button>
                                        @else
                                            <span class="text-xs text-brand-slate">&mdash;</span>
                                        @endif
                                    </td>
                                    <td class="text-right whitespace-nowrap">
                                        <a href="{{ route('payment.show', $item) }}" 
                                            class="{{ $item->payment_status->value === 'UNPAID' ? 'btn-primary' : 'btn-ghost text-brand-primary font-bold' }} text-xs py-1.5 px-3.5 inline-flex items-center gap-1 shadow-sm">
                                            <span>{{ $item->payment_status->value === 'UNPAID' ? 'Bayar Invoice' : 'Lihat Invoice' }}</span>
                                            &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-12 text-center text-brand-slate text-[0.8125rem]">
                                        Belum ada riwayat pesanan kolektif.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pt-3 border-t border-black/[0.04]">
                    {{ $orderItems->links() }}
                </div>
            </div>
        </div>

        <!-- Rating Modal -->
        <div x-show="showModal" 
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            @keydown.escape.window="showModal = false">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-black/5 space-y-5" @click.away="showModal = false">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="font-extrabold text-brand-dark text-lg">Beri Penilaian Distributor</h3>
                        <p class="text-xs text-brand-slate mt-0.5" x-text="distributorName"></p>
                    </div>
                    <button type="button" @click="showModal = false" class="text-brand-slate hover:text-brand-dark text-lg font-bold">&times;</button>
                </div>

                <form :action="reviewAction" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1.5">Rating Bintang (Kualitas Layanan &amp; Produk)</label>
                        <div class="flex items-center gap-2">
                            <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                                <button type="button" @click="rating = star" class="text-2xl transition-transform hover:scale-110 focus:outline-none" :class="star <= rating ? 'text-amber-400' : 'text-slate-200'">
                                    ★
                                </button>
                            </template>
                            <span class="text-xs font-bold text-brand-slate ml-2" x-text="rating + ' / 5 Bintang'"></span>
                        </div>
                        <input type="hidden" name="rating" :value="rating">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1.5">Catatan / Ulasan Tambahan (Opsional)</label>
                        <textarea name="comment" rows="3" x-model="comment" placeholder="Bagikan pengalaman transaksi, kualitas bahan baku, dan ketepatan waktu pengiriman..." class="input-field text-xs w-full"></textarea>
                    </div>

                    <div class="flex justify-end gap-2.5 pt-2">
                        <button type="button" @click="showModal = false" class="btn-ghost text-xs py-2 px-4">Batal</button>
                        <button type="submit" class="btn-primary text-xs py-2 px-5 font-bold">Kirim Penilaian</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
