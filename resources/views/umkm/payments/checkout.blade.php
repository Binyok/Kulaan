<x-layouts.app>
    <x-slot name="title">Pembayaran Invoice {{ $orderItem->order->order_number }}</x-slot>

    <div x-data="{
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

        <!-- Header -->
        <div class="mb-6">
            <div class="bg-gradient-to-r from-brand-forest to-brand-primary text-white rounded-2xl p-5 sm:p-7 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <a href="{{ route('umkm.orders.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-white/80 hover:text-white transition-colors">
                            &larr; Riwayat Pesanan
                        </a>
                        <h1 class="font-display font-bold text-2xl sm:text-3xl mt-2 tracking-tight">Invoice Pembayaran</h1>
                        <p class="text-xs text-white/80 mt-1">
                            No. <span class="font-mono font-bold">{{ $orderItem->order->order_number }}</span> &bull; Mitra {{ $orderItem->order->distributor->company_name }}
                        </p>
                    </div>
                    <span class="badge {{ $orderItem->payment_status->badgeClass() }} text-xs py-1.5 px-4 self-start sm:self-auto">
                        {{ $orderItem->payment_status->label() }}
                    </span>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="p-4 rounded-2xl bg-brand-mint text-brand-primary border border-brand-primary/15 text-xs font-bold flex items-center gap-2.5 mb-6">
                <span class="w-2.5 h-2.5 rounded-full bg-brand-emerald shrink-0"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($orderItem->payment_status->value !== 'PAID')
            <!-- Pola D: 2 Kolom (Metode Bayar di Kiri, Ringkasan Sticky di Kanan) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <!-- Kolom Kiri: Pilihan Metode Pembayaran (7 Kolom) -->
                <div class="lg:col-span-7 space-y-5">
                    <!-- Kartu Pemilihan Metode Visual -->
                    <section class="bg-white rounded-2xl border border-brand-line p-5 sm:p-6 shadow-xs">
                        <div class="flex items-center justify-between gap-3 mb-4">
                            <h2 class="text-xs font-bold uppercase tracking-wider text-brand-dark">Pilih Metode Pembayaran</h2>
                            <span class="inline-flex items-center gap-1.5 text-[0.6875rem] font-bold text-brand-brass bg-brand-ochre-soft px-3 py-1 rounded-full border border-brand-accent/25">
                                <span class="font-mono tabular-nums" x-text="`${String(minutesLeft).padStart(2, '0')}:${String(secondsLeft).padStart(2, '0')}`"></span>
                            </span>
                        </div>

                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" @click="selectedMethod = 'qris'"
                                    :class="selectedMethod === 'qris' ? 'border-brand-primary bg-brand-mint/50 text-brand-primary font-bold ring-2 ring-brand-primary/20' : 'border-brand-line bg-white text-brand-slate hover:bg-brand-cream/50'"
                                    class="border rounded-xl p-3 text-center text-xs transition-all cursor-pointer flex flex-col items-center justify-center gap-1 min-h-16">
                                <span class="font-extrabold text-sm">QRIS</span>
                                <span class="text-[0.625rem] opacity-75">Instan &bull; Gratis</span>
                            </button>
                            <button type="button" @click="selectedMethod = 'bca_va'"
                                    :class="selectedMethod === 'bca_va' ? 'border-brand-primary bg-brand-mint/50 text-brand-primary font-bold ring-2 ring-brand-primary/20' : 'border-brand-line bg-white text-brand-slate hover:bg-brand-cream/50'"
                                    class="border rounded-xl p-3 text-center text-xs transition-all cursor-pointer flex flex-col items-center justify-center gap-1 min-h-16">
                                <span class="font-extrabold text-sm">BCA VA</span>
                                <span class="text-[0.625rem] opacity-75">Virtual Account</span>
                            </button>
                            <button type="button" @click="selectedMethod = 'bni_va'"
                                    :class="selectedMethod === 'bni_va' ? 'border-brand-primary bg-brand-mint/50 text-brand-primary font-bold ring-2 ring-brand-primary/20' : 'border-brand-line bg-white text-brand-slate hover:bg-brand-cream/50'"
                                    class="border rounded-xl p-3 text-center text-xs transition-all cursor-pointer flex flex-col items-center justify-center gap-1 min-h-16">
                                <span class="font-extrabold text-sm">BNI / Mandiri</span>
                                <span class="text-[0.625rem] opacity-75">Virtual Account</span>
                            </button>
                        </div>

                        <!-- Panel Detail QRIS -->
                        <div x-show="selectedMethod === 'qris'" class="mt-5 text-center">
                            <div class="p-4 bg-white border border-brand-line rounded-2xl inline-block shadow-xs">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode('QRIS:KULAAN-MIDTRANS-'. $payment->payment_reference .'-RP'. (int)$orderItem->subtotal) }}"
                                     alt="QRIS Code Kula'an" loading="lazy" class="w-44 h-44 mx-auto rounded-lg">
                                <p class="text-[10px] font-mono text-brand-slate mt-2 font-bold">NMID: ID1020038921820 &bull; KULAAN</p>
                            </div>
                            <ol class="text-left text-xs text-brand-slate space-y-1.5 bg-brand-cream/60 p-4 rounded-xl border border-brand-line/60 mt-4 list-decimal list-inside">
                                <li>Buka aplikasi mobile banking atau e-wallet (GoPay, OVO, ShopeePay, Dana, dll).</li>
                                <li>Scan QR di atas, pastikan nominal sesuai: <strong class="text-brand-dark">Rp {{ number_format($orderItem->subtotal, 0, ',', '.') }}</strong>.</li>
                                <li>Masukkan PIN transaksi dan simpan bukti bayar.</li>
                            </ol>
                        </div>

                        <!-- Panel Detail BCA VA -->
                        <div x-show="selectedMethod === 'bca_va'" x-cloak class="mt-5 space-y-3">
                            <div class="p-4 bg-brand-cream/60 rounded-xl border border-brand-line flex items-center justify-between gap-3">
                                <div>
                                    <span class="text-[0.625rem] font-bold text-brand-slate uppercase tracking-wider block">Nomor Rekening BCA Virtual Account</span>
                                    <span class="font-mono text-xl font-extrabold text-brand-dark tracking-wider block mt-0.5">{{ $bcaVaNumber }}</span>
                                </div>
                                <button type="button" @click="copyToClipboard('{{ $bcaVaNumber }}', 'BCA')"
                                        class="btn-ghost text-xs py-1.5 px-3 bg-white font-bold text-brand-primary border-brand-primary/20 shrink-0">
                                    Salin Nomor
                                </button>
                            </div>
                            <p class="text-[10px] text-brand-primary font-bold" x-show="copiedText === 'BCA'">Nomor BCA VA berhasil disalin ke clipboard!</p>
                            <ol class="text-left text-xs text-brand-slate space-y-1.5 bg-brand-cream/40 p-4 rounded-xl border border-brand-line/40 list-decimal list-inside">
                                <li>Masuk ke m-BCA &rarr; menu <strong>m-Transfer</strong> &rarr; <strong>BCA Virtual Account</strong>.</li>
                                <li>Masukkan nomor VA di atas. Pastikan nama penerima <strong>Kula'an &bull; {{ $orderItem->umkm->business_name }}</strong>.</li>
                                <li>Masukkan PIN transaksi Anda.</li>
                            </ol>
                        </div>

                        <!-- Panel Detail BNI/Mandiri VA -->
                        <div x-show="selectedMethod === 'bni_va'" x-cloak class="mt-5 space-y-3">
                            <div class="p-4 bg-brand-cream/60 rounded-xl border border-brand-line flex items-center justify-between gap-3">
                                <div>
                                    <span class="text-[0.625rem] font-bold text-brand-slate uppercase tracking-wider block">Nomor BNI / Mandiri Virtual Account</span>
                                    <span class="font-mono text-xl font-extrabold text-brand-dark tracking-wider block mt-0.5">{{ $bniVaNumber }}</span>
                                </div>
                                <button type="button" @click="copyToClipboard('{{ $bniVaNumber }}', 'BNI')"
                                        class="btn-ghost text-xs py-1.5 px-3 bg-white font-bold text-brand-primary border-brand-primary/20 shrink-0">
                                    Salin Nomor
                                </button>
                            </div>
                            <p class="text-[10px] text-brand-primary font-bold" x-show="copiedText === 'BNI'">Nomor VA berhasil disalin ke clipboard!</p>
                        </div>
                    </section>
                </div>

                <!-- Kolom Kanan: Ringkasan Biaya & Sticky Tombol Bayar (5 Kolom) -->
                <div class="lg:col-span-5 space-y-4">
                    <section class="bg-white rounded-2xl border border-brand-line p-5 sm:p-6 shadow-xs sticky top-24">
                        <h2 class="font-display font-bold text-brand-dark text-base border-b border-brand-line pb-3">
                            Ringkasan Pembayaran
                        </h2>

                        <!-- Rincian Produk -->
                        <div class="py-3 border-b border-brand-line/60">
                            <p class="font-bold text-brand-dark text-sm">{{ $orderItem->product->name }}</p>
                            <p class="text-xs text-brand-slate mt-0.5">
                                {{ number_format($orderItem->quantity, 0) }} {{ $orderItem->product->unit }} &times; Rp {{ number_format($orderItem->unit_price, 0, ',', '.') }}
                            </p>
                            <p class="text-[0.6875rem] text-brand-slate mt-1">
                                Kirim ke: {{ $orderItem->delivery_address }}, {{ $orderItem->delivery_district }}
                            </p>
                        </div>

                        <dl class="space-y-2.5 mt-4 text-xs">
                            <div class="flex items-center justify-between">
                                <dt class="text-brand-slate">Subtotal</dt>
                                <dd class="font-bold text-brand-dark tabular-nums">
                                    Rp {{ number_format($orderItem->subtotal, 0, ',', '.') }}
                                </dd>
                            </div>
                            @if ($orderItem->savings_amount > 0)
                                <div class="flex items-center justify-between text-brand-primary">
                                    <dt class="font-semibold">Hemat Kolektif</dt>
                                    <dd class="font-bold tabular-nums">
                                        - Rp {{ number_format($orderItem->savings_amount, 0, ',', '.') }}
                                    </dd>
                                </div>
                            @endif
                            <div class="pt-3 border-t border-brand-line flex items-baseline justify-between">
                                <dt class="text-sm font-bold text-brand-dark">Total Tagihan</dt>
                                <dd class="font-display font-extrabold text-brand-primary text-2xl tabular-nums">
                                    Rp {{ number_format($orderItem->subtotal, 0, ',', '.') }}
                                </dd>
                            </div>
                        </dl>

                        <!-- Aksi Pembayaran & Verifikasi -->
                        <div class="space-y-2.5 mt-5 pt-4 border-t border-brand-line">
                            <form method="POST" action="{{ route('payment.simulate', $orderItem) }}" data-loading>
                                @csrf
                                <input type="hidden" name="payment_method" :value="selectedMethod">
                                <button type="submit" class="btn-primary w-full justify-center py-3.5 text-sm font-bold rounded-xl shadow-sm">
                                    Konfirmasi Dana Masuk (Simulasi) &rarr;
                                </button>
                            </form>

                            <button type="button" @click="checkPaymentStatus(true)" :disabled="isCheckingStatus"
                                    class="btn-ghost w-full justify-center py-2.5 text-xs font-bold rounded-xl border border-brand-line">
                                <span x-show="!isCheckingStatus">Cek Status Gateway</span>
                                <span x-show="isCheckingStatus" x-cloak>Memeriksa server...</span>
                            </button>
                        </div>

                        <p class="text-[0.625rem] text-center text-brand-slate mt-3 leading-relaxed">
                            Dana aman di escrow gateway Kula'an dan baru diteruskan ke distributor setelah konfirmasi pesanan siap dikirim.
                        </p>
                    </section>
                </div>
            </div>
        @else
            <!-- Receipt / Lunas -->
            <section class="bg-white rounded-2xl border border-brand-line p-8 sm:p-12 text-center max-w-md mx-auto shadow-xs">
                <span class="w-16 h-16 rounded-full bg-brand-mint text-brand-emerald inline-flex items-center justify-center font-display font-bold text-2xl mx-auto">
                    &check;
                </span>
                <h2 class="font-display font-bold text-2xl text-brand-dark tracking-tight mt-4">Pembayaran Lunas</h2>
                <p class="text-xs text-brand-slate mt-1">
                    Invoice <span class="font-mono font-bold">{{ $orderItem->order->order_number }}</span> telah terbayar.
                </p>

                <dl class="mt-6 p-4 bg-brand-cream/60 rounded-xl text-xs space-y-2 text-left border border-brand-line/60">
                    <div class="flex justify-between">
                        <dt class="text-brand-slate">Metode Bayar</dt>
                        <dd class="font-bold text-brand-dark uppercase">{{ $payment->payment_method ?? 'QRIS' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-brand-slate">Waktu Bayar</dt>
                        <dd class="font-medium text-brand-dark">{{ $payment->paid_at?->format('d M Y, H:i') ?? now()->format('d M Y, H:i') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-brand-slate">Total Lunas</dt>
                        <dd class="font-extrabold text-brand-primary tabular-nums">Rp {{ number_format($orderItem->subtotal, 0, ',', '.') }}</dd>
                    </div>
                </dl>

                @php $shipment = $orderItem->order->shipments->first(); @endphp
                @if ($shipment)
                    <a href="{{ route('umkm.tracking.show', $shipment) }}" class="btn-primary w-full justify-center py-3 text-sm font-bold mt-6 inline-flex">
                        Pantau Kurir di Peta Live &rarr;
                    </a>
                @else
                    <a href="{{ route('umkm.orders.index') }}" class="btn-ghost w-full justify-center py-3 text-xs font-bold text-brand-primary mt-6 inline-flex border border-brand-primary/20">
                        Kembali ke Riwayat Pesanan
                    </a>
                @endif
            </section>
        @endif
    </div>
</x-layouts.app>
