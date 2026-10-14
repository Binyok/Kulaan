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
        <!-- Header -->
        <div class="mb-6">
            <div class="bg-gradient-to-r from-brand-forest to-brand-primary text-white rounded-2xl p-5 sm:p-7 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[0.6875rem] font-semibold bg-white/15 text-white">
                            Pesanan Kolektif
                        </span>
                        <h1 class="font-display font-bold text-2xl sm:text-3xl mt-2 tracking-tight">Riwayat Pesanan &amp; Tagihan</h1>
                        <p class="text-xs sm:text-sm text-white/80 mt-1">
                            Invoice, status pembayaran, dan ulasan distributor mitra pengadaanmu.
                        </p>
                    </div>
                    <a href="{{ route('umkm.catalog.index') }}" class="btn-accent text-xs font-bold px-4 py-2.5 rounded-xl shrink-0 text-center">
                        + Pesan Lagi
                    </a>
                </div>
            </div>
        </div>

        @php
            $hasOrders = $orderItems->isNotEmpty();
            $unpaidItems = $orderItems->filter(fn($i) => $i->payment_status->value === 'UNPAID');
            $paidItems   = $orderItems->filter(fn($i) => $i->payment_status->value !== 'UNPAID');
        @endphp

        @if ($hasOrders)
            <!-- Pola C: Section "Menunggu Pembayaran" di Atas (Urgent) -->
            @if ($unpaidItems->isNotEmpty())
                <section class="mb-6">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-brand-brick"></span>
                        <h2 class="text-xs font-bold text-brand-brick uppercase tracking-wider">
                            Menunggu Pembayaran ({{ $unpaidItems->count() }})
                        </h2>
                    </div>
                    <div class="space-y-3">
                        @foreach ($unpaidItems as $item)
                            <!-- Kartu vertikal tagihan: header (nomor+badge), isi (item+qty), footer (total+aksi utama) -->
                            <article class="bg-white rounded-2xl border border-brand-brick/30 shadow-xs overflow-hidden">
                                <div class="flex items-center justify-between px-4 sm:px-5 py-3 border-b border-brand-line/60 bg-brand-cream/40">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="font-mono text-xs text-brand-slate">{{ $item->order->order_number }}</span>
                                        <span class="badge {{ $item->payment_status->badgeClass() }}">{{ $item->payment_status->label() }}</span>
                                    </div>
                                    <span class="text-[0.625rem] text-brand-slate">{{ $item->created_at->format('d M Y') }}</span>
                                </div>
                                <div class="px-4 sm:px-5 py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-bold text-brand-dark text-sm sm:text-base">{{ $item->product->name }}</p>
                                        <p class="text-xs text-brand-slate mt-0.5">
                                            {{ number_format($item->quantity, 0) }} {{ $item->product->unit }}
                                            &bull; Distributor: {{ $item->order->distributor->company_name }}
                                        </p>
                                        @if ($item->savings_amount > 0)
                                            <p class="text-[0.6875rem] font-semibold text-brand-brass mt-1">
                                                Hemat Rp {{ number_format($item->savings_amount, 0, ',', '.') }} vs eceran
                                            </p>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-3 shrink-0">
                                        <span class="font-extrabold text-brand-primary text-base sm:text-xl tabular-nums">
                                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                        </span>
                                        <a href="{{ route('payment.show', $item) }}" class="btn-primary text-xs font-bold py-2.5 px-4 rounded-xl shadow-sm">
                                            Bayar Sekarang &rarr;
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- Riwayat Pesanan Lunas (Pola C) -->
            @if ($paidItems->isNotEmpty())
                <section>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-brand-emerald"></span>
                        <h2 class="text-xs font-bold text-brand-slate uppercase tracking-wider">
                            Riwayat Pesanan Lunas ({{ $paidItems->count() }})
                        </h2>
                    </div>
                    <div class="space-y-3">
                        @foreach ($paidItems as $item)
                            @php $shipment = $item->order->shipments->first(); @endphp
                            <article class="bg-white rounded-2xl border border-brand-line shadow-xs overflow-hidden">
                                <div class="flex items-center justify-between px-4 sm:px-5 py-3 border-b border-brand-line/60">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="font-mono text-xs text-brand-slate">{{ $item->order->order_number }}</span>
                                        <span class="badge {{ $item->payment_status->badgeClass() }}">{{ $item->payment_status->label() }}</span>
                                    </div>
                                    <span class="text-[0.625rem] text-brand-slate">{{ $item->created_at->format('d M Y') }}</span>
                                </div>
                                <div class="px-4 sm:px-5 py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-bold text-brand-dark text-sm">{{ $item->product->name }}</p>
                                        <p class="text-xs text-brand-slate mt-0.5">
                                            {{ number_format($item->quantity, 0) }} {{ $item->product->unit }}
                                            &bull; {{ $item->order->distributor->company_name }}
                                        </p>
                                        @if ($item->savings_amount > 0)
                                            <p class="text-[0.6875rem] font-semibold text-brand-brass mt-0.5">
                                                Hemat Rp {{ number_format($item->savings_amount, 0, ',', '.') }}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2 sm:gap-3 shrink-0 flex-wrap sm:flex-nowrap">
                                        <span class="font-extrabold text-brand-dark text-sm tabular-nums">
                                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                        </span>

                                        @if ($item->review)
                                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-brand-brass bg-brand-ochre-soft px-2.5 py-1 rounded-full border border-brand-accent/30">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.9 6.26 6.6.56-5 4.36 1.5 6.45L12 16.9 5.99 19.63l1.5-6.45-5-4.36 6.6-.56L12 2z"/></svg>
                                                {{ $item->review->rating }}/5
                                            </span>
                                        @elseif (in_array($item->order->status->value, ['DELIVERED', 'COMPLETED'], true))
                                            <button type="button"
                                                    @click="openRatingModal({{ $item->id }}, '{{ addslashes($item->order->distributor->company_name) }}', '{{ route('umkm.orders.review', $item) }}')"
                                                    class="min-h-9 px-3 inline-flex items-center gap-1 text-[11px] font-semibold text-brand-brass bg-brand-ochre-soft rounded-lg border border-brand-accent/30 transition-colors cursor-pointer">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.9 6.26 6.6.56-5 4.36 1.5 6.45L12 16.9 5.99 19.63l1.5-6.45-5-4.36 6.6-.56L12 2z"/></svg>
                                                Beri Ulasan
                                            </button>
                                        @endif

                                        @if ($shipment)
                                            <a href="{{ route('umkm.tracking.show', $shipment) }}" class="text-xs font-bold text-brand-primary hover:underline">
                                                Lacak Kurir &rarr;
                                            </a>
                                        @endif
                                        <a href="{{ route('payment.show', $item) }}" class="btn-ghost text-xs py-1.5 px-3 font-semibold">
                                            Invoice
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            <div class="pt-4">{{ $orderItems->links() }}</div>
        @else
            <section class="bg-white rounded-2xl border border-brand-line p-10 sm:p-16 text-center max-w-lg mx-auto shadow-xs">
                <div class="w-14 h-14 rounded-2xl bg-brand-mint text-brand-primary flex items-center justify-center mx-auto font-display font-bold text-2xl">K</div>
                <h2 class="font-display font-bold text-brand-dark text-lg sm:text-xl mt-4">Belum Ada Riwayat Pesanan</h2>
                <p class="text-xs sm:text-sm text-brand-slate mt-2 leading-relaxed max-w-sm mx-auto">
                    Setelah pengajuanmu ter-matching dan dibayar, invoice dan ulasan mitra tampil di sini.
                </p>
                <a href="{{ route('umkm.catalog.index') }}" class="btn-primary inline-flex text-sm font-bold px-5 py-2.5 mt-5">
                    Buka Katalog &rarr;
                </a>
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
                                <svg class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" :class="star <= rating ? 'text-brand-accent' : 'text-brand-slate/30'"><path d="M12 2l2.9 6.26 6.6.56-5 4.36 1.5 6.45L12 16.9 5.99 19.63l1.5-6.45-5-4.36 6.6-.56L12 2z"/></svg>
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
