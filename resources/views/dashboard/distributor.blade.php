<x-layouts.app>
    <x-slot name="title">Dashboard Distributor</x-slot>

    <div class="space-y-8 animate-fade-up">
        <div>
            <span class="eyebrow">Distributor Ekosistem</span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-brand-dark tracking-tight mt-1.5">{{ $distributor?->company_name ?? $user->name }}</h1>
            <p class="text-[0.8125rem] text-brand-slate mt-1">Area Operasional: {{ $distributor?->district ?? 'Banjarmasin' }} &bull; Waktu Kirim Rata-rata: {{ $distributor?->delivery_lead_time_days ?? 1 }} Hari</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div class="card-shell">
                <div class="card-core p-6">
                    <p class="metric-label">Katalog Produk Baku</p>
                    <p class="metric-value text-brand-dark mt-2.5">{{ $catalogCount }}</p>
                    <p class="text-[0.6875rem] text-brand-slate mt-2">Bahan baku aktif dijual</p>
                </div>
            </div>

            <div class="card-shell">
                <div class="card-core p-6">
                    <p class="metric-label">Pesanan Perlu Diproses</p>
                    <p class="metric-value {{ $pendingOrdersCount > 0 ? 'text-brand-brick' : 'text-brand-primary' }} mt-2.5">
                        {{ $pendingOrdersCount }}
                    </p>
                    <p class="text-[0.6875rem] text-brand-slate mt-2">Menunggu konfirmasi / kurir</p>
                </div>
            </div>

            <div class="card-shell">
                <div class="card-core p-6 savings-card">
                    <p class="metric-label text-brand-accent">Rating Performa SPK</p>
                    <p class="metric-value text-brand-accent mt-2.5">{{ number_format($distributor?->rating ?? 5.0, 2) }} <span class="text-xs font-normal text-brand-slate">/ 5.0</span></p>
                    <p class="text-[0.6875rem] text-brand-slate mt-2">Skor AHP + TOPSIS</p>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="card-shell">
            <div class="card-core p-6 sm:p-7 space-y-5">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-base font-bold text-brand-dark tracking-tight">Pesanan Kolektif Masuk</h2>
                        <p class="text-[0.75rem] text-brand-slate mt-0.5">Daftar pesanan terbaru dari grup auto-matching UMKM</p>
                    </div>
                    <a href="{{ route('distributor.orders.index') }}" class="btn-ghost text-xs py-1.5 px-4 font-semibold text-brand-primary">
                        Lihat Semua &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto -mx-6 sm:mx-0">
                    <table class="table-premium">
                        <thead>
                            <tr>
                                <th>No. Pesanan</th>
                                <th>Anggota UMKM</th>
                                <th class="text-center">Kuantitas</th>
                                <th class="text-right">Nilai Transaksi</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($orders as $order)
                                <tr>
                                    <td class="font-mono font-bold text-brand-dark">{{ $order->order_number }}</td>
                                    <td class="text-xs text-brand-slate">
                                        {{ $order->items->pluck('umkm.business_name')->join(', ') }}
                                    </td>
                                    <td class="text-center font-bold text-brand-dark">{{ number_format($order->total_quantity, 0) }} unit</td>
                                    <td class="text-right font-extrabold text-brand-primary">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $order->status->badgeClass() }}">
                                            {{ $order->status->label() }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-brand-slate text-[0.8125rem]">Belum ada pesanan kolektif masuk saat ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
