<x-layouts.admin title="Keuangan & Pencairan Escrow">
    <div class="p-6 max-w-7xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-extrabold text-ink">Keuangan &amp; Pencairan Escrow</h1>
            <p class="text-sm text-muted mt-1">Pencairan ke rekening distributor berjalan <strong>otomatis</strong> saat dana escrow dilepas. Halaman ini untuk memantau ringkasan dana dan menangani pencairan yang tertahan, misalnya karena rekening distributor belum lengkap.</p>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-8">
            <div class="bg-white rounded-3xl border border-line p-4">
                <p class="text-[11px] font-bold text-muted uppercase tracking-wider">Dana Ditahan (Escrow)</p>
                <p class="text-xl font-extrabold text-ink mt-1.5 tabular-nums">Rp {{ number_format($summary['held'], 0, ',', '.') }}</p>
                <p class="text-[11px] text-muted mt-1">Menunggu barang diterima UMKM.</p>
            </div>
            <div class="bg-white rounded-3xl border-2 border-brass/40 p-4">
                <p class="text-[11px] font-bold text-brass uppercase tracking-wider">Perlu Perhatian</p>
                <p class="text-xl font-extrabold text-ink mt-1.5 tabular-nums">Rp {{ number_format($summary['queued'], 0, ',', '.') }}</p>
                <p class="text-[11px] text-muted mt-1">Pencairan otomatis tertahan dan perlu ditangani admin.</p>
            </div>
            <div class="bg-white rounded-3xl border border-line p-4">
                <p class="text-[11px] font-bold text-muted uppercase tracking-wider">Sudah Dicairkan</p>
                <p class="text-xl font-extrabold text-forest mt-1.5 tabular-nums">Rp {{ number_format($summary['paid'], 0, ',', '.') }}</p>
                <p class="text-[11px] text-muted mt-1">Tercairkan otomatis ke rekening distributor.</p>
            </div>
            <div class="bg-white rounded-3xl border border-line p-4">
                <p class="text-[11px] font-bold text-muted uppercase tracking-wider">Sudah Direfund</p>
                <p class="text-xl font-extrabold text-ink mt-1.5 tabular-nums">Rp {{ number_format($summary['refunded'], 0, ',', '.') }}</p>
                <p class="text-[11px] text-muted mt-1">Dikembalikan ke UMKM.</p>
            </div>
        </div>

        <h2 class="text-base font-extrabold text-ink mb-3">Pencairan Perlu Perhatian ({{ $queue->total() }})</h2>

        @if ($queue->isEmpty())
            <div class="bg-white rounded-3xl border border-line p-8 text-center mb-8">
                <p class="text-sm font-bold text-ink">Tidak ada pencairan yang tertahan</p>
                <p class="text-xs text-muted mt-1">Semua dana yang sudah dilepas telah dicairkan otomatis ke distributor.</p>
            </div>
        @else
            <div class="space-y-3 mb-4">
                @foreach ($queue as $escrow)
                    <article class="bg-white rounded-3xl border border-line p-4 sm:p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-bold text-ink">{{ $escrow->distributor->company_name }}</p>
                                <p class="text-xs text-muted mt-0.5">
                                    {{ $escrow->order->order_number }} &bull; {{ $escrow->orderItem->product->name }}
                                    @if ($escrow->released_at)
                                        &bull; Dilepas {{ $escrow->released_at->format('d M Y, H:i') }}
                                    @endif
                                </p>
                                <p class="text-xs text-muted mt-1">
                                    Tujuan: <span class="font-semibold text-ink">{{ $escrow->distributor->bank_name ?? '-' }}</span>
                                    &bull; <span class="font-mono">{{ $escrow->distributor->bank_account_number ?? '-' }}</span>
                                    &bull; a.n. {{ $escrow->distributor->bank_account_name ?? '-' }}
                                </p>
                                @if ($escrow->payout_status === 'FAILED_RETRY')
                                    <span class="inline-flex mt-2 px-2 py-0.5 rounded-full text-[10px] font-bold bg-danger/10 text-danger">Gateway gagal, cairkan manual</span>
                                @elseif ($escrow->payout_status === 'PENDING_BANK')
                                    <span class="inline-flex mt-2 px-2 py-0.5 rounded-full text-[10px] font-bold bg-brass/10 text-brass">Rekening distributor belum lengkap</span>
                                @endif
                            </div>
                            <p class="text-lg font-extrabold text-forest tabular-nums">Rp {{ number_format((float) $escrow->amount, 0, ',', '.') }}</p>
                        </div>
                        <form method="POST" action="{{ route('admin.payouts.mark-paid', $escrow) }}" class="mt-4 flex flex-col sm:flex-row gap-2" onsubmit="return confirm('Tandai pencairan ini sebagai sudah ditransfer?')">
                            @csrf
                            <input type="text" name="transfer_reference" required maxlength="100" placeholder="Nomor referensi transfer bank"
                                   class="flex-1 rounded-xl border border-line px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-forest/30">
                            <button type="submit" class="px-4 py-2.5 rounded-xl bg-forest text-white text-xs font-bold hover:bg-forest-dark transition cursor-pointer">Tandai Sudah Ditransfer</button>
                        </form>
                    </article>
                @endforeach
            </div>
            <div class="mb-8">{{ $queue->links() }}</div>
        @endif

        <h2 class="text-base font-extrabold text-ink mb-3">Riwayat Pencairan Terakhir</h2>
        @if ($history->isEmpty())
            <div class="bg-white rounded-3xl border border-line p-6 text-center">
                <p class="text-xs text-muted">Belum ada pencairan yang selesai dicairkan.</p>
            </div>
        @else
            <div class="bg-white rounded-3xl border border-line divide-y divide-line/70 overflow-hidden">
                @foreach ($history as $escrow)
                    <div class="flex flex-wrap items-center justify-between gap-2 px-4 sm:px-5 py-3.5">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-ink">{{ $escrow->distributor->company_name }}</p>
                            <p class="text-[11px] text-muted">{{ $escrow->order->order_number }} &bull; Ref: <span class="font-mono">{{ $escrow->payout_reference }}</span></p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-extrabold text-forest tabular-nums">Rp {{ number_format((float) $escrow->amount, 0, ',', '.') }}</p>
                            <p class="text-[11px] text-muted">{{ optional($escrow->payout_completed_at)->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.admin>
