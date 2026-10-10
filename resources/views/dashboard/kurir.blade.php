<x-layouts.app>
    <x-slot name="title">Dashboard Kurir</x-slot>

    <div class="space-y-8">
        <div data-reveal>
            <span class="eyebrow">Kurir Armada</span>
            <h1 class="font-display font-semibold text-[1.75rem] sm:text-4xl text-brand-dark tracking-tight text-balance mt-2">{{ $user->name }}</h1>
            <p class="text-[0.8125rem] text-brand-slate mt-1">Armada: {{ $courier?->vehicle_type ?? 'Motor' }} &bull; Plat: {{ $courier?->vehicle_number ?? '-' }}</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div class="card-shell">
                <div class="card-core p-6">
                    <p class="metric-label">Tugas Baru Menunggu</p>
                    <p class="metric-value text-brand-dark mt-2.5">{{ $assignedShipmentsCount }}</p>
                    <p class="text-[0.6875rem] text-brand-slate mt-2">Siap untuk dikirim</p>
                </div>
            </div>

            <div class="card-shell">
                <div class="card-core p-6">
                    <p class="metric-label">Status GPS Pengiriman</p>
                    <p class="metric-value {{ $activeDelivery ? 'text-brand-emerald' : 'text-brand-slate' }} mt-2.5 text-xl sm:text-2xl">
                        {{ $activeDelivery ? 'GPS Aktif' : 'Standby' }}
                    </p>
                    <p class="text-[0.6875rem] text-brand-slate mt-2">{{ $activeDelivery ? 'Sedang dalam perjalanan' : 'Menunggu tugas' }}</p>
                </div>
            </div>

            <div class="card-shell">
                <div class="card-core p-6">
                    <p class="metric-label">Pengiriman Selesai</p>
                    <p class="metric-value text-brand-primary mt-2.5">{{ $completedCount }}</p>
                    <p class="text-[0.6875rem] text-brand-slate mt-2">Total riwayat sukses</p>
                </div>
            </div>
        </div>

        @if ($activeDelivery)
            <div class="card-shell">
                <div class="card-core p-6 sm:p-7 border-l-4 border-brand-emerald space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-brand-emerald"></span>
                            <h2 class="text-sm font-bold text-brand-dark uppercase tracking-wide">Pengiriman Sedang Berlangsung</h2>
                        </div>
                        <a href="{{ route('kurir.shipments.index') }}" class="btn-primary text-xs py-1.5 px-4 font-semibold self-start sm:self-auto">
                            Buka Panel Kurir
                        </a>
                    </div>
                    <div class="text-xs text-brand-slate space-y-1">
                        <p class="font-mono font-bold text-brand-dark">{{ $activeDelivery->shipment_number }} (Pesanan {{ $activeDelivery->order->order_number }})</p>
                        <p>Tujuan: {{ $activeDelivery->order->items->pluck('umkm.business_name')->join(', ') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="card-shell">
            <div class="card-core p-6 sm:p-7 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <div>
                    <h3 class="font-bold text-brand-dark text-base">Panel Tugas Pengiriman</h3>
                    <p class="text-xs text-brand-slate mt-0.5">Mulai perjalanan atau kirim konfirmasi penerimaan barang.</p>
                </div>
                <a href="{{ route('kurir.shipments.index') }}" class="btn-primary text-xs py-2.5 px-5 font-semibold w-full sm:w-auto justify-center">
                    Buka Daftar Tugas
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
