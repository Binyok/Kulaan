<x-layouts.admin title="Arbitrase Komplain Pesanan">
    <div class="p-6 max-w-7xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-extrabold text-ink">Arbitrase Komplain &amp; Sengketa Escrow</h1>
            <p class="text-sm text-muted mt-1">Tinjau laporan ketidaksesuaian barang dari UMKM dan putuskan pelepasan dana escrow atau pengembalian (refund).</p>
        </div>

        @if (session('success'))
            <div class="mb-4 bg-success/15 border border-success/30 text-success font-semibold px-4 py-3 rounded-2xl text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="space-y-6">
            @forelse ($disputes as $d)
                <div class="bg-white rounded-3xl border border-line p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-4 flex-wrap pb-4 border-b border-line">
                        <div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $d->status === 'OPEN' ? 'bg-danger text-white' : 'bg-success/10 text-success' }}">
                                {{ $d->status }}
                            </span>
                            <h2 class="text-lg font-bold text-ink mt-2">Order {{ $d->orderItem->order->order_number }} &bull; {{ $d->orderItem->product->name }}</h2>
                            <p class="text-xs text-muted mt-0.5">UMKM: {{ $d->umkm->business_name }} &bull; Distributor: {{ $d->orderItem->order->distributor->company_name }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-muted">Nominal Escrow Tertahan</p>
                            <p class="text-xl font-extrabold text-forest tabular-nums">Rp {{ number_format($d->orderItem->escrow->amount ?? $d->orderItem->subtotal, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6 my-4">
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-muted mb-1">Keterangan Komplain UMKM</h3>
                            <div class="bg-cream/60 rounded-2xl p-4 border border-line">
                                <p class="text-xs font-bold text-danger">Alasan: {{ $d->reason }}</p>
                                <p class="text-xs text-ink mt-2 leading-relaxed">{{ $d->description }}</p>
                                @if ($d->evidence_path)
                                    <div class="mt-3">
                                        <p class="text-[11px] font-bold text-muted mb-1">Bukti Foto UMKM:</p>
                                        <img src="{{ asset('storage/'.$d->evidence_path) }}" alt="Bukti UMKM" class="h-32 rounded-xl border border-line object-cover">
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-muted mb-1">Bukti Pengiriman Kurir</h3>
                            <div class="bg-cream/60 rounded-2xl p-4 border border-line">
                                @php $shipment = $d->orderItem->order->shipments->first(); @endphp
                                @if ($shipment && $shipment->proof_image_path)
                                    <p class="text-xs text-muted mb-1">Kurir: {{ $shipment->courier?->user?->name ?? 'Kurir' }} ({{ optional($shipment->delivered_at)->format('d M Y, H:i') }})</p>
                                    <img src="{{ asset('storage/'.$shipment->proof_image_path) }}" alt="Bukti Kurir" class="h-32 rounded-xl border border-line object-cover">
                                @else
                                    <p class="text-xs text-muted">Tidak ada foto bukti kurir terlampir.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($d->status === 'OPEN')
                        <div class="pt-4 border-t border-line flex flex-wrap gap-3 items-center justify-end" x-data="{ openPartial: false }">
                            <form method="POST" action="{{ route('admin.disputes.resolve', $d) }}" onsubmit="return confirm('Tolak komplain dan teruskan dana penuh ke distributor?')">
                                @csrf
                                <input type="hidden" name="action" value="REJECT_RELEASE">
                                <input type="hidden" name="resolution_note" value="Komplain ditolak oleh admin setelah verifikasi bukti kurir. Dana dicairkan ke distributor.">
                                <button type="submit" class="px-4 py-2 bg-forest text-white rounded-xl text-xs font-bold cursor-pointer">
                                    Tolak &amp; Cairkan ke Distributor
                                </button>
                            </form>

                            <form method="POST" action="{{ route('admin.disputes.resolve', $d) }}" onsubmit="return confirm('Setujui refund penuh untuk UMKM?')">
                                @csrf
                                <input type="hidden" name="action" value="REFUND_FULL">
                                <input type="hidden" name="resolution_note" value="Komplain diterima. Dana direfund penuh ke UMKM via payment gateway.">
                                <button type="submit" class="px-4 py-2 bg-danger text-white rounded-xl text-xs font-bold cursor-pointer">
                                    Refund Penuh ke UMKM
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="pt-3 border-t border-line text-xs text-muted">
                            <p><strong>Catatan Resolusi:</strong> {{ $d->resolution_note }} &bull; Diputuskan pada {{ optional($d->resolved_at)->format('d M Y H:i') }}</p>
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-3xl border border-line p-12 text-center text-muted">
                    Tidak ada komplain / sengketa aktif saat ini.
                </div>
            @endforelse
        </div>
    </div>
</x-layouts.admin>
