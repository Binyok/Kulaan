<x-layouts.app>
    <x-slot name="title">Invoice Tagihan Kolektif</x-slot>

    <div class="max-w-3xl mx-auto space-y-6 sm:space-y-8 animate-fade-up" x-data="{
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
        <!-- Breadcrumb & Title -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <a href="{{ route('umkm.orders.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-primary hover:underline mb-1">
                    &larr; <span>Kembali ke Riwayat Pesanan</span>
                </a>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-brand-dark tracking-tight">Invoice Pembayaran Kolektif</h1>
                <p class="text-[0.8125rem] text-brand-slate mt-0.5">Tagihan pengadaan kolektif berdasarkan kuantitas pesanan riil usaha Anda.</p>
            </div>

            <!-- Status Pill -->
            <div>
                <span class="badge {{ $orderItem->payment_status->badgeClass() }} text-xs py-1.5 px-4 shadow-sm">
                    {{ $orderItem->payment_status->label() }}
                </span>
            </div>
        </div>

        @if (session('success'))
            <div class="p-4 rounded-2xl bg-brand-mint text-brand-primary border border-brand-primary/15 text-xs font-bold flex items-center gap-2.5 animate-fade-up">
                <span class="w-2.5 h-2.5 rounded-full bg-brand-emerald"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Left Column: Invoice Details & Items -->
            <div class="lg:col-span-7 space-y-6">
                <div class="card-shell">
                    <div class="card-core p-6 sm:p-7 space-y-6">
                        <!-- Invoice Header -->
                        <div class="pb-5 border-b border-black/[0.06] space-y-1">
                            <div class="flex justify-between items-center">
                                <span class="eyebrow">Dokumen Invoice</span>
                                <span class="text-xs text-brand-slate">{{ $orderItem->created_at->format('d M Y, H:i') }} WITA</span>
                            </div>
                            <h2 class="text-lg font-bold text-brand-dark font-mono">{{ $orderItem->order->order_number }}</h2>
                            <p class="text-xs text-brand-slate">
                                Penyedia / Distributor: <strong class="text-brand-dark">{{ $orderItem->order->distributor->company_name }}</strong>
                            </p>
                        </div>

                        <!-- Product Ordered -->
                        <div class="space-y-2.5">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-brand-dark">Rincian Komoditas</h3>
                            <div class="p-4 bg-brand-cream/60 rounded-xl border border-black/[0.04] flex justify-between items-center">
                                <div>
                                    <p class="font-bold text-brand-dark text-sm">{{ $orderItem->product->name }}</p>
                                    <p class="text-xs text-brand-slate mt-0.5">
                                        {{ number_format($orderItem->quantity, 0) }} {{ $orderItem->product->unit }} &times; Rp {{ number_format($orderItem->unit_price, 0, ',', '.') }} (Harga Grosir Kolektif)
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-extrabold text-brand-dark text-base">Rp {{ number_format($orderItem->subtotal, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Savings Badge Card -->
                        <div class="p-4 savings-card flex justify-between items-center">
                            <div>
                                <span class="text-[10px] font-bold text-brand-accent uppercase tracking-wider">Hemat Pembelian Kolektif</span>
                                <p class="text-xs text-brand-slate mt-0.5">Patokan eceran pasar: Rp {{ number_format($orderItem->quantity * $orderItem->benchmark_retail_price, 0, ',', '.') }}</p>
                            </div>
                            <p class="text-base sm:text-lg font-extrabold text-brand-accent">
                                Hemat Rp {{ number_format($orderItem->savings_amount, 0, ',', '.') }}
                            </p>
                        </div>

                        <!-- Delivery Address Details -->
                        <div class="pt-4 border-t border-black/[0.06] text-xs space-y-1">
                            <p class="font-bold text-brand-dark">Alamat Penerima / Pengiriman:</p>
                            <p class="text-brand-slate">{{ $orderItem->delivery_address }}, {{ $orderItem->delivery_district }}</p>
                        </div>

                        <!-- Order Summary Total -->
                        <div class="pt-4 border-t border-black/[0.06] flex justify-between items-center">
                            <div>
                                <p class="text-xs text-brand-slate">Total Tagihan Yang Harus Dibayar</p>
                                <p class="text-xs text-brand-slate font-mono">Ref: {{ $payment->payment_reference }}</p>
                            </div>
                            <p class="text-2xl font-extrabold text-brand-primary">Rp {{ number_format($orderItem->subtotal, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Interactive Payment Gateway Box -->
            <div class="lg:col-span-5 space-y-6">
                @if ($orderItem->payment_status->value !== 'PAID')
                    <!-- Unpaid Gateway Box -->
                    <div class="card-shell">
                        <div class="card-core p-6 sm:p-7 space-y-6">
                            <!-- Countdown Banner -->
                            <div class="p-3.5 bg-brand-ochre-soft rounded-xl border border-brand-accent/20 flex items-center justify-between text-xs text-brand-accent font-bold">
                                <span>Batas Waktu Pembayaran:</span>
                                <span class="font-mono text-sm" x-text="`${String(minutesLeft).padStart(2, '0')}:${String(secondsLeft).padStart(2, '0')}`"></span>
                            </div>

                            <!-- Payment Method Selector -->
                            <div class="space-y-2">
                                <label class="block text-xs font-bold text-brand-dark uppercase tracking-wider">Metode Pembayaran</label>
                                <div class="grid grid-cols-3 gap-2">
                                    <button type="button" @click="selectedMethod = 'qris'"
                                        :class="selectedMethod === 'qris' ? 'border-brand-primary bg-brand-mint/60 text-brand-primary font-bold ring-1 ring-brand-primary/20' : 'border-black/[0.08] bg-white text-brand-slate'"
                                        class="border rounded-xl p-2.5 text-center text-xs transition-all cursor-pointer">
                                        QRIS Instan
                                    </button>
                                    <button type="button" @click="selectedMethod = 'bca_va'"
                                        :class="selectedMethod === 'bca_va' ? 'border-brand-primary bg-brand-mint/60 text-brand-primary font-bold ring-1 ring-brand-primary/20' : 'border-black/[0.08] bg-white text-brand-slate'"
                                        class="border rounded-xl p-2.5 text-center text-xs transition-all cursor-pointer">
                                        BCA VA
                                    </button>
                                    <button type="button" @click="selectedMethod = 'bni_va'"
                                        :class="selectedMethod === 'bni_va' ? 'border-brand-primary bg-brand-mint/60 text-brand-primary font-bold ring-1 ring-brand-primary/20' : 'border-black/[0.08] bg-white text-brand-slate'"
                                        class="border rounded-xl p-2.5 text-center text-xs transition-all cursor-pointer">
                                        BNI / Mandiri
                                    </button>
                                </div>
                            </div>

                            <!-- QRIS Interactive View -->
                            <div x-show="selectedMethod === 'qris'" class="space-y-4 text-center">
                                <div class="p-4 bg-white border border-black/[0.08] rounded-2xl inline-block mx-auto shadow-sm">
                                    <!-- Dynamic QR Code SVG Rendering -->
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode('QRIS:KULAAN-MIDTRANS-'. $payment->payment_reference .'-RP'. (int)$orderItem->subtotal) }}" 
                                         alt="QRIS Code Kula'an" class="w-44 h-44 mx-auto rounded-lg">
                                    <p class="text-[10px] font-mono text-brand-slate mt-2 font-bold">NMID: ID1020038921820 &bull; KULAAN</p>
                                </div>
                                <div class="text-left text-xs text-brand-slate space-y-1 bg-brand-cream/60 p-3.5 rounded-xl border border-black/[0.03]">
                                    <p class="font-bold text-brand-dark mb-1">Cara Pembayaran QRIS:</p>
                                    <p>1. Buka aplikasi e-Wallet (GoPay, OVO, Dana, ShopeePay) atau Mobile Banking (BCA, Mandiri, BRI).</p>
                                    <p>2. Scan kode QR di atas dan periksa nominal: <strong>Rp {{ number_format($orderItem->subtotal, 0, ',', '.') }}</strong>.</p>
                                    <p>3. Masukkan PIN transaksi Anda dan konfirmasi pembayaran.</p>
                                </div>
                            </div>

                            <!-- BCA Virtual Account View -->
                            <div x-show="selectedMethod === 'bca_va'" x-cloak class="space-y-4">
                                <div class="p-4 bg-brand-cream/60 rounded-xl border border-black/[0.06] space-y-2">
                                    <p class="text-[11px] text-brand-slate font-medium">Nomor BCA Virtual Account:</p>
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="font-mono text-lg font-extrabold text-brand-dark tracking-wider">{{ $bcaVaNumber }}</span>
                                        <button type="button" @click="copyToClipboard('{{ $bcaVaNumber }}', 'BCA')" 
                                            class="btn-ghost text-xs py-1 px-3 bg-white font-bold text-brand-primary border-brand-primary/20 cursor-pointer">
                                            Salin
                                        </button>
                                    </div>
                                    <p class="text-[10px] text-brand-primary font-bold" x-show="copiedText === 'BCA'">Nomor BCA VA Tersalin!</p>
                                </div>
                                <div class="text-xs text-brand-slate space-y-1 bg-brand-cream/60 p-3.5 rounded-xl border border-black/[0.03]">
                                    <p class="font-bold text-brand-dark mb-1">Petunjuk Transfer Virtual Account:</p>
                                    <p>1. Buka m-BCA &rarr; Transfer &rarr; BCA Virtual Account.</p>
                                    <p>2. Masukkan nomor VA di atas & pastikan nama tertera <strong>Kula'an &bull; {{ $orderItem->umkm->business_name }}</strong>.</p>
                                    <p>3. Masukkan PIN dan konfirmasi transfer.</p>
                                </div>
                            </div>

                            <!-- BNI / Mandiri VA View -->
                            <div x-show="selectedMethod === 'bni_va'" x-cloak class="space-y-4">
                                <div class="p-4 bg-brand-cream/60 rounded-xl border border-black/[0.06] space-y-2">
                                    <p class="text-[11px] text-brand-slate font-medium">Nomor BNI / Mandiri VA:</p>
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="font-mono text-lg font-extrabold text-brand-dark tracking-wider">{{ $bniVaNumber }}</span>
                                        <button type="button" @click="copyToClipboard('{{ $bniVaNumber }}', 'BNI')" 
                                            class="btn-ghost text-xs py-1 px-3 bg-white font-bold text-brand-primary border-brand-primary/20 cursor-pointer">
                                            Salin
                                        </button>
                                    </div>
                                    <p class="text-[10px] text-brand-primary font-bold" x-show="copiedText === 'BNI'">Nomor VA Tersalin!</p>
                                </div>
                            </div>

                            <!-- Verification Actions -->
                            <div class="space-y-3 pt-4 border-t border-black/[0.06]">
                                <button type="button" @click="checkPaymentStatus(true)" :disabled="isCheckingStatus"
                                    class="btn-ghost w-full justify-center py-3 text-xs font-bold text-brand-dark border-black/[0.12] hover:bg-black/[0.02] cursor-pointer">
                                    <span x-show="!isCheckingStatus">Cek Status Pembayaran (Sinkronisasi Gateway) &olarr;</span>
                                    <span x-show="isCheckingStatus" x-cloak>Menghubungi Server Gateway...</span>
                                </button>

                                <form method="POST" action="{{ route('payment.simulate', $orderItem) }}">
                                    @csrf
                                    <input type="hidden" name="payment_method" :value="selectedMethod">
                                    <button type="submit" class="btn-primary w-full justify-center py-3 text-xs font-bold shadow-md shadow-brand-primary/25 cursor-pointer">
                                        Konfirmasi Dana Masuk (Simulasi Bank Settlement) &check;
                                    </button>
                                </form>

                                <p class="text-[10px] text-center text-brand-slate leading-relaxed">
                                    Setelah pembayaran masuk ke escrow gateway, pesanan otomatis dikonfirmasi dan notifikasi diteruskan ke distributor untuk disiapkan armada pengiriman.
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Receipt Card for Paid Invoices -->
                    <div class="card-shell">
                        <div class="card-core p-6 sm:p-7 space-y-6 text-center">
                            <div class="w-16 h-16 rounded-full bg-brand-mint text-brand-emerald flex items-center justify-center text-3xl font-extrabold mx-auto shadow-sm">
                                &check;
                            </div>

                            <div class="space-y-1">
                                <h3 class="text-xl font-extrabold text-brand-dark tracking-tight">Pembayaran Lunas</h3>
                                <p class="text-xs text-brand-slate">Dana telah terverifikasi aman di sistem escrow gateway Kula'an.</p>
                            </div>

                            <div class="p-4 bg-brand-cream/60 rounded-xl text-xs space-y-2 text-left border border-black/[0.04]">
                                <div class="flex justify-between">
                                    <span class="text-brand-slate">Metode Bayar:</span>
                                    <span class="font-bold text-brand-dark uppercase">{{ $payment->payment_method ?? 'QRIS' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-brand-slate">Waktu Pelunasan:</span>
                                    <span class="font-medium text-brand-dark">{{ $payment->paid_at?->format('d M Y, H:i:s') ?? now()->format('d M Y, H:i:s') }} WITA</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-brand-slate">Status Pesanan:</span>
                                    <span class="font-bold text-brand-emerald">{{ $orderItem->order->status->label() }}</span>
                                </div>
                            </div>

                            @php $shipment = $orderItem->order->shipments->first(); @endphp
                            @if ($shipment)
                                <a href="{{ route('umkm.tracking.show', $shipment) }}" class="btn-primary w-full justify-center py-3 text-xs font-bold shadow-md shadow-brand-primary/25">
                                    Pantau Posisi Kurir di Peta Live GPS &rarr;
                                </a>
                            @else
                                <a href="{{ route('umkm.orders.index') }}" class="btn-ghost w-full justify-center py-3 text-xs font-bold text-brand-primary">
                                    Lihat Riwayat Pesanan
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
