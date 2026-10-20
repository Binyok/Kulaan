<x-layouts.admin title="Usulan Produk Distributor">
    <div x-data="{ rejectModal: false, rejectAction: '', rejectName: '' }">

        <!-- ============================ MOBILE ============================ -->
        <div class="lg:hidden pb-24">
            <div class="px-4 pt-2 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="relative" x-data="{ menuOpen: false }">
                        <button type="button" @click="menuOpen = !menuOpen" class="w-11 h-11 rounded-2xl bg-forest text-white flex items-center justify-center font-extrabold text-sm cursor-pointer">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</button>
                        <div x-show="menuOpen" @click.away="menuOpen = false" x-cloak class="absolute left-0 mt-2 w-48 bg-white border border-line rounded-2xl shadow-xl p-2 z-40 text-xs">
                            <a href="{{ route('profile.complete') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-ink hover:bg-cream font-medium">Profil Saya</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-3 py-2.5 rounded-xl text-danger hover:bg-danger/10 font-bold transition text-left cursor-pointer">Keluar</button>
                            </form>
                        </div>
                    </div>
                    <div>
                        <p class="text-[11px] text-muted">Kontrol Pusat,</p>
                        <p class="text-[15px] font-extrabold leading-tight">{{ auth()->user()->name }}</p>
                    </div>
                </div>
                <x-notification-bell />
            </div>

            <div class="px-4 mt-4">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div>
                        <span class="eyebrow text-brass">Moderasi Katalog</span>
                        <h1 class="text-[1.75rem] sm:text-3xl font-extrabold text-ink tracking-tight mt-2">Usulan Produk Distributor</h1>
                        <p class="text-[0.8125rem] text-muted mt-1">Review produk custom sebelum masuk katalog master.</p>
                    </div>
                    @if ($pendingCount > 0)
                        <span class="badge bg-brass-soft text-brass !text-xs !py-1.5 !px-4 shrink-0">{{ $pendingCount }} menunggu review</span>
                    @endif
                </div>

                @if ($proposals->isNotEmpty())
                    <div class="flex flex-col sm:flex-row gap-2 mb-4">
                        <div class="flex items-center gap-2 bg-white border border-line rounded-2xl pl-4 pr-2 py-2 flex-1">
                            <span class="text-muted"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m21 21-5.2-5.2m0 0A7.5 7.5 0 1 0 5.2 5.2a7.5 7.5 0 0 0 10.6 10.6Z"/></svg></span>
                            <input class="flex-1 bg-transparent text-sm outline-none placeholder:text-muted/70" placeholder="Cari usulan produk...">
                        </div>
                    </div>

                    @foreach ($proposals as $proposal)
                        @php
                            $statusBadge = match ($proposal->status->value) {
                                'PENDING' => 'bg-brass-soft text-brass',
                                'APPROVED' => 'bg-success/10 text-success',
                                'REJECTED' => 'bg-danger/10 text-danger',
                                default => 'bg-brass-soft text-brass',
                            };
                        @endphp
                        <div class="card p-4 space-y-2 mb-3">
                            <div class="flex justify-between items-start gap-3">
                                <div class="min-w-0">
                                    <p class="font-bold text-ink text-sm">{{ $proposal->name }}</p>
                                    <p class="text-[11px] text-muted mt-0.5">{{ $proposal->category?->name ?? 'Tanpa kategori' }} &bull; per {{ $proposal->unit }}</p>
                                    <p class="text-[11px] text-muted mt-0.5">{{ $proposal->distributor->company_name }}</p>
                                </div>
                                <span class="badge {{ $statusBadge }} shrink-0">{{ $proposal->status->label() }}</span>
                            </div>
                            <div class="flex justify-between text-xs tnum pt-2 border-t border-black/[0.04]">
                                <span class="text-muted">Stok {{ number_format($proposal->stock_available, 0) }} {{ $proposal->unit }} &bull; MOQ {{ number_format($proposal->moq, 0) }}</span>
                                <span class="font-extrabold text-ink">Rp {{ number_format($proposal->base_retail_price, 0, ',', '.') }}</span>
                            </div>
                            @if ($proposal->admin_note)
                                <p class="text-[11px] text-muted italic">Catatan: {{ $proposal->admin_note }}</p>
                            @endif
                            @if ($proposal->status->value === 'PENDING')
                                <div class="grid grid-cols-2 gap-2 pt-1">
                                    <form method="POST" action="{{ route('admin.proposals.approve', $proposal) }}">
                                        @csrf
                                        <button type="submit" class="btn-primary !py-2.5 text-xs justify-center w-full">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m4.5 12.75 6 6 9-13.5"/></svg>
                                            Setujui
                                        </button>
                                    </form>
                                    <button type="button" @click="rejectAction = '{{ route('admin.proposals.reject', $proposal) }}'; rejectName = '{{ addslashes($proposal->name) }}'; rejectModal = true;" class="btn-ghost !py-2.5 text-xs justify-center w-full !text-danger">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 18 18 6M6 6l12 12"/></svg>
                                        Tolak
                                    </button>
                                </div>
                            @elseif ($proposal->status->value === 'APPROVED' && $proposal->product)
                                <a href="{{ route('admin.products.index') }}" class="text-xs text-forest font-bold">Lihat di Katalog &rarr;</a>
                            @endif
                        </div>
                    @endforeach

                    <div class="pt-4 mt-4 border-t border-black/[0.05]">
                        {{ $proposals->links() }}
                    </div>
                @else
                    <div class="card px-6 py-12 text-center">
                        <p class="font-bold text-ink">Tidak ada usulan produk.</p>
                        <p class="text-[0.8125rem] text-muted mt-1.5 max-w-md mx-auto">Usulan dari distributor tampil di sini untuk direview sebelum masuk katalog master.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- bottom tab -->
        <div class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-white border-t border-line px-2 pt-2 pb-5 flex items-start justify-around">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 w-14 text-muted">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m2.25 12 8.95-8.95a1.13 1.13 0 0 1 1.6 0l8.95 8.95M4.5 9.75V19.5a.75.75 0 0 0 .75.75h13.5a.75.75 0 0 0 .75-.75V9.75"/></svg>
                <span class="text-[10px] font-semibold">Beranda</span>
            </a>
            <a href="{{ route('admin.proposals.index') }}" class="flex flex-col items-center gap-1 w-14 text-forest relative">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5v14m-7-7h14"/></svg>
                <span class="text-[10px] font-bold">Usulan</span>
                @if ($pendingCount > 0)
                    <span class="absolute top-0 right-2 min-w-[18px] h-[18px] px-1 rounded-full bg-brass text-white text-[10px] font-bold flex items-center justify-center">{{ $pendingCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.spk.index') }}" class="flex flex-col items-center gap-1 w-14 text-muted">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 20h18M6 16v-5m6 5V8m6 8v-9"/></svg>
                <span class="text-[10px] font-semibold">SPK</span>
            </a>
            <a href="{{ route('profile.complete') }}" class="flex flex-col items-center gap-1 w-14 text-muted">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17.98 18.72a6 6 0 0 0-8.46 0M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm-9.75 8.25a9 9 0 1 1 13.5 0"/></svg>
                <span class="text-[10px] font-semibold">Akun</span>
            </a>
        </div>

        <!-- ============================ DESKTOP ============================ -->
        <div class="hidden lg:flex min-h-screen">
            <!-- SIDEBAR -->
            <aside class="w-64 shrink-0 bg-white border-r border-line flex flex-col sticky top-0 h-screen">
                <div class="flex items-center gap-2.5 px-6 h-[72px] border-b border-line">
                    <span class="w-10 h-10 rounded-2xl bg-forest flex items-center justify-center text-white">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.25 7.5l-.63 10.63a2.25 2.25 0 0 1-2.25 2.12H6.63a2.25 2.25 0 0 1-2.25-2.12L3.75 7.5M10 11.25h4M3.38 7.5h17.25c.62 0 1.12-.5 1.12-1.12v-1.5C21.75 4.26 21.25 3.75 20.63 3.75H3.38c-.62 0-1.13.5-1.13 1.12v1.5c0 .63.5 1.13 1.12 1.13Z"/></svg>
                    </span>
                    <span class="text-xl font-extrabold tracking-tight">Kulaan</span>
                </div>
                <nav class="flex-1 px-4 py-6 space-y-1 text-sm font-semibold">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m2.25 12 8.95-8.95a1.13 1.13 0 0 1 1.6 0l8.95 8.95M4.5 9.75V19.5a.75.75 0 0 0 .75.75h13.5a.75.75 0 0 0 .75-.75V9.75"/></svg>
                        Dashboard
                    </a>
                    <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7a2 2 0 0 1 2-2h4l2 2h6a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7Z"/></svg>
                        Master Kategori
                    </a>
                    <a href="{{ route('admin.products.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5M12 22V12"/></svg>
                        Master Produk
                    </a>
                    <a href="{{ route('admin.proposals.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-forest text-white">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5v14m-7-7h14"/></svg>
                        Usulan Produk
                        @if ($pendingCount > 0)
                            <span class="ml-auto bg-brass text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $pendingCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('admin.spk.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 20h18M6 16v-5m6 5V8m6 8v-9"/></svg>
                        Kriteria SPK
                    </a>
                </nav>
                <div class="p-4 border-t border-line">
                    <a href="{{ route('profile.complete') }}" class="flex items-center gap-3 px-2">
                        <span class="w-10 h-10 rounded-2xl bg-forest text-white flex items-center justify-center font-extrabold text-xs">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                        <div class="min-w-0">
                            <p class="text-sm font-bold truncate">{{ auth()->user()->name }}</p>
                            <p class="text-[11px] text-muted">Kontrol Pusat</p>
                        </div>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="mt-3">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2.5 text-xs font-bold text-danger bg-danger/10 hover:bg-danger/15 rounded-xl transition cursor-pointer">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3h4a3 3 0 0 1 3 3v1"/></svg>
                            Keluar
                        </button>
                    </form>
                </div>
            </aside>

            <!-- MAIN -->
            <div class="flex-1 min-w-0">
                <header class="bg-white/70 backdrop-blur border-b border-line sticky top-0 z-10">
                    <div class="px-8 h-[72px] flex items-center gap-4">
                        <div>
                            <h1 class="text-lg font-extrabold leading-tight">Usulan Produk Distributor</h1>
                            <p class="text-xs text-muted">{{ now()->translatedFormat('l, d F Y') }}</p>
                        </div>
                        <div class="ml-auto flex items-center gap-3">
                            <div class="hidden lg:flex items-center gap-2 bg-white border border-line rounded-2xl pl-4 pr-2 py-2 w-72">
                                <svg class="w-4 h-4 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21 21-5.2-5.2m0 0A7.5 7.5 0 1 0 5.2 5.2a7.5 7.5 0 0 0 10.6 10.6Z"/></svg>
                                <input class="flex-1 bg-transparent text-sm outline-none placeholder:text-muted/70" placeholder="Cari usulan produk...">
                            </div>
                            <x-notification-bell />
                        </div>
                    </div>
                </header>

                <main class="px-8 py-6 max-w-6xl">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                        <div>
                            <span class="eyebrow text-brass">Moderasi Katalog</span>
                            <h1 class="text-[1.75rem] sm:text-3xl font-extrabold text-ink tracking-tight mt-2">Usulan Produk Distributor</h1>
                            <p class="text-[0.8125rem] text-muted mt-1">Review produk custom dari distributor sebelum masuk katalog master.</p>
                        </div>
                        @if ($pendingCount > 0)
                            <span class="badge bg-brass-soft text-brass !text-xs !py-1.5 !px-4 shrink-0">{{ $pendingCount }} menunggu review</span>
                        @endif
                    </div>

                    @if ($proposals->isNotEmpty())
                        <div class="card p-6">
                            <div class="flex flex-col sm:flex-row gap-2 mb-4">
                                <div class="flex items-center gap-2 bg-white border border-line rounded-2xl pl-4 pr-2 py-2 flex-1">
                                    <span class="text-muted"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m21 21-5.2-5.2m0 0A7.5 7.5 0 1 0 5.2 5.2a7.5 7.5 0 0 0 10.6 10.6Z"/></svg></span>
                                    <input class="flex-1 bg-transparent text-sm outline-none placeholder:text-muted/70" placeholder="Cari usulan produk / distributor...">
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="tbl w-full">
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
                                            @php
                                                $statusBadge = match ($proposal->status->value) {
                                                    'PENDING' => 'bg-brass-soft text-brass',
                                                    'APPROVED' => 'bg-success/10 text-success',
                                                    'REJECTED' => 'bg-danger/10 text-danger',
                                                    default => 'bg-brass-soft text-brass',
                                                };
                                            @endphp
                                            <tr>
                                                <td>
                                                    <div class="font-bold text-ink">{{ $proposal->name }}</div>
                                                    <div class="text-[11px] text-muted mt-0.5">{{ $proposal->category?->name ?? 'Tanpa kategori' }} &bull; per {{ $proposal->unit }}</div>
                                                    @if ($proposal->admin_note)
                                                        <div class="text-[11px] text-muted mt-0.5 italic">Catatan: {{ $proposal->admin_note }}</div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="font-bold text-ink text-xs">{{ $proposal->distributor->company_name }}</div>
                                                    <div class="text-[11px] text-muted">{{ $proposal->distributor->user->name }}</div>
                                                </td>
                                                <td class="text-right text-xs tnum">
                                                    <div class="font-bold text-ink">{{ number_format($proposal->stock_available, 0) }} {{ $proposal->unit }}</div>
                                                    <div class="text-muted">MOQ {{ number_format($proposal->moq, 0) }} &bull; Rp {{ number_format($proposal->base_retail_price, 0, ',', '.') }}</div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge {{ $statusBadge }}">{{ $proposal->status->label() }}</span>
                                                </td>
                                                <td class="text-right whitespace-nowrap">
                                                    @if ($proposal->status->value === 'PENDING')
                                                        <form method="POST" action="{{ route('admin.proposals.approve', $proposal) }}" class="inline">
                                                            @csrf
                                                            <button type="submit" class="inline-flex items-center gap-1 min-h-[44px] px-3 text-xs text-success font-bold hover:underline cursor-pointer">
                                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m4.5 12.75 6 6 9-13.5"/></svg>
                                                                Setujui
                                                            </button>
                                                        </form>
                                                        <button type="button" @click="rejectAction = '{{ route('admin.proposals.reject', $proposal) }}'; rejectName = '{{ addslashes($proposal->name) }}'; rejectModal = true;" class="inline-flex items-center gap-1 min-h-[44px] px-3 text-xs text-danger font-bold hover:underline cursor-pointer">
                                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 18 18 6M6 6l12 12"/></svg>
                                                            Tolak
                                                        </button>
                                                    @elseif ($proposal->status->value === 'APPROVED' && $proposal->product)
                                                        <a href="{{ route('admin.products.index') }}" class="min-h-[44px] inline-flex items-center px-3 text-xs text-forest font-bold hover:underline">
                                                            Lihat di Katalog
                                                        </a>
                                                    @else
                                                        <span class="text-[11px] text-muted">Selesai</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="pt-4 mt-4 border-t border-black/[0.05]">
                                {{ $proposals->links() }}
                            </div>
                        </div>
                    @else
                        <div class="card px-6 py-12 text-center">
                            <p class="font-bold text-ink">Tidak ada usulan produk.</p>
                            <p class="text-[0.8125rem] text-muted mt-1.5 max-w-md mx-auto">Usulan dari distributor tampil di sini untuk direview sebelum masuk katalog master.</p>
                        </div>
                    @endif
                </main>
            </div>
        </div>

        <!-- Reject Modal -->
        <x-modal open="rejectModal" size="sm" eyebrow="Moderasi Katalog" title="Tolak Usulan Produk">
            <p class="text-xs text-muted mt-4">Menolak <strong class="text-ink" x-text="rejectName"></strong>. Tulis alasan agar distributor bisa memperbaiki.</p>
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
</x-layouts.admin>
