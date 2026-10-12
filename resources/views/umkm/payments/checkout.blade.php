<x-layouts.app>
    <x-slot name="title">Invoice Tagihan Kolektif</x-slot>

    <div class="max-w-3xl mx-auto" x-data="{
        selectedMethod: 'qris',
        isCheckingStatus: false,
        copiedText: '',
        minutesLeft: 14,
        secondsLeft: 59,
        isPaid: {{ $orderItem->payment_status->value === 'PAID' ? 'true' : 'false' }},
        timer: null,

        init() {
            if (!this.isPaid) {
                this.timer = setInterval(() => {
                    if (this.secondsLeft > 0) {
                        this.secondsLeft--;
                    } else if (this.minutesLeft > 0) {
                        this.minutesLeft--;
                        this.secondsLeft = 59;
                    }
                }, 1000);

                // Auto-poll status every 6 seconds
                setInterval(() => {
                    this.checkPaymentStatus(false);
                }, 6000);
            }
        },

        copyToClipboard(text, label) {
            navigator.clipboard.writeText(text);
            this.copiedText = label;
            setTimeout(() => this.copiedText = '', 2500);
        },

        checkPaymentStatus(showFeedback = true) {
            if (this.isPaid) return;
            if (showFeedback) this.isCheckingStatus = true;

            axios.get('{{ route('payment.status', $orderItem) }}').then(res => {
                if (showFeedback) this.isCheckingStatus = false;
                if (res.data.is_paid) {
                    this.isPaid = true;
                    window.location.reload();
                } else if (showFeedback) {
                    alert('Status Pembayaran: Belum Terdeteksi Masuk di Server Gateway. Silakan selesaikan transfer atau gunakan tombol konfirmasi transfer.');
                }
            }).catch(err => {
                if (showFeedback) this.isCheckingStatus = false;
            });
        }
    }">
        {{-- Forest band: invoice header --}}
        <div class="-mx-3 sm:-mx-6 lg:-mx-8 -mt-6 sm:-mt-8 mb-6 sm:mb-8">
            <div class="texture-grain bg-brand-forest text-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12">
                    <div class="max-w-3xl mx-auto" data-reveal>
                        <a href="{{ route('umkm.orders.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-white/70 hover:text-white transition-colors">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M19 12H5m7-7-7 7 7 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Kembali ke Riwayat Pesanan
                        </a>
                        <div class="flex flex-wrap items-center justify-between gap-4 mt-4">
                            <div>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[0.625rem] font-semibold bg-white/10 text-[#D8E8DD] uppercase tracking-[0.14em]">
                                    Invoice Pembayaran
                                </span>
                                <h1 class="font-mono font-bold text-xl sm:text-2xl mt-3">{{ $orderItem->order->order_number }}</h1>
                                <p class="text-xs text-white/70 mt-1">{{ $orderItem->created_at->format('d M Y, H:i') }} WITA &bull; {{ $orderItem->order->distributor->company_name }}</p>
                            </div>
                            <span class="badge {{ $orderItem->payment_status->badgeClass() }} text-xs py-1.5 px-4">
                                {{ $orderItem->payment_status->label() }}
                            </span>
                        </div>
                        <p class="font-display font-extrabold text-3xl sm:text-4xl tabular-nums mt-5">Rp {{ number_format($orderItem->subtotal, 0, ',', '.') }}</p>
                        <p class="text-[0.6875rem] text-white/60 mt-1 font-mono">Ref: {{ $payment->payment_reference }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            @if (session('success'))
                <div class="p-4 rounded-2xl bg-brand-mint text-brand-primary border border-brand-primary/15 text-xs font-bold flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-brand-emerald shrink-0"></span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if ($orderItem->payment_status->value !== 'PAID')
                {{-- Rincian tagihan --}}
                <section class="bg-white rounded-[20px] border border-brand-line p-5 sm:p-7" data-reveal>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-brand-dark mb-4">Rincian tagihan</h2>
                    <div class="flex gap-4 items-start">
                        <span class="w-11 h-11 rounded-full bg-brand-mint text-brand-primary flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="m3.3 7 8.7 5 8.7-5M12 22V12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-brand-dark text-sm">{{ $orderItem->product->name }}</p>
                            <p class="text-xs text-brand-slate mt-0.5">{{ number_format($orderItem->quantity, 0) }} {{ $orderItem->product->unit }} &times; Rp {{ number_format($orderItem->unit_price, 0, ',', '.') }} <span class="text-brand-primary font-semibold">(grosir kolektif)</span></p>
                            <p class="text-[0.6875rem] text-brand-slate mt-1.5">{{ $orderItem->delivery_address }}, {{ $orderItem->delivery_district }}</p>
                        </div>
                        <p class="font-extrabold text-brand-dark tabular-nums shrink-0">Rp {{ number_format($orderItem->subtotal, 0, ',', '.') }}</p>
                    </div>
                    <div class="mt-4 p-4 savings-card flex justify-between items-center gap-3">
                        <div>
                            <p class="text-[0.6875rem] font-bold text-brand-brass uppercase tracking-[0.1em]">Kamu hemat</p>
                            <p class="text-[0.6875rem] text-brand-slate mt-0.5">vs eceran Rp {{ number_format($orderItem->quantity * $orderItem->benchmark_retail_price, 0, ',', '.') }}</p>
                        </div>
                        <p class="text-lg font-extrabold text-brand-brass tabular-nums shrink-0">Rp {{ number_format($orderItem->savings_amount, 0, ',', '.') }}</p>
                    </div>
                </section>

                {{-- Metode pembayaran --}}
                <section class="bg-brand-sand/70 rounded-[20px] p-5 sm:p-7" data-reveal>
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-brand-dark">Pilih metode bayar</h2>
                        <span class="inline-flex items-center gap-2 text-[0.6875rem] font-bold text-brand-brass bg-white px-3 py-1.5 rounded-full border border-brand-accent/25">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/><path d="M12 7v5l3 3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                            <span class="font-mono tabular-nums" x-text="`${String(minutesLeft).padStart(2, '0')}:${String(secondsLeft).padStart(2, '0')}`"></span>
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" @click="selectedMethod = 'qris'"
                            :class="selectedMethod === 'qris' ? 'border-brand-primary bg-white text-brand-primary font-bold ring-1 ring-brand-primary/25 shadow-sm' : 'border-black/[0.08] bg-white/60 text-brand-slate'"
                            class="border rounded-xl min-h-11 px-2 py-2.5 text-center text-xs transition-all cursor-pointer">
                            QRIS Instan
                        </button>
                        <button type="button" @click="selectedMethod = 'bca_va'"
                            :class="selectedMethod === 'bca_va' ? 'border-brand-primary bg-white text-brand-primary font-bold ring-1 ring-brand-primary/25 shadow-sm' : 'border-black/[0.08] bg-white/60 text-brand-slate'"
                            class="border rounded-xl min-h-11 px-2 py-2.5 text-center text-xs transition-all cursor-pointer">
                            BCA VA
                        </button>
                        <button type="button" @click="selectedMethod = 'bni_va'"
                            :class="selectedMethod === 'bni_va' ? 'border-brand-primary bg-white text-brand-primary font-bold ring-1 ring-brand-primary/25 shadow-sm' : 'border-black/[0.08] bg-white/60 text-brand-slate'"
                            class="border rounded-xl min-h-11 px-2 py-2.5 text-center text-xs transition-all cursor-pointer">
                            BNI / Mandiri
                        </button>
                    </div>

                    {{-- QRIS --}}
                    <div x-show="selectedMethod === 'qris'" class="mt-5 text-center">
                        <div class="p-4 bg-white border border-black/[0.08] rounded-2xl inline-block shadow-sm">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode('QRIS:KULAAN-MIDTRANS-'. $payment->payment_reference .'-RP'. (int)$orderItem->subtotal) }}"
                                 alt="QRIS Code Kula'an" class="w-44 h-44 mx-auto rounded-lg">
                            <p class="text-[10px] font-mono text-brand-slate mt-2 font-bold">NMID: ID1020038921820 &bull; KULAAN</p>
                        </div>
                        <ol class="text-left text-xs text-brand-slate space-y-1.5 bg-white/70 p-4 rounded-xl border border-black/[0.04] mt-4 list-none">
                            <li><strong class="text-brand-dark">1.</strong> Buka e-wallet atau mobile banking favoritmu.</li>
                            <li><strong class="text-brand-dark">2.</strong> Scan QR di atas, pastikan nominalnya <strong class="text-brand-dark">Rp {{ number_format($orderItem->subtotal, 0, ',', '.') }}</strong>.</li>
                            <li><strong class="text-brand-dark">3.</strong> Masukkan PIN dan konfirmasi.</li>
                        </ol>
                    </div>

                    {{-- BCA VA --}}
                    <div x-show="selectedMethod === 'bca_va'" x-cloak class="mt-5">
                        <div class="p-4 bg-white rounded-xl border border-black/[0.06] space-y-2">
                            <p class="text-[11px] text-brand-slate font-medium">Nomor BCA Virtual Account:</p>
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-mono text-lg font-extrabold text-brand-dark tracking-wider">{{ $bcaVaNumber }}</span>
                                <button type="button" @click="copyToClipboard('{{ $bcaVaNumber }}', 'BCA')"
                                    class="btn-ghost text-xs py-1.5 px-3 bg-white font-bold text-brand-primary border-brand-primary/20 cursor-pointer shrink-0">
                                    Salin
                                </button>
                            </div>
                            <p class="text-[10px] text-brand-primary font-bold" x-show="copiedText === 'BCA'">Nomor BCA VA tersalin!</p>
                        </div>
                        <ol class="text-xs text-brand-slate space-y-1.5 bg-white/70 p-4 rounded-xl border border-black/[0.04] mt-3 list-none">
                            <li><strong class="text-brand-dark">1.</strong> m-BCA &rarr; Transfer &rarr; BCA Virtual Account.</li>
                            <li><strong class="text-brand-dark">2.</strong> Pastikan nama <strong>Kula'an &bull; {{ $orderItem->umkm->business_name }}</strong>.</li>
                            <li><strong class="text-brand-dark">3.</strong> Masukkan PIN dan konfirmasi.</li>
                        </ol>
                    </div>

                    {{-- BNI/Mandiri VA --}}
                    <div x-show="selectedMethod === 'bni_va'" x-cloak class="mt-5">
                        <div class="p-4 bg-white rounded-xl border border-black/[0.06] space-y-2">
                            <p class="text-[11px] text-brand-slate font-medium">Nomor BNI / Mandiri VA:</p>
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-mono text-lg font-extrabold text-brand-dark tracking-wider">{{ $bniVaNumber }}</span>
                                <button type="button" @click="copyToClipboard('{{ $bniVaNumber }}', 'BNI')"
                                    class="btn-ghost text-xs py-1.5 px-3 bg-white font-bold text-brand-primary border-brand-primary/20 cursor-pointer shrink-0">
                                    Salin
                                </button>
                            </div>
                            <p class="text-[10px] text-brand-primary font-bold" x-show="copiedText === 'BNI'">Nomor VA tersalin!</p>
                        </div>
                    </div>

                    {{-- Verification actions --}}
                    <div class="space-y-3 pt-5 mt-5 border-t border-black/[0.06]">
                        <button type="button" @click="checkPaymentStatus(true)" :disabled="isCheckingStatus"
                            class="btn-ghost w-full justify-center py-3 text-xs font-bold text-brand-dark border-black/[0.12] hover:bg-black/[0.02] cursor-pointer bg-white">
                            <span x-show="!isCheckingStatus">Cek Status Pembayaran</span>
                            <span x-show="isCheckingStatus" x-cloak>Menghubungi server gateway...</span>
                        </button>

                        <form method="POST" action="{{ route('payment.simulate', $orderItem) }}" data-loading>
                            @csrf
                            <input type="hidden" name="payment_method" :value="selectedMethod">
                            <button type="submit" class="btn-primary w-full justify-center py-3.5 text-sm font-bold shadow-md shadow-brand-primary/25 cursor-pointer">
                                Konfirmasi Dana Masuk (Simulasi)
                            </button>
                        </form>

                        <p class="text-[10px] text-center text-brand-slate leading-relaxed">
                            Status dicek otomatis tiap beberapa detik. Setelah lunas, pesanan diteruskan ke distributor.
                        </p>
                    </div>
                </section>
            @else
                {{-- Receipt --}}
                <section class="bg-white rounded-[20px] border border-brand-line p-6 sm:p-8 text-center" data-reveal>
                    <span class="w-16 h-16 rounded-full bg-brand-mint text-brand-emerald inline-flex items-center justify-center">
                        <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <h2 class="text-xl font-extrabold text-brand-dark tracking-tight mt-4">Pembayaran lunas</h2>
                    <p class="text-xs text-brand-slate mt-1.5">Dana terverifikasi aman di escrow gateway Kula'an.</p>

                    <dl class="mt-6 p-4 bg-brand-cream/60 rounded-xl text-xs space-y-2.5 text-left border border-black/[0.04]">
                        <div class="flex justify-between gap-3">
                            <dt class="text-brand-slate">Metode bayar</dt>
                            <dd class="font-bold text-brand-dark uppercase">{{ $payment->payment_method ?? 'QRIS' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-brand-slate">Waktu pelunasan</dt>
                            <dd class="font-medium text-brand-dark tabular-nums">{{ $payment->paid_at?->format('d M Y, H:i') ?? now()->format('d M Y, H:i') }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-brand-slate">Status pesanan</dt>
                            <dd class="font-bold text-brand-emerald">{{ $orderItem->order->status->label() }}</dd>
                        </div>
                    </dl>

                    @php $shipment = $orderItem->order->shipments->first(); @endphp
                    @if ($shipment)
                        <a href="{{ route('umkm.tracking.show', $shipment) }}" class="btn-primary w-full justify-center py-3.5 text-sm font-bold shadow-md shadow-brand-primary/25 mt-6">
                            Pantau Kurir di Peta Live
                        </a>
                    @else
                        <a href="{{ route('umkm.orders.index') }}" class="btn-ghost w-full justify-center py-3 text-xs font-bold text-brand-primary mt-6">
                            Lihat Riwayat Pesanan
                        </a>
                    @endif
                </section>
            @endif
        </div>
    </div>
</x-layouts.app>
