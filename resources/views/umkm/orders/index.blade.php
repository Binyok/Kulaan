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
    }">
        {{-- Forest band: page header --}}
        <div class="-mx-3 sm:-mx-6 lg:-mx-8 -mt-6 sm:-mt-8 mb-6 sm:mb-8">
            <div class="texture-grain bg-brand-forest text-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
                    <div class="max-w-2xl" data-reveal>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[0.625rem] font-semibold bg-white/10 text-[#D8E8DD] uppercase tracking-[0.14em]">
                            Pesanan Kolektif
                        </span>
                        <h1 class="font-display font-semibold tracking-tight text-balance text-4xl sm:text-5xl mt-4">
                            Riwayat Pesanan &amp; Tagihan
                        </h1>
                        <p class="text-sm text-white/70 leading-relaxed mt-3">
                            Semua invoice individualmu, status pembayaran, dan ulasan buat distributor mitra — rapi dalam satu daftar.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6 sm:space-y-8">
            @php $hasOrders = $orderItems->isNotEmpty(); @endphp

            @if ($hasOrders)
                <section class="bg-white rounded-[20px] border border-brand-line p-5 sm:p-7" data-reveal>
                    <div class="divide-y divide-black/[0.05]">
                        @foreach ($orderItems as $item)
                            <div class="py-4 flex gap-4 items-start hover:bg-brand-cream/60 -mx-2 px-2 sm:-mx-4 sm:px-4 rounded-xl transition-colors">
                                <span class="w-11 h-11 rounded-full bg-brand-mint text-brand-primary flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 17h6m-5 4h4M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 8h6M9 12h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="font-bold text-brand-dark text-sm">{{ $item->product->name }}</p>
                                            <p class="font-mono text-[0.6875rem] text-brand-slate mt-0.5">{{ $item->order->order_number }} &bull; {{ $item->created_at->format('d M Y') }}</p>
                                            <p class="text-[0.6875rem] text-brand-slate mt-0.5">Distributor: {{ $item->order->distributor->company_name }} &bull; {{ number_format($item->quantity, 0) }} {{ $item->product->unit }}</p>
                                        </div>
                                        <span class="badge {{ $item->payment_status->badgeClass() }} shrink-0">{{ $item->payment_status->label() }}</span>
                                    </div>
                                    <div class="flex flex-wrap items-center justify-between gap-3 mt-2.5">
                                        <div class="text-[0.75rem]">
                                            <span class="font-extrabold text-brand-primary tabular-nums">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                            <span class="text-brand-brass font-semibold ml-2">Hemat Rp {{ number_format($item->savings_amount, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0">
                                            @if ($item->review)
                                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-full border border-amber-200" title="{{ $item->review->comment }}">
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.9 6.26 6.6.56-5 4.36 1.5 6.45L12 16.9 5.99 19.63l1.5-6.45-5-4.36 6.6-.56L12 2z"/></svg>
                                                    {{ $item->review->rating }}/5
                                                </span>
                                            @elseif (in_array($item->order->status->value, ['DELIVERED', 'COMPLETED'], true))
                                                <button type="button"
                                                    @click="openRatingModal({{ $item->id }}, '{{ addslashes($item->order->distributor->company_name) }}', '{{ route('umkm.orders.review', $item) }}')"
                                                    class="min-h-11 px-3 inline-flex items-center gap-1.5 text-[11px] font-semibold text-amber-700 hover:text-amber-800 bg-amber-50 hover:bg-amber-100 rounded-lg border border-amber-200 transition-colors cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.9 6.26 6.6.56-5 4.36 1.5 6.45L12 16.9 5.99 19.63l1.5-6.45-5-4.36 6.6-.56L12 2z"/></svg>
                                                    Beri Ulasan
                                                </button>
                                            @endif
                                            <a href="{{ route('payment.show', $item) }}"
                                                class="{{ $item->payment_status->value === 'UNPAID' ? 'btn-primary' : 'btn-ghost text-brand-primary font-bold' }} text-xs py-2 px-4">
                                                {{ $item->payment_status->value === 'UNPAID' ? 'Bayar Invoice' : 'Lihat Invoice' }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-4 mt-2 border-t border-black/[0.04]">
                        {{ $orderItems->links() }}
                    </div>
                </section>
            @else
                <section class="bg-white rounded-[20px] border border-brand-line px-6 py-12 text-center" data-reveal>
                    <span class="w-14 h-14 rounded-full bg-brand-mint text-brand-primary inline-flex items-center justify-center">
                        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 17h6m-5 4h4M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <p class="font-bold text-brand-dark mt-4">Belum ada riwayat pesanan kolektif.</p>
                    <p class="text-[0.8125rem] text-brand-slate mt-1.5 max-w-md mx-auto">Setelah pengajuanmu ter-matching dan dibayar, invoice dan ulasan mitra tampil di sini.</p>
                    <a href="{{ route('umkm.requests.index') }}" class="btn-primary text-sm mt-5">Ajukan Kebutuhan</a>
                </section>
            @endif

            <!-- Rating Modal -->
            <x-modal open="showModal" size="sm" title="Beri Penilaian Distributor">
                <p class="text-xs text-brand-slate mt-4" x-text="distributorName"></p>

                <form :action="reviewAction" method="POST" class="pt-4 space-y-4" data-loading>
                    @csrf
                    <div>
                        <label class="field-label">Rating bintang (kualitas layanan &amp; produk)</label>
                        <div class="flex items-center gap-1">
                            <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                                <button type="button" @click="rating = star"
                                    class="min-w-11 min-h-11 flex items-center justify-center transition-transform hover:scale-110 rounded-lg cursor-pointer"
                                    :aria-label="star + ' bintang'">
                                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" :class="star <= rating ? 'text-amber-400' : 'text-slate-300'"><path d="M12 2l2.9 6.26 6.6.56-5 4.36 1.5 6.45L12 16.9 5.99 19.63l1.5-6.45-5-4.36 6.6-.56L12 2z"/></svg>
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
    </div>
</x-layouts.app>
