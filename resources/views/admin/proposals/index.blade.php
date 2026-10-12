<x-layouts.app>
    <x-slot name="title">Usulan Produk Distributor</x-slot>

    <div class="space-y-8" x-data="{ rejectModal: false, rejectAction: '', rejectName: '' }">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <span class="eyebrow">Moderasi Katalog</span>
                <h1 class="font-display font-semibold text-[1.75rem] sm:text-4xl text-brand-dark tracking-tight text-balance mt-2">Usulan Produk Distributor</h1>
                <p class="text-[0.8125rem] text-brand-slate mt-1">Review produk custom dari distributor sebelum masuk katalog master.</p>
            </div>
            @if ($pendingCount > 0)
                <span class="badge bg-brand-ochre-soft text-brand-brass shrink-0">{{ $pendingCount }} menunggu review</span>
            @endif
        </div>

        @if ($proposals->isNotEmpty())
            <div class="card-shell">
                <div class="card-core p-6 sm:p-7 space-y-5">
                    <!-- Desktop: table -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="table-premium">
                            <thead>
                                <tr>
                                    <th>Usulan Produk</th>
                                    <th>Distributor</th>
                                    <th class="text-right">Stok / MOQ / Harga</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($proposals as $proposal)
                                    <tr>
                                        <td>
                                            <div class="font-bold text-brand-dark">{{ $proposal->name }}</div>
                                            <div class="text-[0.6875rem] text-brand-slate mt-0.5">
                                                {{ $proposal->category?->name ?? 'Tanpa kategori' }} &bull; per {{ $proposal->unit }}
                                            </div>
                                            @if ($proposal->admin_note)
                                                <div class="text-[0.6875rem] text-brand-slate mt-0.5 italic">Catatan: {{ $proposal->admin_note }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="font-bold text-brand-dark text-xs">{{ $proposal->distributor->company_name }}</div>
                                            <div class="text-[0.6875rem] text-brand-slate">{{ $proposal->distributor->user->name }}</div>
                                        </td>
                                        <td class="text-right text-xs tnum">
                                            <div class="font-bold text-brand-dark">{{ number_format($proposal->stock_available, 0) }} {{ $proposal->unit }}</div>
                                            <div class="text-brand-slate">MOQ {{ number_format($proposal->moq, 0) }} &bull; Rp {{ number_format($proposal->base_retail_price, 0, ',', '.') }}</div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $proposal->status->badgeClass() }}">{{ $proposal->status->label() }}</span>
                                        </td>
                                        <td class="text-right whitespace-nowrap">
                                            @if ($proposal->status->value === 'PENDING')
                                                <form method="POST" action="{{ route('admin.proposals.approve', $proposal) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="min-h-11 px-3 text-xs text-brand-emerald font-bold hover:underline cursor-pointer">
                                                        Setujui
                                                    </button>
                                                </form>
                                                <button type="button" @click="rejectAction = '{{ route('admin.proposals.reject', $proposal) }}'; rejectName = '{{ addslashes($proposal->name) }}'; rejectModal = true;" class="min-h-11 px-3 text-xs text-brand-brick font-bold hover:underline cursor-pointer">
                                                    Tolak
                                                </button>
                                            @elseif ($proposal->status->value === 'APPROVED' && $proposal->product)
                                                <a href="{{ route('admin.products.index') }}" class="min-h-11 inline-flex items-center px-3 text-xs text-brand-primary font-bold hover:underline">
                                                    Lihat di Katalog
                                                </a>
                                            @else
                                                <span class="text-[0.6875rem] text-brand-slate">Selesai</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile: cards -->
                    <div class="sm:hidden space-y-3">
                        @foreach ($proposals as $proposal)
                            <div class="list-card space-y-2.5">
                                <div class="flex justify-between items-start gap-3">
                                    <div class="min-w-0">
                                        <p class="font-bold text-brand-dark text-sm">{{ $proposal->name }}</p>
                                        <p class="text-[0.6875rem] text-brand-slate mt-0.5">{{ $proposal->category?->name ?? 'Tanpa kategori' }} &bull; per {{ $proposal->unit }}</p>
                                        <p class="text-[0.6875rem] text-brand-slate mt-0.5">{{ $proposal->distributor->company_name }}</p>
                                    </div>
                                    <span class="badge {{ $proposal->status->badgeClass() }} shrink-0">{{ $proposal->status->label() }}</span>
                                </div>
                                <div class="flex justify-between text-[0.75rem] tnum pt-2 border-t border-black/[0.04]">
                                    <span class="text-brand-slate">Stok {{ number_format($proposal->stock_available, 0) }} {{ $proposal->unit }}</span>
                                    <span class="font-extrabold text-brand-dark">Rp {{ number_format($proposal->base_retail_price, 0, ',', '.') }}</span>
                                </div>
                                @if ($proposal->admin_note)
                                    <p class="text-[0.6875rem] text-brand-slate italic">Catatan: {{ $proposal->admin_note }}</p>
                                @endif
                                @if ($proposal->status->value === 'PENDING')
                                    <div class="grid grid-cols-2 gap-2 pt-1">
                                        <form method="POST" action="{{ route('admin.proposals.approve', $proposal) }}">
                                            @csrf
                                            <button type="submit" class="btn-primary text-xs font-bold w-full">Setujui</button>
                                        </form>
                                        <button type="button" @click="rejectAction = '{{ route('admin.proposals.reject', $proposal) }}'; rejectName = '{{ addslashes($proposal->name) }}'; rejectModal = true;" class="btn-ghost text-xs font-bold w-full text-brand-brick">
                                            Tolak
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-3 border-t border-black/[0.04]">
                        {{ $proposals->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="card-shell">
                <div class="card-core px-6 py-12 text-center">
                    <p class="font-bold text-brand-dark">Tidak ada usulan produk.</p>
                    <p class="text-[0.8125rem] text-brand-slate mt-1.5 max-w-md mx-auto">Usulan dari distributor tampil di sini untuk direview sebelum masuk katalog master.</p>
                </div>
            </div>
        @endif

        <!-- Reject Modal -->
        <x-modal open="rejectModal" size="sm" eyebrow="Moderasi Katalog" title="Tolak Usulan Produk">
            <p class="text-xs text-brand-slate mt-4">Menolak <strong class="text-brand-dark" x-text="rejectName"></strong>. Tulis alasan agar distributor bisa memperbaiki.</p>
            <form :action="rejectAction" method="POST" class="pt-4 space-y-4">
                @csrf
                <div>
                    <label for="reject-note" class="field-label">Alasan penolakan</label>
                    <textarea id="reject-note" name="admin_note" rows="3" required placeholder="Contoh: nama kurang spesifik, tambahkan spesifikasi kemasan…" class="input-field"></textarea>
                </div>
                <div class="flex flex-col-reverse sm:flex-row-reverse sm:justify-end gap-3 pt-3 border-t border-black/[0.06]">
                    <button type="submit" class="btn-primary text-sm py-3 px-6 w-full sm:w-auto">Tolak Usulan</button>
                    <button type="button" @click="rejectModal = false" class="btn-ghost text-sm py-3 w-full sm:w-auto">Batal</button>
                </div>
            </form>
        </x-modal>
    </div>
</x-layouts.app>
