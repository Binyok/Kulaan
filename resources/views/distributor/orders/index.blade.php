<x-layouts.app>
    <x-slot name="title">Kelola Pesanan Kolektif</x-slot>

    <div class="space-y-8 animate-fade-up" x-data="{ openCourierModal: false, selectedOrder: null }">
        <div>
            <span class="eyebrow">Pesanan Masuk</span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-brand-dark tracking-tight mt-1.5">Pesanan Kolektif UMKM</h1>
            <p class="text-[0.8125rem] text-brand-slate mt-1">Kelola konfirmasi pesanan, persiapan barang, dan penugasan armada kurir.</p>
        </div>

        <!-- Table (Double-Bezel) -->
        <div class="card-shell">
            <div class="card-core p-6 sm:p-7 space-y-5">
                <div class="overflow-x-auto -mx-6 sm:mx-0">
                    <table class="table-premium">
                        <thead>
                            <tr>
                                <th>No. Pesanan</th>
                                <th>Detail Anggota UMKM</th>
                                <th class="text-center">Total Kuantitas</th>
                                <th class="text-right">Nilai Transaksi</th>
                                <th class="text-center">Status</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($orders as $order)
                                <tr>
                                    <td class="font-mono font-bold text-brand-dark">{{ $order->order_number }}</td>
                                    <td>
                                        <div class="space-y-1">
                                            @foreach ($order->items as $item)
                                                <div class="text-xs">
                                                    <span class="font-bold text-brand-dark">{{ $item->umkm->business_name }}</span>
                                                    <span class="text-brand-slate">({{ number_format($item->quantity, 0) }} {{ $item->product->unit }})</span>
                                                    <span class="badge {{ $item->payment_status->badgeClass() }} text-[10px]">
                                                        {{ $item->payment_status->label() }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="text-center font-bold text-brand-dark">{{ number_format($order->total_quantity, 0) }} unit</td>
                                    <td class="text-right font-extrabold text-brand-primary">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $order->status->badgeClass() }}">
                                            {{ $order->status->label() }}
                                        </span>
                                    </td>
                                    <td class="text-right space-x-2">
                                        @if ($order->status->value === 'PAID')
                                            <form method="POST" action="{{ route('distributor.orders.update-status', $order) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="status" value="CONFIRMED">
                                                <button type="submit" class="btn-primary text-xs py-1.5 px-3 font-semibold">
                                                    Konfirmasi
                                                </button>
                                            </form>
                                        @elseif ($order->status->value === 'CONFIRMED')
                                            <form method="POST" action="{{ route('distributor.orders.update-status', $order) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="status" value="PREPARING">
                                                <button type="submit" class="btn-primary text-xs py-1.5 px-3 font-semibold">
                                                    Siapkan Barang
                                                </button>
                                            </form>
                                        @elseif ($order->status->value === 'PREPARING')
                                            <button @click="selectedOrder = {{ json_encode($order) }}; openCourierModal = true" class="btn-accent text-xs py-1.5 px-3 font-semibold">
                                                Tugaskan Kurir
                                            </button>
                                        @else
                                            <span class="text-xs text-brand-slate">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-brand-slate text-[0.8125rem]">Belum ada pesanan kolektif masuk.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="pt-3 border-t border-black/[0.04]">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>

        <!-- Assign Courier Modal -->
        <div x-show="openCourierModal" x-cloak class="modal-overlay">
            <div @click.away="openCourierModal = false" class="modal-content max-w-md space-y-5 animate-fade-up">
                <div class="flex justify-between items-center pb-3 border-b border-black/[0.04]">
                    <h2 class="text-base font-bold text-brand-dark">Tugaskan Kurir Armada</h2>
                    <button @click="openCourierModal = false" class="text-brand-slate hover:text-brand-dark text-xl font-bold cursor-pointer">&times;</button>
                </div>
                <form :action="`/distributor/orders/${selectedOrder?.id}/assign-courier`" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Pilih Kurir Armada</label>
                        <select name="courier_id" required class="input-field bg-white">
                            @foreach ($couriers as $courier)
                                <option value="{{ $courier->id }}">{{ $courier->user->name }} ({{ $courier->vehicle_type }} - {{ $courier->vehicle_number }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Catatan Pengiriman</label>
                        <textarea name="notes" rows="2" placeholder="Petunjuk rute atau kontak penerima..." class="input-field"></textarea>
                    </div>
                    <div class="flex justify-end gap-2.5 pt-2">
                        <button type="button" @click="openCourierModal = false" class="btn-ghost text-xs py-2 px-4">Batal</button>
                        <button type="submit" class="btn-primary text-xs py-2 px-5">Tugaskan Kurir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
