<x-layouts.app>
    <x-slot name="title">Kelola Pesanan Kolektif</x-slot>

    <div x-data="{ openCourierModal: false, selectedOrder: null }">
        {{-- Forest band: page header --}}
        <div class="-mx-3 sm:-mx-6 lg:-mx-8 -mt-6 sm:-mt-8 mb-6 sm:mb-8">
            <div class="texture-grain bg-brand-forest text-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
                    <div class="max-w-2xl" data-reveal>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[0.625rem] font-semibold bg-white/10 text-[#D8E8DD] uppercase tracking-[0.14em]">
                            Pesanan Masuk
                        </span>
                        <h1 class="font-display font-semibold tracking-tight text-balance text-4xl sm:text-5xl mt-4">
                            Pesanan Kolektif UMKM
                        </h1>
                        <p class="text-sm text-white/70 leading-relaxed mt-3">
                            Konfirmasi pesanan, siapkan barang, terus tugaskan kurir — alurnya berurutan biar nggak ada yang kelewat.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6 sm:space-y-8">
            @php $hasOrders = $orders->isNotEmpty(); @endphp

            @if ($hasOrders)
                <section class="bg-white rounded-[20px] border border-brand-line p-5 sm:p-7" data-reveal>
                    <div class="divide-y divide-black/[0.05]">
                        @foreach ($orders as $order)
                            <div class="py-4 flex gap-4 items-start hover:bg-brand-cream/60 -mx-2 px-2 sm:-mx-4 sm:px-4 rounded-xl transition-colors">
                                <span class="w-11 h-11 rounded-full bg-brand-mint text-brand-primary flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 17h6m-5 4h4M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 8h6M9 12h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="font-mono font-bold text-brand-dark text-sm">{{ $order->order_number }}</p>
                                            <p class="text-[0.6875rem] text-brand-slate mt-0.5">{{ number_format($order->total_quantity, 0) }} unit &bull; Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                                        </div>
                                        <span class="badge {{ $order->status->badgeClass() }} shrink-0">{{ $order->status->label() }}</span>
                                    </div>
                                    <div class="mt-2.5 space-y-1.5">
                                        @foreach ($order->items as $item)
                                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-[0.75rem]">
                                                <span class="font-bold text-brand-dark">{{ $item->umkm->business_name }}</span>
                                                <span class="text-brand-slate">{{ number_format($item->quantity, 0) }} {{ $item->product->unit }}</span>
                                                <span class="badge {{ $item->payment_status->badgeClass() }} text-[10px]">{{ $item->payment_status->label() }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="mt-3">
                                        @include('distributor.orders._action-form', ['order' => $order])
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-4 mt-2 border-t border-black/[0.04]">
                        {{ $orders->links() }}
                    </div>
                </section>
            @else
                <section class="bg-white rounded-[20px] border border-brand-line px-6 py-12 text-center" data-reveal>
                    <span class="w-14 h-14 rounded-full bg-brand-mint text-brand-primary inline-flex items-center justify-center">
                        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 17h6m-5 4h4M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <p class="font-bold text-brand-dark mt-4">Belum ada pesanan kolektif masuk.</p>
                    <p class="text-[0.8125rem] text-brand-slate mt-1.5 max-w-md mx-auto">Pesanan muncul otomatis setelah grup UMKM mencapai kuota MOQ dan pembayaran terkonfirmasi.</p>
                    <a href="{{ route('distributor.products.index') }}" class="btn-primary text-sm mt-5">Kelola Katalog Produk</a>
                </section>
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
    </div>
</x-layouts.app>
