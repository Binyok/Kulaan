<x-layouts.app>
    <x-slot name="title">Pembayaran Invoice {{ $orderItem->order->order_number }}</x-slot>

    @php
        $isPaid = $orderItem->payment_status->value === 'PAID';
        $productName = $orderItem->product->name;
        $productInitial = strtoupper(substr($productName, 0, 1));
        $quantityLabel = number_format((float) $orderItem->quantity, 0, ',', '.').' '.$orderItem->product->unit;
        $distributorName = $orderItem->order->distributor->company_name;
        $subtotal = $orderItem->subtotal;
        $savings = $orderItem->savings_amount;
        $shipment = $orderItem->order->shipments->first();
    @endphp

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

        {{-- MOBILE --}}
        <div class="lg:hidden pb-24">
            <div class="px-4 pt-6">
                <div class="flex items-center gap-3">
                    <a href="{{ route('umkm.orders.index') }}" aria-label="Kembali" class="w-10 h-10 rounded-2xl bg-white border border-line flex items-center justify-center text-ink shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                    </a>
                    <div class="flex-1 min-w-0">
                        <h1 class="text-lg font-extrabold tracking-tight">Invoice Pembayaran</h1>
                        <p class="text-xs text-muted font-semibold">
                            <span class="font-mono">{{ $orderItem->order->order_number }}</span> &middot; jatuh tempo <span class="font-mono tabular-nums" x-text="`${String(minutesLeft).padStart(2, '0')}:${String(secondsLeft).padStart(2, '0')}`"></span>
                        </p>
                    </div>
                </div>
            </div>

            @if (! $isPaid)
                <div class="px-4 mt-4">
                    <div class="bg-white border border-line rounded-3xl p-4 shadow-sm">
                        <div class="flex gap-3">
                            <div class="w-14 h-14 rounded-2xl bg-forest/10 flex items-center justify-center text-forest font-extrabold text-xl shrink-0">{{ $productInitial }}</div>
                            <div class="flex-1 min-w-0">
                                <p class="font-extrabold text-sm">{{ $productName }}</p>
                                <p class="text-xs text-muted mt-0.5">{{ $quantityLabel }} &middot; {{ $distributorName }}</p>
                                <p class="text-[11px] text-muted mt-0.5">Kuota terpenuhi &mdash; matching berhasil</p>
                            </div>
                        </div>
                        <div class="border-t border-dashed border-line mt-3 pt-3 space-y-1.5 text-xs">
                            <div class="flex justify-between"><span class="text-muted">Subtotal <span class="text-[10px]">(estimasi)</span></span><span class="font-bold tabular-nums">Rp {{ number_format($subtotal, 0, ',', '.') }}</span></div>
                            @if ($savings > 0)
                                <div class="flex justify-between text-forest"><span class="font-semibold">Hemat Kolektif</span><span class="font-bold tabular-nums">- Rp {{ number_format($savings, 0, ',', '.') }}</span></div>
                            @endif
                        </div>
                        <div class="border-t border-line mt-3 pt-3">
                            <p class="text-[11px] font-semibold text-muted">Total Tagihan</p>
                            <p class="text-2xl font-extrabold text-forest tracking-tight mt-0.5 tabular-nums">Rp {{ number_format($subtotal, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>

                <div class="px-4 mt-5">
                    <h2 class="text-sm font-extrabold">Pilih Metode Pembayaran</h2>

                    <div class="mt-3 bg-white border-2 rounded-3xl overflow-hidden shadow-sm transition-colors" :class="selectedMethod === 'qris' ? 'border-forest' : 'border-line'">
                        <button type="button" @click="selectedMethod = 'qris'" class="w-full flex items-center gap-3 p-4 cursor-pointer">
                            <span class="w-10 h-10 rounded-2xl bg-danger/10 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6 text-danger" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5Zm9.75 0c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM3.75 13.5c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5Zm6.75 1.5h1.5m4.5 0h1.5m-4.5 3h1.5m4.5 0h1.5"/></svg>
                            </span>
                            <span class="flex-1 text-left">
                                <span class="block font-extrabold text-sm">QRIS</span>
                                <span class="block text-[11px] text-muted">Scan dengan e-wallet / m-banking apa pun</span>
                            </span>
                            <span x-show="selectedMethod === 'qris'" class="w-5 h-5 rounded-full bg-forest flex items-center justify-center shrink-0">
                                <svg class="w-3 h-3 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m4.5 12.75 6 6 9-13.5"/></svg>
                            </span>
                            <svg x-show="selectedMethod !== 'qris'" x-cloak class="w-5 h-5 text-muted shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                        </button>
                        <div x-show="selectedMethod === 'qris'" class="px-4 pb-4">
                            <div class="bg-cream border border-line rounded-2xl p-4 flex flex-col items-center">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode('QRIS:KULAAN-MIDTRANS-'. $payment->payment_reference .'-RP'. (int)$orderItem->subtotal) }}"
                                     alt="QRIS Code Kulaan" loading="lazy" class="w-40 h-40 rounded-lg">
                                <p class="text-[11px] font-bold text-muted mt-2">NMID: ID1020038921820 &bull; KULAAN</p>
                            </div>
                            <ol class="mt-3 space-y-1.5 text-xs text-muted list-decimal list-inside leading-relaxed">
                                <li>Buka e-wallet atau m-banking apa pun</li>
                                <li>Pilih <span class="font-bold text-ink">Scan QR</span>, arahkan ke kode di atas</li>
                                <li>Periksa nominal <span class="font-bold text-ink tabular-nums">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>, masukkan PIN &amp; konfirmasi</li>
                            </ol>
                        </div>
                    </div>

                    <div class="mt-2.5 space-y-2.5">
                        <div class="bg-white border rounded-3xl overflow-hidden transition-colors" :class="selectedMethod === 'bca_va' ? 'border-forest' : 'border-line'">
                            <button type="button" @click="selectedMethod = 'bca_va'" class="w-full p-4 flex items-center gap-3 cursor-pointer">
                                <span class="w-10 h-10 rounded-2xl bg-forest/10 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-forest" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/></svg>
                                </span>
                                <span class="flex-1 text-left">
                                    <span class="block font-extrabold text-sm">BCA Virtual Account</span>
                                    <span class="block text-[11px] text-muted">Cek otomatis setelah transfer</span>
                                </span>
                                <svg class="w-5 h-5 text-muted shrink-0 transition-transform" :class="selectedMethod === 'bca_va' ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                            </button>
                            <div x-show="selectedMethod === 'bca_va'" x-cloak class="px-4 pb-4">
                                <div class="flex items-center gap-3 bg-cream border border-line rounded-2xl px-4 py-3">
                                    <span class="flex-1 font-extrabold tracking-wider text-base tabular-nums">{{ $bcaVaNumber }}</span>
                                    <button type="button" @click="copyToClipboard('{{ $bcaVaNumber }}', 'BCA')" class="text-xs font-bold text-forest bg-forest/10 px-4 py-2 rounded-xl cursor-pointer">Salin</button>
                                </div>
                                <p class="text-[11px] text-forest font-bold mt-2" x-show="copiedText === 'BCA'" x-cloak>Nomor BCA VA berhasil disalin ke clipboard!</p>
                                <ol class="mt-3 space-y-1.5 text-xs text-muted list-decimal list-inside leading-relaxed">
                                    <li>Masuk ke m-BCA &rarr; <span class="font-bold text-ink">m-Transfer</span> &rarr; <span class="font-bold text-ink">BCA Virtual Account</span></li>
                                    <li>Masukkan nomor VA di atas, pastikan nama penerima <span class="font-bold text-ink">Kulaan &bull; {{ $distributorName }}</span></li>
                                    <li>Masukkan PIN transaksi &amp; simpan bukti bayar</li>
                                </ol>
                            </div>
                        </div>

                        <div class="bg-white border rounded-3xl overflow-hidden transition-colors" :class="selectedMethod === 'bni_va' ? 'border-forest' : 'border-line'">
                            <button type="button" @click="selectedMethod = 'bni_va'" class="w-full p-4 flex items-center gap-3 cursor-pointer">
                                <span class="w-10 h-10 rounded-2xl bg-forest/10 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-forest" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/></svg>
                                </span>
                                <span class="flex-1 text-left">
                                    <span class="block font-extrabold text-sm">BNI Virtual Account</span>
                                    <span class="block text-[11px] text-muted">Cek otomatis setelah transfer</span>
                                </span>
                                <svg class="w-5 h-5 text-muted shrink-0 transition-transform" :class="selectedMethod === 'bni_va' ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                            </button>
                            <div x-show="selectedMethod === 'bni_va'" x-cloak class="px-4 pb-4">
                                <div class="flex items-center gap-3 bg-cream border border-line rounded-2xl px-4 py-3">
                                    <span class="flex-1 font-extrabold tracking-wider text-base tabular-nums">{{ $bniVaNumber }}</span>
                                    <button type="button" @click="copyToClipboard('{{ $bniVaNumber }}', 'BNI')" class="text-xs font-bold text-forest bg-forest/10 px-4 py-2 rounded-xl cursor-pointer">Salin</button>
                                </div>
                                <p class="text-[11px] text-forest font-bold mt-2" x-show="copiedText === 'BNI'" x-cloak>Nomor BNI VA berhasil disalin ke clipboard!</p>
                            </div>
                        </div>

                        <div class="bg-white border rounded-3xl overflow-hidden transition-colors" :class="selectedMethod === 'mandiri_va' ? 'border-forest' : 'border-line'">
                            <button type="button" @click="selectedMethod = 'mandiri_va'" class="w-full p-4 flex items-center gap-3 cursor-pointer">
                                <span class="w-10 h-10 rounded-2xl bg-forest/10 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-forest" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/></svg>
                                </span>
                                <span class="flex-1 text-left">
                                    <span class="block font-extrabold text-sm">Mandiri Virtual Account</span>
                                    <span class="block text-[11px] text-muted">Cek otomatis setelah transfer</span>
                                </span>
                                <svg class="w-5 h-5 text-muted shrink-0 transition-transform" :class="selectedMethod === 'mandiri_va' ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                            </button>
                            <div x-show="selectedMethod === 'mandiri_va'" x-cloak class="px-4 pb-4">
                                <div class="flex items-center gap-3 bg-cream border border-line rounded-2xl px-4 py-3">
                                    <span class="flex-1 font-extrabold tracking-wider text-base tabular-nums">{{ $mandiriVaNumber }}</span>
                                    <button type="button" @click="copyToClipboard('{{ $mandiriVaNumber }}', 'Mandiri')" class="text-xs font-bold text-forest bg-forest/10 px-4 py-2 rounded-xl cursor-pointer">Salin</button>
                                </div>
                                <p class="text-[11px] text-forest font-bold mt-2" x-show="copiedText === 'Mandiri'" x-cloak>Nomor Mandiri VA berhasil disalin ke clipboard!</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-4 mt-5 pb-6">
                    <button type="button" @click="checkPaymentStatus(true)" :disabled="isCheckingStatus" class="w-full bg-forest text-white text-sm font-bold py-4 rounded-2xl shadow-sm disabled:opacity-70 disabled:cursor-wait cursor-pointer">
                        <span x-show="!isCheckingStatus">Cek Status Pembayaran</span>
                        <span x-show="isCheckingStatus" x-cloak>Memeriksa server...</span>
                    </button>
                    <form method="POST" action="{{ route('payment.simulate', $orderItem) }}" data-loading class="mt-2.5">
                        @csrf
                        <input type="hidden" name="payment_method" :value="selectedMethod">
                        <button type="submit" class="w-full bg-white border-2 border-forest text-forest text-sm font-bold py-4 rounded-2xl cursor-pointer">Konfirmasi Transfer</button>
                    </form>
                    <p class="flex items-center justify-center gap-1.5 text-[11px] text-muted mt-4">
                        <svg class="w-4 h-4 text-success" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 12.75 11.25 15 15 9.75m-3-7.04A11.96 11.96 0 0 1 3.6 6 12 12 0 0 0 3 9.75c0 5.59 3.82 10.29 9 11.62 5.18-1.33 9-6.03 9-11.62 0-1.31-.21-2.57-.6-3.75h-.15c-3.2 0-6.1-1.25-8.25-3.29Z"/></svg>
                        Pembayaran aman didukung oleh Midtrans
                    </p>
                </div>
            @else
                <div class="px-4 mt-6">
                    <div class="bg-white border border-line rounded-3xl p-8 text-center shadow-sm">
                        <span class="w-16 h-16 rounded-full bg-forest/10 text-forest inline-flex items-center justify-center mx-auto">
                            <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m4.5 12.75 6 6 9-13.5"/></svg>
                        </span>
                        <h2 class="text-xl font-extrabold text-ink tracking-tight mt-4">Pembayaran Lunas</h2>
                        <p class="text-xs text-muted mt-1">Invoice <span class="font-mono font-bold">{{ $orderItem->order->order_number }}</span> telah terbayar.</p>
                        <dl class="mt-6 p-4 bg-cream rounded-2xl text-xs space-y-2 text-left border border-line">
                            <div class="flex justify-between">
                                <dt class="text-muted">Metode Bayar</dt>
                                <dd class="font-bold text-ink uppercase">{{ $payment->payment_method ?? 'QRIS' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted">Waktu Bayar</dt>
                                <dd class="font-medium text-ink">{{ $payment->paid_at?->format('d M Y, H:i') ?? now()->format('d M Y, H:i') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted">Total Lunas</dt>
                                <dd class="font-extrabold text-forest tabular-nums">Rp {{ number_format($subtotal, 0, ',', '.') }}</dd>
                            </div>
                        </dl>
                        @if ($shipment)
                            <a href="{{ route('umkm.tracking.show', $shipment) }}" class="w-full bg-forest text-white text-sm font-bold py-3.5 rounded-2xl shadow-sm mt-6 inline-flex items-center justify-center">Pantau Kurir di Peta Live &rarr;</a>
                        @else
                            <a href="{{ route('umkm.orders.index') }}" class="w-full bg-white border-2 border-forest text-forest text-sm font-bold py-3.5 rounded-2xl mt-6 inline-flex items-center justify-center">Kembali ke Riwayat Pesanan</a>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- DESKTOP --}}
        <div class="hidden lg:flex min-h-screen">
            <div class="flex-1 min-w-0">
                <header class="bg-white border-b border-line sticky top-0 z-20">
                    <div class="max-w-7xl mx-auto px-6 h-[72px] flex items-center gap-6">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 shrink-0">
                            <span class="w-10 h-10 rounded-2xl bg-forest flex items-center justify-center text-white">
                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.25 7.5l-.63 10.63a2.25 2.25 0 0 1-2.25 2.12H6.63a2.25 2.25 0 0 1-2.25-2.12L3.75 7.5M10 11.25h4M3.38 7.5h17.25c.62 0 1.12-.5 1.12-1.12v-1.5C21.75 4.26 21.25 3.75 20.63 3.75H3.38c-.62 0-1.13.5-1.13 1.12v1.5c0 .63.5 1.13 1.12 1.13Z"/></svg>
                            </span>
                            <span class="text-xl font-extrabold tracking-tight">Kulaan</span>
                        </a>
                        <form method="GET" action="{{ route('umkm.catalog.index') }}" class="flex-1 max-w-xl">
                            <div class="flex items-center gap-2 bg-cream border border-line rounded-2xl pl-4 pr-2 py-2">
                                <svg class="w-5 h-5 text-muted shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21 21-5.2-5.2m0 0A7.5 7.5 0 1 0 5.2 5.2a7.5 7.5 0 0 0 10.6 10.6Z"/></svg>
                                <input name="q" class="flex-1 bg-transparent text-sm outline-none placeholder:text-muted/70" placeholder="Cari beras, minyak goreng, gula...">
                                <button type="submit" class="bg-forest text-white text-sm font-bold px-5 py-2 rounded-xl cursor-pointer">Cari</button>
                            </div>
                        </form>
                        <nav class="hidden lg:flex items-center gap-6 text-sm font-semibold text-muted shrink-0">
                            <a href="{{ route('umkm.catalog.index') }}" class="hover:text-ink">Katalog</a>
                            <a href="{{ route('umkm.orders.index') }}" class="text-forest font-bold">Pesanan</a>
                            <a href="{{ route('umkm.tracking.index') }}" class="hover:text-ink">Tracking</a>
                        </nav>
                        <div class="flex items-center gap-3 shrink-0 ml-auto">
                            <span class="flex items-center gap-1.5 text-sm font-semibold text-muted">
                                <svg class="w-5 h-5 text-forest" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path d="M19.5 10.5c0 7.14-7.5 11.25-7.5 11.25S4.5 17.64 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                                {{ auth()->user()->umkm?->city ?? 'Jember' }}
                            </span>
                            <a href="{{ route('umkm.cart.index') }}" aria-label="Keranjang" class="w-11 h-11 rounded-2xl bg-white border border-line flex items-center justify-center relative">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2.25 3h1.39c.51 0 .96.34 1.09.84l.38 1.44M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.22c1.12-2.3 2.1-4.68 2.92-7.14a60.11 60.11 0 0 0-16.54-1.84M7.5 14.25 5.11 5.27M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                            </a>
                            <span class="w-11 h-11 rounded-2xl bg-forest text-white flex items-center justify-center font-extrabold text-sm">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                        </div>
                    </div>
                </header>

                <main class="max-w-7xl mx-auto px-6 pb-16">
                    @if (! $isPaid)
                        <div class="mt-8 flex items-center justify-between">
                            <div>
                                <h1 class="text-3xl font-extrabold tracking-tight">Invoice Pembayaran</h1>
                                <p class="text-sm text-muted mt-1">Selesaikan pembayaran agar pesananmu diproses distributor.</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold bg-white border border-line px-4 py-2 rounded-2xl font-mono">{{ $orderItem->order->order_number }}</span>
                                <span class="text-xs font-bold bg-brass-soft text-brass px-3 py-2 rounded-2xl">Jatuh tempo <span class="font-mono tabular-nums" x-text="`${String(minutesLeft).padStart(2, '0')}:${String(secondsLeft).padStart(2, '0')}`"></span></span>
                            </div>
                        </div>

                        <div class="grid lg:grid-cols-3 gap-6 mt-6">
                            <div class="lg:col-span-2">
                                <h2 class="text-base font-extrabold">Pilih Metode Pembayaran</h2>

                                <div class="flex gap-2 mt-4">
                                    <button type="button" @click="selectedMethod = 'qris'" :class="selectedMethod === 'qris' ? 'bg-forest text-white shadow-sm' : 'bg-white border border-line text-muted'" class="flex-1 text-sm font-bold py-3.5 rounded-2xl cursor-pointer">QRIS</button>
                                    <button type="button" @click="selectedMethod = 'bca_va'" :class="selectedMethod === 'bca_va' ? 'bg-forest text-white shadow-sm' : 'bg-white border border-line text-muted'" class="flex-1 text-sm font-bold py-3.5 rounded-2xl cursor-pointer">BCA VA</button>
                                    <button type="button" @click="selectedMethod = 'bni_va'" :class="selectedMethod === 'bni_va' ? 'bg-forest text-white shadow-sm' : 'bg-white border border-line text-muted'" class="flex-1 text-sm font-bold py-3.5 rounded-2xl cursor-pointer">BNI VA</button>
                                    <button type="button" @click="selectedMethod = 'mandiri_va'" :class="selectedMethod === 'mandiri_va' ? 'bg-forest text-white shadow-sm' : 'bg-white border border-line text-muted'" class="flex-1 text-sm font-bold py-3.5 rounded-2xl cursor-pointer">Mandiri VA</button>
                                </div>

                                <div x-show="selectedMethod === 'qris'" class="mt-4 bg-white border border-line rounded-3xl p-6 shadow-sm">
                                    <div class="flex flex-col sm:flex-row gap-6 items-center">
                                        <div class="bg-cream border border-line rounded-2xl p-4 shrink-0">
                                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode('QRIS:KULAAN-MIDTRANS-'. $payment->payment_reference .'-RP'. (int)$orderItem->subtotal) }}"
                                                 alt="QRIS Code Kulaan" loading="lazy" class="w-44 h-44 rounded-lg">
                                            <p class="text-[11px] font-bold text-muted text-center mt-2">NMID: ID1020038921820 &bull; KULAAN</p>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-extrabold">Cara bayar dengan QRIS</p>
                                            <ol class="mt-3 space-y-2.5 text-sm text-muted list-decimal list-inside leading-relaxed">
                                                <li>Buka e-wallet atau m-banking apa pun di HP-mu</li>
                                                <li>Pilih <span class="font-bold text-ink">Scan QR</span>, arahkan ke kode di samping</li>
                                                <li>Periksa nominal <span class="font-bold text-ink tabular-nums">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>, masukkan PIN &amp; konfirmasi</li>
                                            </ol>
                                            <p class="text-xs text-muted mt-4 bg-cream border border-line rounded-2xl p-3">Status pembayaran terdeteksi otomatis dalam &plusmn;1 menit setelah kamu membayar.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 space-y-3">
                                    <div class="bg-white border border-line rounded-3xl p-5" x-show="selectedMethod === 'bca_va'" x-cloak>
                                        <div class="flex items-center justify-between">
                                            <p class="font-extrabold text-sm">BCA Virtual Account</p>
                                            <svg class="w-5 h-5 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m4.5 15.75 7.5-7.5 7.5 7.5"/></svg>
                                        </div>
                                        <div class="flex items-center gap-3 mt-3 bg-cream border border-line rounded-2xl px-4 py-3">
                                            <span class="flex-1 font-extrabold tracking-wider text-base tabular-nums">{{ $bcaVaNumber }}</span>
                                            <button type="button" @click="copyToClipboard('{{ $bcaVaNumber }}', 'BCA')" class="text-xs font-bold text-forest bg-forest/10 px-4 py-2 rounded-xl cursor-pointer">Salin</button>
                                        </div>
                                        <p class="text-xs text-forest font-bold mt-2" x-show="copiedText === 'BCA'" x-cloak>Nomor BCA VA berhasil disalin ke clipboard!</p>
                                        <p class="text-xs text-muted mt-2">Transfer tepat hingga 3 digit terakhir &mdash; cek otomatis.</p>
                                    </div>
                                    <div x-show="selectedMethod !== 'bca_va'" class="bg-white border border-line rounded-3xl">
                                        <button type="button" @click="selectedMethod = 'bca_va'" class="w-full p-5 flex items-center justify-between cursor-pointer">
                                            <span class="font-extrabold text-sm">BCA Virtual Account</span>
                                            <svg class="w-5 h-5 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                        </button>
                                    </div>

                                    <div class="bg-white border border-line rounded-3xl p-5" x-show="selectedMethod === 'bni_va'" x-cloak>
                                        <div class="flex items-center justify-between">
                                            <p class="font-extrabold text-sm">BNI Virtual Account</p>
                                            <svg class="w-5 h-5 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m4.5 15.75 7.5-7.5 7.5 7.5"/></svg>
                                        </div>
                                        <div class="flex items-center gap-3 mt-3 bg-cream border border-line rounded-2xl px-4 py-3">
                                            <span class="flex-1 font-extrabold tracking-wider text-base tabular-nums">{{ $bniVaNumber }}</span>
                                            <button type="button" @click="copyToClipboard('{{ $bniVaNumber }}', 'BNI')" class="text-xs font-bold text-forest bg-forest/10 px-4 py-2 rounded-xl cursor-pointer">Salin</button>
                                        </div>
                                        <p class="text-xs text-forest font-bold mt-2" x-show="copiedText === 'BNI'" x-cloak>Nomor BNI VA berhasil disalin ke clipboard!</p>
                                        <p class="text-xs text-muted mt-2">Transfer tepat hingga 3 digit terakhir &mdash; cek otomatis.</p>
                                    </div>
                                    <div x-show="selectedMethod !== 'bni_va'" class="bg-white border border-line rounded-3xl">
                                        <button type="button" @click="selectedMethod = 'bni_va'" class="w-full p-5 flex items-center justify-between cursor-pointer">
                                            <span class="font-extrabold text-sm">BNI Virtual Account</span>
                                            <svg class="w-5 h-5 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                        </button>
                                    </div>

                                    <div class="bg-white border border-line rounded-3xl p-5" x-show="selectedMethod === 'mandiri_va'" x-cloak>
                                        <div class="flex items-center justify-between">
                                            <p class="font-extrabold text-sm">Mandiri Virtual Account</p>
                                            <svg class="w-5 h-5 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m4.5 15.75 7.5-7.5 7.5 7.5"/></svg>
                                        </div>
                                        <div class="flex items-center gap-3 mt-3 bg-cream border border-line rounded-2xl px-4 py-3">
                                            <span class="flex-1 font-extrabold tracking-wider text-base tabular-nums">{{ $mandiriVaNumber }}</span>
                                            <button type="button" @click="copyToClipboard('{{ $mandiriVaNumber }}', 'Mandiri')" class="text-xs font-bold text-forest bg-forest/10 px-4 py-2 rounded-xl cursor-pointer">Salin</button>
                                        </div>
                                        <p class="text-xs text-forest font-bold mt-2" x-show="copiedText === 'Mandiri'" x-cloak>Nomor Mandiri VA berhasil disalin ke clipboard!</p>
                                        <p class="text-xs text-muted mt-2">Transfer tepat hingga 3 digit terakhir &mdash; cek otomatis.</p>
                                    </div>
                                    <div x-show="selectedMethod !== 'mandiri_va'" class="bg-white border border-line rounded-3xl">
                                        <button type="button" @click="selectedMethod = 'mandiri_va'" class="w-full p-5 flex items-center justify-between cursor-pointer">
                                            <span class="font-extrabold text-sm">Mandiri Virtual Account</span>
                                            <svg class="w-5 h-5 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                        </button>
                                    </div>
                                </div>
                                <p class="text-[11px] text-muted mt-4">Nomor virtual account BNI: {{ $bniVaNumber }} &bull; Mandiri: {{ $mandiriVaNumber }} (ditampilkan penuh setelah tab dipilih).</p>
                            </div>

                            <div>
                                <div class="bg-white border border-line rounded-3xl p-6 shadow-sm lg:sticky lg:top-24">
                                    <h2 class="text-base font-extrabold">Ringkasan Tagihan</h2>
                                    <div class="flex gap-3 mt-4">
                                        <div class="w-14 h-14 rounded-2xl bg-forest/10 flex items-center justify-center text-forest font-extrabold text-xl shrink-0">{{ $productInitial }}</div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-extrabold text-sm">{{ $productName }}</p>
                                            <p class="text-xs text-muted mt-0.5">{{ $quantityLabel }} &middot; {{ $distributorName }}</p>
                                            <p class="text-[11px] text-success font-semibold mt-0.5">Kuota terpenuhi &mdash; matching berhasil</p>
                                        </div>
                                    </div>
                                    <div class="border-t border-dashed border-line mt-4 pt-4 space-y-2 text-sm">
                                        <div class="flex justify-between"><span class="text-muted">Subtotal <span class="text-[11px]">(estimasi)</span></span><span class="font-bold tabular-nums">Rp {{ number_format($subtotal, 0, ',', '.') }}</span></div>
                                        @if ($savings > 0)
                                            <div class="flex justify-between text-forest"><span class="font-semibold">Hemat Kolektif</span><span class="font-bold tabular-nums">- Rp {{ number_format($savings, 0, ',', '.') }}</span></div>
                                        @endif
                                    </div>
                                    <div class="border-t border-line mt-4 pt-4">
                                        <p class="text-xs font-semibold text-muted">Total Tagihan</p>
                                        <p class="text-3xl font-extrabold text-forest tracking-tight mt-1 tabular-nums">Rp {{ number_format($subtotal, 0, ',', '.') }}</p>
                                    </div>
                                    <button type="button" @click="checkPaymentStatus(true)" :disabled="isCheckingStatus" class="w-full bg-forest text-white text-sm font-bold py-4 rounded-2xl shadow-sm mt-5 disabled:opacity-70 disabled:cursor-wait cursor-pointer">
                                        <span x-show="!isCheckingStatus">Cek Status Pembayaran</span>
                                        <span x-show="isCheckingStatus" x-cloak>Memeriksa server...</span>
                                    </button>
                                    <form method="POST" action="{{ route('payment.simulate', $orderItem) }}" data-loading class="mt-2.5">
                                        @csrf
                                        <input type="hidden" name="payment_method" :value="selectedMethod">
                                        <button type="submit" class="w-full bg-white border-2 border-forest text-forest text-sm font-bold py-4 rounded-2xl cursor-pointer">Konfirmasi Transfer</button>
                                    </form>
                                    <p class="flex items-center justify-center gap-1.5 text-[11px] text-muted mt-4">
                                        <svg class="w-4 h-4 text-success" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 12.75 11.25 15 15 9.75m-3-7.04A11.96 11.96 0 0 1 3.6 6 12 12 0 0 0 3 9.75c0 5.59 3.82 10.29 9 11.62 5.18-1.33 9-6.03 9-11.62 0-1.31-.21-2.57-.6-3.75h-.15c-3.2 0-6.1-1.25-8.25-3.29Z"/></svg>
                                        Pembayaran aman didukung oleh Midtrans
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <section class="bg-white border border-line rounded-3xl p-12 text-center max-w-md mx-auto shadow-sm mt-8">
                            <span class="w-16 h-16 rounded-full bg-forest/10 text-forest inline-flex items-center justify-center mx-auto">
                                <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m4.5 12.75 6 6 9-13.5"/></svg>
                            </span>
                            <h2 class="text-2xl font-extrabold text-ink tracking-tight mt-4">Pembayaran Lunas</h2>
                            <p class="text-xs text-muted mt-1">Invoice <span class="font-mono font-bold">{{ $orderItem->order->order_number }}</span> telah terbayar.</p>
                            <dl class="mt-6 p-4 bg-cream rounded-2xl text-xs space-y-2 text-left border border-line">
                                <div class="flex justify-between">
                                    <dt class="text-muted">Metode Bayar</dt>
                                    <dd class="font-bold text-ink uppercase">{{ $payment->payment_method ?? 'QRIS' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-muted">Waktu Bayar</dt>
                                    <dd class="font-medium text-ink">{{ $payment->paid_at?->format('d M Y, H:i') ?? now()->format('d M Y, H:i') }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-muted">Total Lunas</dt>
                                    <dd class="font-extrabold text-forest tabular-nums">Rp {{ number_format($subtotal, 0, ',', '.') }}</dd>
                                </div>
                            </dl>
                            @if ($shipment)
                                <a href="{{ route('umkm.tracking.show', $shipment) }}" class="w-full bg-forest text-white text-sm font-bold py-3.5 rounded-2xl shadow-sm mt-6 inline-flex items-center justify-center">Pantau Kurir di Peta Live &rarr;</a>
                            @else
                                <a href="{{ route('umkm.orders.index') }}" class="w-full bg-white border-2 border-forest text-forest text-sm font-bold py-3.5 rounded-2xl mt-6 inline-flex items-center justify-center">Kembali ke Riwayat Pesanan</a>
                            @endif
                        </section>
                    @endif
                </main>
            </div>
        </div>

        {{-- BOTTOM TAB --}}
        <div class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-white border-t border-line px-2 pt-2 pb-5 flex items-start justify-around">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 w-14 text-muted">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m2.25 12 8.95-8.95a1.13 1.13 0 0 1 1.6 0l8.95 8.95M4.5 9.75V19.5a.75.75 0 0 0 .75.75h13.5a.75.75 0 0 0 .75-.75V9.75"/></svg>
                <span class="text-[10px] font-semibold">Beranda</span>
            </a>
            <a href="{{ route('umkm.catalog.index') }}" class="flex flex-col items-center gap-1 w-14 text-muted">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>
                <span class="text-[10px] font-semibold">Katalog</span>
            </a>
            <a href="{{ route('umkm.cart.index') }}" class="flex flex-col items-center gap-1 w-14 text-muted">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2.25 3h1.39c.51 0 .96.34 1.09.84l.38 1.44M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.22c1.12-2.3 2.1-4.68 2.92-7.14a60.11 60.11 0 0 0-16.54-1.84M7.5 14.25 5.11 5.27M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                <span class="text-[10px] font-semibold">Keranjang</span>
            </a>
            <a href="{{ route('umkm.orders.index') }}" class="flex flex-col items-center gap-1 w-14 text-forest relative">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.25 7.5l-.63 10.63a2.25 2.25 0 0 1-2.25 2.12H6.63a2.25 2.25 0 0 1-2.25-2.12L3.75 7.5M10 11.25h4M3.38 7.5h17.25c.62 0 1.12-.5 1.12-1.12v-1.5C21.75 4.26 21.25 3.75 20.63 3.75H3.38c-.62 0-1.13.5-1.13 1.12v1.5c0 .63.5 1.13 1.12 1.13Z"/></svg>
                <span class="text-[10px] font-bold">Pesanan</span>
            </a>
            <a href="{{ route('profile.complete') }}" class="flex flex-col items-center gap-1 w-14 text-muted">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17.98 18.72a6 6 0 0 0-8.46 0M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm-9.75 8.25a9 9 0 1 1 13.5 0"/></svg>
                <span class="text-[10px] font-semibold">Akun</span>
            </a>
        </div>
    </div>
</x-layouts.app>
