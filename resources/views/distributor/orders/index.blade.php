<x-layouts.app>
    <x-slot name="title">Kelola Pesanan Kolektif</x-slot>

    <div class="space-y-8" x-data="{ openCourierModal: false, selectedOrder: null }">
        <div>
            <span class="eyebrow">Pesanan Masuk</span>
            <h1 class="font-display font-semibold text-[1.75rem] sm:text-4xl text-brand-dark tracking-tight text-balance mt-2">Pesanan Kolektif UMKM</h1>
            <p class="text-[0.8125rem] text-brand-slate mt-1">Kelola konfirmasi pesanan, persiapan barang, dan penugasan armada kurir.</p>
        </div>

        @php $hasOrders = $orders->isNotEmpty(); @endphp

        <!-- Orders -->
        @if ($hasOrders)
            <div class="card-shell">
                <div class="card-core p-6 sm:p-7 space-y-5">
                    <!-- Desktop: table -->
                    <div class="hidden md:block overflow-x-auto">
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
                                @foreach ($orders as $order)
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
                                        <td class="text-right">
                                            @include('distributor.orders._action-form', ['order' => $order])
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile: cards -->
                    <div class="md:hidden space-y-3">
                        @foreach ($orders as $order)
                            <div class="list-card space-y-3">
                                <div class="flex justify-between items-start gap-3">
                                    <div class="min-w-0">
                                        <p class="font-mono font-bold text-brand-dark text-sm">{{ $order->order_number }}</p>
                                        <p class="font-extrabold text-brand-primary text-sm mt-1">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                                    </div>
                                    <span class="badge {{ $order->status->badgeClass() }} shrink-0">{{ $order->status->label() }}</span>
                                </div>

                                <div class="space-y-1.5 pt-2 border-t border-black/[0.04]">
                                    @foreach ($order->items as $item)
                                        <div class="flex justify-between items-center gap-2 text-[0.75rem]">
                                            <span class="font-bold text-brand-dark min-w-0 truncate">{{ $item->umkm->business_name }}</span>
                                            <span class="flex items-center gap-1.5 shrink-0">
                                                <span class="text-brand-slate">{{ number_format($item->quantity, 0) }} {{ $item->product->unit }}</span>
                                                <span class="badge {{ $item->payment_status->badgeClass() }} text-[10px]">{{ $item->payment_status->label() }}</span>
                                            </span>
                                        </div>
                                    @endforeach
                                    <div class="flex justify-between text-[0.75rem] pt-1.5">
                                        <span class="text-brand-slate">Total kuantitas</span>
                                        <span class="font-bold text-brand-dark">{{ number_format($order->total_quantity, 0) }} unit</span>
                                    </div>
                                </div>

                                <div class="pt-1">
                                    @include('distributor.orders._action-form', ['order' => $order])
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-3 border-t border-black/[0.04]">
                        {{ $orders->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="card-shell">
                <div class="card-core px-6 py-12 text-center">
                    <p class="font-bold text-brand-dark">Belum ada pesanan kolektif masuk.</p>
                    <p class="text-[0.8125rem] text-brand-slate mt-1.5 max-w-md mx-auto">Pesanan muncul otomatis setelah grup UMKM mencapai kuota MOQ dan pembayaran terkonfirmasi.</p>
                    <a href="{{ route('distributor.products.index') }}" class="btn-primary text-sm mt-5">Kelola Katalog Produk</a>
                </div>
            </div>
        @endif

        <!-- Assign Courier Modal -->
        <x-modal open="openCourierModal" size="sm"
                 eyebrow="Penugasan Armada"
                 title="Tugaskan Kurir">
            <form :action="`/distributor/orders/${selectedOrder?.id}/assign-courier`" method="POST" class="pt-5 space-y-4">
                @csrf
                <fieldset>
                    <legend class="field-label">Pilih kurir armada</legend>
                    @if ($couriers->isEmpty())
                        <p class="text-xs text-brand-slate p-3 bg-brand-cream rounded-xl border border-black/[0.04]">
                            Belum ada kurir terdaftar. Daftarkan kurir terlebih dahulu.
                        </p>
                    @else
                        <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                            @foreach ($couriers as $courier)
                                <label class="flex items-center gap-3 border rounded-xl p-3 min-h-[3.5rem] cursor-pointer transition-colors border-black/[0.08] hover:border-black/20 has-[:checked]:border-brand-primary has-[:checked]:bg-brand-mint has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-brand-primary/40 has-[:focus-visible]:ring-offset-2">
                                    <input type="radio" name="courier_id" value="{{ $courier->id }}" {{ $loop->first ? 'checked' : '' }} class="peer sr-only" required>
                                    <span aria-hidden="true" class="w-5 h-5 rounded-full border-2 border-black/25 bg-white shrink-0 transition-colors peer-checked:border-brand-primary peer-checked:bg-brand-primary"></span>
                                    <span class="min-w-0">
                                        <span class="block text-sm font-bold text-brand-dark">{{ $courier->user->name }}</span>
                                        <span class="block text-[0.6875rem] text-brand-slate mt-0.5">{{ $courier->vehicle_type }} &bull; {{ $courier->vehicle_number }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </fieldset>

                <div>
                    <label for="courier-notes" class="field-label">Catatan pengiriman (opsional)</label>
                    <textarea id="courier-notes" name="notes" rows="2" placeholder="Petunjuk rute atau kontak penerima..." class="input-field"></textarea>
                </div>

                <div class="flex flex-col-reverse sm:flex-row-reverse sm:justify-end gap-3 pt-3 border-t border-black/[0.06]">
                    <button type="submit" class="btn-primary text-sm py-3 px-6 w-full sm:w-auto" @if ($couriers->isEmpty()) disabled @endif>
                        Tugaskan Kurir
                    </button>
                    <button type="button" @click="openCourierModal = false" class="btn-ghost text-sm py-3 w-full sm:w-auto">Batal</button>
                </div>
            </form>
        </x-modal>
    </div>
</x-layouts.app>
