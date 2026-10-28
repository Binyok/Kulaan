<x-layouts.app>
    <x-slot name="title">Katalog &amp; Tingkat Harga Grosir</x-slot>

    @php
        $user = auth()->user();
    @endphp

    <div x-data="{
        openAddProduct: false,
        openProposal: false,
        tierModal: false,
        selectedProduct: null,
        tiers: [],
        openEditProduct: false,
        editProductData: {}
    }">
        {{-- Mobile shell --}}
        <div class="lg:hidden bg-cream min-h-screen">
            <div class="pb-24">
                <div class="px-4 pt-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="relative" x-data="{ menuOpen: false }">
                            <button type="button" @click="menuOpen = !menuOpen" class="w-11 h-11 rounded-2xl bg-forest text-white flex items-center justify-center font-extrabold text-sm cursor-pointer">{{ strtoupper(substr($user->name, 0, 2)) }}</button>
                            <div x-show="menuOpen" @click.away="menuOpen = false" x-cloak class="absolute left-0 mt-2 w-48 bg-white border border-line rounded-2xl shadow-xl p-2 z-40 text-xs">
                                <a href="{{ route('profile.complete') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-ink hover:bg-cream font-medium">Profil Saya</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2 px-3 py-2.5 rounded-xl text-danger hover:bg-danger/10 font-bold transition text-left cursor-pointer">Keluar</button>
                                </form>
                            </div>
                        </div>
                        <div>
                            <p class="text-[11px] text-muted">Selamat pagi,</p>
                            <p class="text-[15px] font-extrabold leading-tight">{{ $distributor?->company_name ?? $user->name }}</p>
                        </div>
                    </div>
                    <x-notification-bell />
                </div>

                <div class="px-4 mt-4">
                    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-forest-deep via-forest to-forest-dark text-white p-5">
                        <div class="absolute -right-10 -top-12 w-44 h-44 rounded-full bg-white/10"></div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold bg-white/10 text-white/85 uppercase tracking-[0.14em]">Distributor Portal</span>
                        <h1 class="text-2xl font-extrabold mt-2 tracking-tight">Katalog &amp; Harga Tier</h1>
                        <p class="text-xs text-white/70 leading-relaxed mt-1.5">Atur stok, MOQ grosir, dan tingkatan harga — makin besar kuota grup, makin murah harganya.</p>
                        <div class="flex gap-2 mt-4 relative">
                            @if ($availableProducts->count() > 0)
                                <button type="button" @click="openAddProduct = true" class="flex-1 bg-brass text-white text-xs font-bold py-3 rounded-2xl cursor-pointer">Daftarkan Produk Baru</button>
                            @endif
                            <button type="button" @click="openProposal = true" class="flex-1 border border-white/30 text-white text-xs font-bold py-3 rounded-2xl cursor-pointer">Usulkan Produk Baru</button>
                        </div>
                    </div>
                </div>

                <div class="px-4 mt-4 space-y-3 pb-6">
                    @forelse ($supplierProducts as $sp)
                        <article class="bg-white rounded-3xl border border-line p-4 shadow-sm {{ $sp->is_available ? '' : 'opacity-90' }}">
                            <div class="flex items-start gap-3">
                                <span class="w-12 h-12 rounded-2xl {{ $sp->is_available ? 'bg-success/10 text-forest' : 'bg-cream border border-line text-muted' }} flex items-center justify-center shrink-0 font-extrabold text-lg">{{ strtoupper(substr($sp->product->name, 0, 1)) }}</span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <p class="font-bold text-ink text-sm">{{ $sp->product->name }}</p>
                                            <p class="text-[11px] text-muted mt-0.5">{{ $sp->product->category->name }}</p>
                                        </div>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold {{ $sp->is_available ? 'bg-success/10 text-success' : 'bg-cream border border-line text-muted' }} shrink-0">{{ $sp->is_available ? 'Tersedia' : 'Habis' }}</span>
                                    </div>
                                    <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-[11px] text-muted">
                                        <span>Stok <strong class="{{ $sp->is_available ? 'text-ink' : 'text-danger' }}">{{ number_format($sp->stock_available, 0) }} {{ $sp->product->unit }}</strong></span>
                                        <span>MOQ <strong class="text-ink">{{ number_format($sp->moq, 0) }} {{ $sp->product->unit }}</strong></span>
                                        <span>Eceran <strong class="text-ink">Rp {{ number_format($sp->base_retail_price, 0, ',', '.') }}</strong></span>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5 mt-2.5">
                                        @forelse ($sp->priceTiers as $tier)
                                            <span class="text-[10px] font-bold bg-brass-soft text-brass border border-brass/20 rounded-full px-2.5 py-1 tabular-nums">
                                                {{ number_format($tier->min_quantity, 0) }}{{ $tier->max_quantity ? '–'.number_format($tier->max_quantity, 0) : '+' }} {{ $sp->product->unit }}: Rp {{ number_format($tier->unit_price, 0, ',', '.') }}
                                            </span>
                                        @empty
                                            <span class="text-[11px] text-muted italic">Belum ada tier harga</span>
                                        @endforelse
                                    </div>
                                    <div class="flex gap-4 mt-3">
                                        <button type="button" @click="
                                            selectedProduct = {{ json_encode($sp) }};
                                            tiers = {{ json_encode($sp->priceTiers) }};
                                            if (tiers.length === 0) {
                                                tiers = [{ min_quantity: 1, max_quantity: 4.99, unit_price: {{ $sp->base_retail_price }} }];
                                            }
                                            tierModal = true;
                                        " class="text-xs font-bold text-brass cursor-pointer">
                                            Kelola Tier
                                        </button>
                                        <button type="button" @click="editProductData = {{ json_encode($sp) }}; openEditProduct = true;" class="text-xs font-bold text-forest cursor-pointer">
                                            Edit Stok/MOQ
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @empty
                        <section class="bg-white rounded-3xl border border-line px-6 py-12 text-center shadow-sm">
                            <span class="w-14 h-14 rounded-2xl bg-success/10 text-forest inline-flex items-center justify-center mb-4">
                                <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5M12 22V12"/></svg>
                            </span>
                            <h3 class="font-extrabold text-ink text-base">Belum ada produk di katalogmu.</h3>
                            <p class="text-[11px] text-muted mt-1.5 max-w-md mx-auto leading-relaxed">Daftarkan produk beserta stok, MOQ, dan tier harga grosir biar mulai menerima pesanan kolektif.</p>
                            @if ($availableProducts->count() > 0)
                                <button type="button" @click="openAddProduct = true" class="mt-5 bg-brass text-white text-xs font-bold py-3 px-6 rounded-2xl cursor-pointer">Daftarkan Produk Baru</button>
                            @endif
                        </section>
                    @endforelse

                    @if ($supplierProducts->hasPages())
                        <div class="pt-2">{{ $supplierProducts->links() }}</div>
                    @endif

                    <section class="bg-white rounded-3xl border border-line p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h2 class="text-sm font-extrabold text-ink">Usulan Produk Baru</h2>
                                <p class="text-[11px] text-muted mt-1">Produk di luar katalog master — aktif setelah disetujui admin.</p>
                            </div>
                            <button type="button" @click="openProposal = true" class="text-xs font-bold text-forest border border-forest/25 rounded-xl px-3 py-2 shrink-0 cursor-pointer">Usulkan Produk</button>
                        </div>

                        @forelse ($proposals as $proposal)
                            @php
                                $statusBadge = match ($proposal->status->value) {
                                    'PENDING' => 'bg-brass-soft text-brass',
                                    'APPROVED' => 'bg-success/10 text-success',
                                    default => 'bg-danger/10 text-danger',
                                };
                            @endphp
                            <div class="mt-3 bg-cream/60 rounded-2xl p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-bold text-ink text-xs">{{ $proposal->name }}</p>
                                        <p class="text-[11px] text-muted mt-0.5">
                                            {{ $proposal->category?->name ?? 'Tanpa kategori' }} &bull; per {{ $proposal->unit }} &bull; MOQ {{ number_format($proposal->moq, 0) }} &bull; Rp {{ number_format($proposal->base_retail_price, 0, ',', '.') }}
                                        </p>
                                        @if ($proposal->status->value === 'REJECTED' && $proposal->admin_note)
                                            <p class="text-[11px] text-danger mt-1">Catatan admin: {{ $proposal->admin_note }}</p>
                                        @endif
                                        @if ($proposal->status->value === 'APPROVED')
                                            <p class="text-[11px] text-success font-semibold mt-1">Sudah masuk katalog master — daftarkan lewat tombol di atas.</p>
                                        @endif
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold {{ $statusBadge }} shrink-0">{{ $proposal->status->label() }}</span>
                                </div>
                                @if ($proposal->status->value === 'PENDING')
                                    <form method="POST" action="{{ route('distributor.products.proposals.destroy', $proposal) }}" id="delete-proposal-mobile-{{ $proposal->id }}" class="mt-2">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                                @click="kulaanConfirm({
                                                    title: 'Tarik Usulan Produk?',
                                                    message: 'Apakah Anda yakin ingin menarik kembali usulan produk ini dari antrean verifikasi admin?',
                                                    confirmText: 'Ya, Tarik Usulan',
                                                    cancelText: 'Kembali',
                                                    isDanger: true,
                                                    onConfirm: () => document.getElementById('delete-proposal-mobile-{{ $proposal->id }}').submit()
                                                })"
                                                class="min-h-11 px-3 text-xs font-semibold text-danger hover:underline cursor-pointer">
                                            Tarik
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <p class="text-[11px] text-muted py-3">Belum ada usulan. Punya barang di luar katalog? Usulkan agar bisa dijual kolektif.</p>
                        @endforelse

                        @if ($proposals->hasPages())
                            <div class="pt-3">{{ $proposals->links() }}</div>
                        @endif
                    </section>
                </div>
            </div>

            <x-mobile-tab role="distributor" active="catalog" />
        </div>

        {{-- Desktop shell --}}
        <div class="hidden lg:flex min-h-screen">
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
                    <a href="{{ route('distributor.products.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-forest text-white">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5M12 22V12"/></svg>
                        Katalog Produk
                    </a>
                    <a href="{{ route('distributor.orders.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 17h6m-5 4h4M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/><path d="M9 8h6M9 12h4"/></svg>
                        Pesanan
                    </a>
                </nav>
                <div class="p-4 border-t border-line">
                    <a href="{{ route('profile.complete') }}" class="flex items-center gap-3 px-2">
                        <span class="w-10 h-10 rounded-2xl bg-forest text-white flex items-center justify-center font-extrabold text-xs">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                        <div class="min-w-0">
                            <p class="text-sm font-bold truncate">{{ $distributor?->company_name ?? $user->name }}</p>
                            <p class="text-[11px] text-muted">{{ $distributor?->city ?? 'Sidoarjo' }}, {{ $distributor?->province ?? 'Jawa Timur' }}</p>
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

            <div class="flex-1 min-w-0">
                <header class="bg-white/70 backdrop-blur border-b border-line sticky top-0 z-10">
                    <div class="px-8 h-[72px] flex items-center gap-4">
                        <div>
                            <h1 class="text-lg font-extrabold leading-tight">Selamat pagi, {{ $distributor?->company_name ?? $user->name }}</h1>
                            <p class="text-xs text-muted">{{ now()->translatedFormat('l, d F Y') }}</p>
                        </div>
                        <div class="ml-auto flex items-center gap-3">
                            <div class="hidden lg:flex items-center gap-2 bg-white border border-line rounded-2xl pl-4 pr-2 py-2 w-72">
                                <svg class="w-4 h-4 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21 21-5.2-5.2m0 0A7.5 7.5 0 1 0 5.2 5.2a7.5 7.5 0 0 0 10.6 10.6Z"/></svg>
                                <input class="flex-1 bg-transparent text-sm outline-none placeholder:text-muted/70" placeholder="Cari pesanan, produk...">
                            </div>
                            <x-notification-bell />
                        </div>
                    </div>
                </header>

                <div class="bg-gradient-to-r from-forest-deep via-forest to-forest-dark text-white">
                    <div class="px-8 py-12 flex flex-col lg:flex-row lg:items-end justify-between gap-6">
                        <div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-bold bg-white/10 text-white/85 uppercase tracking-[0.14em]">Distributor Portal</span>
                            <h1 class="text-4xl font-extrabold mt-3 tracking-tight">Katalog &amp; Harga Tier</h1>
                            <p class="text-sm text-white/70 mt-2 max-w-2xl">Atur stok, MOQ grosir, dan tingkatan harga — makin besar kuota grup yang kebentuk, makin murah harganya.</p>
                        </div>
                        <div class="flex gap-3 shrink-0">
                            @if ($availableProducts->count() > 0)
                                <button type="button" @click="openAddProduct = true" class="bg-brass text-white text-sm font-bold py-3.5 px-7 rounded-2xl cursor-pointer">Daftarkan Produk Baru</button>
                            @endif
                            <button type="button" @click="openProposal = true" class="border border-white/30 text-white text-sm font-bold py-3.5 px-6 rounded-2xl cursor-pointer">Usulkan Produk Baru</button>
                        </div>
                    </div>
                </div>

                <main class="px-8 py-8 grid lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 space-y-4">
                        @forelse ($supplierProducts as $sp)
                            <article class="bg-white rounded-3xl border border-line p-6 {{ $sp->is_available ? '' : 'opacity-90' }}">
                                <div class="flex items-start gap-4">
                                    <span class="w-14 h-14 rounded-2xl {{ $sp->is_available ? 'bg-success/10 text-forest' : 'bg-cream border border-line text-muted' }} flex items-center justify-center shrink-0 font-extrabold text-xl">{{ strtoupper(substr($sp->product->name, 0, 1)) }}</span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="font-bold text-ink">{{ $sp->product->name }}</p>
                                                <p class="text-xs text-muted mt-0.5">{{ $sp->product->category->name }}</p>
                                            </div>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $sp->is_available ? 'bg-success/10 text-success' : 'bg-cream border border-line text-muted' }} shrink-0">{{ $sp->is_available ? 'Tersedia' : 'Habis' }}</span>
                                        </div>
                                        <div class="flex flex-wrap gap-x-6 gap-y-1 mt-3 text-xs text-muted">
                                            <span>Stok <strong class="{{ $sp->is_available ? 'text-ink' : 'text-danger' }}">{{ number_format($sp->stock_available, 0) }} {{ $sp->product->unit }}</strong></span>
                                            <span>MOQ <strong class="text-ink">{{ number_format($sp->moq, 0) }} {{ $sp->product->unit }}</strong></span>
                                            <span>Eceran <strong class="text-ink">Rp {{ number_format($sp->base_retail_price, 0, ',', '.') }}</strong></span>
                                        </div>
                                        <div class="flex flex-wrap gap-2 mt-3">
                                            @forelse ($sp->priceTiers as $tier)
                                                <span class="text-xs font-bold bg-brass-soft text-brass border border-brass/20 rounded-full px-3 py-1.5 tabular-nums">
                                                    {{ number_format($tier->min_quantity, 0) }}{{ $tier->max_quantity ? '–'.number_format($tier->max_quantity, 0) : '+' }} {{ $sp->product->unit }}: Rp {{ number_format($tier->unit_price, 0, ',', '.') }}
                                                </span>
                                            @empty
                                                <span class="text-xs text-muted italic">Belum ada tier harga</span>
                                            @endforelse
                                        </div>
                                        <div class="flex gap-6 mt-4">
                                            <button type="button" @click="
                                                selectedProduct = {{ json_encode($sp) }};
                                                tiers = {{ json_encode($sp->priceTiers) }};
                                                if (tiers.length === 0) {
                                                    tiers = [{ min_quantity: 1, max_quantity: 4.99, unit_price: {{ $sp->base_retail_price }} }];
                                                }
                                                tierModal = true;
                                            " class="text-sm font-bold text-brass cursor-pointer">
                                                Kelola Tier
                                            </button>
                                            <button type="button" @click="editProductData = {{ json_encode($sp) }}; openEditProduct = true;" class="text-sm font-bold text-forest cursor-pointer">
                                                Edit Stok/MOQ
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <section class="bg-white rounded-3xl border border-line px-8 py-14 text-center">
                                <span class="w-16 h-16 rounded-2xl bg-success/10 text-forest inline-flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5M12 22V12"/></svg>
                                </span>
                                <h3 class="font-extrabold text-ink text-lg">Belum ada produk di katalogmu.</h3>
                                <p class="text-xs text-muted mt-1.5 max-w-md mx-auto leading-relaxed">Daftarkan produk beserta stok, MOQ, dan tier harga grosir biar mulai menerima pesanan kolektif.</p>
                                @if ($availableProducts->count() > 0)
                                    <button type="button" @click="openAddProduct = true" class="mt-5 bg-brass text-white text-sm font-bold py-3.5 px-7 rounded-2xl cursor-pointer">Daftarkan Produk Baru</button>
                                @endif
                            </section>
                        @endforelse

                        @if ($supplierProducts->hasPages())
                            <div class="pt-2">{{ $supplierProducts->links() }}</div>
                        @endif
                    </div>

                    <aside class="space-y-4">
                        <section class="bg-white rounded-3xl border border-line p-6 sticky top-24">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h2 class="font-extrabold text-ink">Usulan Produk Baru</h2>
                                    <p class="text-xs text-muted mt-1">Produk di luar katalog master — aktif setelah disetujui admin.</p>
                                </div>
                            </div>
                            <button type="button" @click="openProposal = true" class="mt-4 w-full border border-forest/25 text-forest text-sm font-bold py-3 rounded-2xl cursor-pointer">Usulkan Produk</button>

                            @forelse ($proposals as $proposal)
                                @php
                                    $statusBadge = match ($proposal->status->value) {
                                        'PENDING' => 'bg-brass-soft text-brass',
                                        'APPROVED' => 'bg-success/10 text-success',
                                        default => 'bg-danger/10 text-danger',
                                    };
                                @endphp
                                <div class="mt-4 bg-cream/60 rounded-2xl p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="font-bold text-ink text-sm">{{ $proposal->name }}</p>
                                            <p class="text-xs text-muted mt-1">{{ $proposal->category?->name ?? 'Tanpa kategori' }} &bull; per {{ $proposal->unit }}<br>MOQ {{ number_format($proposal->moq, 0) }} &bull; Rp {{ number_format($proposal->base_retail_price, 0, ',', '.') }}</p>
                                            @if ($proposal->status->value === 'REJECTED' && $proposal->admin_note)
                                                <p class="text-xs text-danger mt-1">Catatan admin: {{ $proposal->admin_note }}</p>
                                            @endif
                                            @if ($proposal->status->value === 'APPROVED')
                                                <p class="text-xs text-success font-semibold mt-1">Sudah masuk katalog master — daftarkan lewat tombol di atas.</p>
                                            @endif
                                        </div>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $statusBadge }} shrink-0">{{ $proposal->status->label() }}</span>
                                    </div>
                                    @if ($proposal->status->value === 'PENDING')
                                        <form method="POST" action="{{ route('distributor.products.proposals.destroy', $proposal) }}" id="delete-proposal-desktop-{{ $proposal->id }}" class="mt-2">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                    @click="kulaanConfirm({
                                                        title: 'Tarik Usulan Produk?',
                                                        message: 'Apakah Anda yakin ingin menarik kembali usulan produk ini dari antrean verifikasi admin?',
                                                        confirmText: 'Ya, Tarik Usulan',
                                                        cancelText: 'Kembali',
                                                        isDanger: true,
                                                        onConfirm: () => document.getElementById('delete-proposal-desktop-{{ $proposal->id }}').submit()
                                                    })"
                                                    class="min-h-11 px-3 text-xs font-semibold text-danger hover:underline cursor-pointer">
                                                Tarik
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @empty
                                <p class="text-xs text-muted py-4">Belum ada usulan. Punya barang di luar katalog? Usulkan agar bisa dijual kolektif.</p>
                            @endforelse

                            @if ($proposals->hasPages())
                                <div class="pt-4">{{ $proposals->links() }}</div>
                            @endif
                        </section>
                    </aside>
                </main>
            </div>
        </div>

        <!-- Add Product Modal -->
        <x-modal open="openAddProduct" size="lg"
                 eyebrow="Katalog"
                 title="Daftarkan Produk ke Toko Anda">
            <form method="POST" action="{{ route('distributor.products.store') }}" class="pt-5 space-y-4">
                @csrf
                <div>
                    <label for="product_id" class="field-label">Pilih produk baku</label>
                    <select id="product_id" name="product_id" required class="input-field bg-white">
                        @foreach ($availableProducts as $prod)
                            <option value="{{ $prod->id }}">{{ $prod->name }} ({{ $prod->unit }}) - Patokan Eceran: Rp {{ number_format($prod->benchmark_retail_price, 0) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="stock_available" class="field-label">Kapasitas stok</label>
                        <input id="stock_available" type="number" name="stock_available" value="1000" min="1" required class="input-field">
                    </div>
                    <div>
                        <label for="moq" class="field-label">MOQ grosir (min order)</label>
                        <input id="moq" type="number" name="moq" value="10" min="1" required class="input-field">
                    </div>
                </div>
                <div>
                    <label for="base_retail_price" class="field-label">Harga normal / eceran (Rp)</label>
                    <input id="base_retail_price" type="number" name="base_retail_price" step="100" placeholder="8000" required class="input-field">
                </div>

                <div class="pt-4 border-t border-black/[0.06]">
                    <p class="text-sm font-bold text-ink mb-1">Tingkatan harga grosir kolektif</p>
                    <p class="field-hint mt-0 mb-3">Isi harga satuan per tingkatan. Kosongkan kolom max pada tier terakhir jika tanpa batas.</p>
                    <div class="space-y-3">
                        @foreach ([1, 2, 3] as $tierIndex)
                            <div class="p-3 rounded-xl border border-black/[0.06] bg-cream/50">
                                <p class="text-[0.6875rem] font-bold text-muted uppercase tracking-wide mb-2">Tier {{ $tierIndex }}</p>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                    <div>
                                        <label class="field-label" for="tier_{{ $tierIndex }}_min">Min qty</label>
                                        <input id="tier_{{ $tierIndex }}_min" type="number" name="tiers[{{ $tierIndex - 1 }}][min_quantity]" value="{{ [1, 5, 10][$tierIndex - 1] }}" class="input-field">
                                    </div>
                                    <div>
                                        <label class="field-label" for="tier_{{ $tierIndex }}_max">Max qty</label>
                                        <input id="tier_{{ $tierIndex }}_max" type="number" name="tiers[{{ $tierIndex - 1 }}][max_quantity]" value="{{ [4.99, 9.99, ''][$tierIndex - 1] }}" placeholder="Tanpa batas" class="input-field">
                                    </div>
                                    <div class="col-span-2 sm:col-span-1">
                                        <label class="field-label" for="tier_{{ $tierIndex }}_price">Harga / satuan (Rp)</label>
                                        <input id="tier_{{ $tierIndex }}_price" type="number" name="tiers[{{ $tierIndex - 1 }}][unit_price]" placeholder="{{ [8000, 7500, 6500][$tierIndex - 1] }}" class="input-field">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex flex-col-reverse sm:flex-row-reverse sm:justify-end gap-3 pt-3 border-t border-black/[0.06]">
                    <button type="submit" class="btn-primary text-sm py-3 px-6 w-full sm:w-auto">Simpan Produk</button>
                    <button type="button" @click="openAddProduct = false" class="btn-ghost text-sm py-3 w-full sm:w-auto">Batal</button>
                </div>
            </form>
        </x-modal>

        <!-- Edit Product Modal -->
        <x-modal open="openEditProduct" size="sm"
                 eyebrow="Katalog"
                 title="Edit Stok &amp; MOQ Produk">
            <form :action="`/distributor/products/${editProductData.id}`" method="POST" class="pt-5 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="edit_stock" class="field-label">Kapasitas stok tersedia</label>
                    <input id="edit_stock" type="number" name="stock_available" x-model="editProductData.stock_available" min="0" required class="input-field">
                </div>
                <div>
                    <label for="edit_moq" class="field-label">MOQ grosir (minimum kuantitas)</label>
                    <input id="edit_moq" type="number" name="moq" x-model="editProductData.moq" min="1" required class="input-field">
                </div>
                <div>
                    <label for="edit_price" class="field-label">Harga normal / eceran (Rp)</label>
                    <input id="edit_price" type="number" name="base_retail_price" x-model="editProductData.base_retail_price" step="100" required class="input-field">
                </div>
                <label class="flex items-center gap-2.5 min-h-11 cursor-pointer">
                    <input type="checkbox" name="is_available" id="sp_avail" :checked="editProductData.is_available" value="1" class="w-4 h-4 rounded border-line text-forest">
                    <span class="text-[0.875rem] font-medium text-ink">Tersedia untuk pengadaan kolektif</span>
                </label>
                <div class="flex flex-col-reverse sm:flex-row-reverse sm:justify-end gap-3 pt-3 border-t border-black/[0.06]">
                    <button type="submit" class="btn-primary text-sm py-3 px-6 w-full sm:w-auto">Simpan Perubahan</button>
                    <button type="button" @click="openEditProduct = false" class="btn-ghost text-sm py-3 w-full sm:w-auto">Batal</button>
                </div>
            </form>
        </x-modal>

        <!-- Manage Tiers Modal -->
        <x-modal open="tierModal" size="lg"
                 eyebrow="Harga Grosir"
                 title="Kelola Tingkatan Harga Grosir">
            <p class="text-xs text-muted mt-4 font-semibold" x-text="selectedProduct ? selectedProduct.product?.name : ''"></p>

            <form :action="`/distributor/products/${selectedProduct?.id}/tiers`" method="POST" class="pt-4 space-y-4">
                @csrf
                @method('PUT')

                <div class="space-y-3">
                    <template x-for="(tier, index) in tiers" :key="index">
                        <div class="p-3 rounded-xl border border-black/[0.06] bg-cream/50">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[0.6875rem] font-bold text-muted uppercase tracking-wide" x-text="`Tier ${index + 1}`"></span>
                                <button type="button" @click="tiers.splice(index, 1)" class="btn-icon min-w-11 min-h-11 text-danger hover:bg-danger/10" :aria-label="`Hapus tier ${index + 1}`">&times;</button>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                <div>
                                    <label class="field-label">Min qty</label>
                                    <input type="number" :name="`tiers[${index}][min_quantity]`" x-model="tier.min_quantity" required placeholder="Min" class="input-field">
                                </div>
                                <div>
                                    <label class="field-label">Max qty</label>
                                    <input type="number" :name="`tiers[${index}][max_quantity]`" x-model="tier.max_quantity" placeholder="Kosongkan = tanpa batas" class="input-field">
                                </div>
                                <div class="col-span-2 sm:col-span-1">
                                    <label class="field-label">Harga / satuan (Rp)</label>
                                    <input type="number" :name="`tiers[${index}][unit_price]`" x-model="tier.unit_price" required placeholder="Harga" class="input-field">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <button type="button" @click="tiers.push({ min_quantity: '', max_quantity: '', unit_price: '' })" class="min-h-11 px-2 text-sm font-semibold text-forest hover:underline cursor-pointer">
                    + Tambah Baris Tier
                </button>

            <div class="flex flex-col-reverse sm:flex-row-reverse sm:justify-end gap-3 pt-3 border-t border-black/[0.06]">
                <button type="submit" class="btn-primary text-sm py-3 px-6 w-full sm:w-auto">Simpan Tier</button>
                <button type="button" @click="tierModal = false" class="btn-ghost text-sm py-3 w-full sm:w-auto">Batal</button>
            </div>
        </form>
    </x-modal>

        <!-- Propose Custom Product Modal -->
        <x-modal open="openProposal" size="lg"
                 eyebrow="Usulan Produk"
                 title="Usulkan Produk Baru ke Katalog">
            <form method="POST" action="{{ route('distributor.products.proposals.store') }}" class="pt-5 space-y-4" data-loading>
                @csrf
                <div>
                    <label for="proposal_category" class="field-label">Kategori terdekat</label>
                    <select id="proposal_category" name="category_id" required class="input-field bg-white">
                        <option value="">Pilih kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="proposal_name" class="field-label">Nama produk</label>
                    <input id="proposal_name" type="text" name="name" placeholder="Contoh: Gula Aren Asli Sidoarjo" required class="input-field">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="proposal_unit" class="field-label">Satuan</label>
                        <input id="proposal_unit" type="text" name="unit" placeholder="kg / liter / pcs" required class="input-field">
                    </div>
                    <div>
                        <label for="proposal_price" class="field-label">Harga eceran (Rp)</label>
                        <input id="proposal_price" type="number" name="base_retail_price" step="100" placeholder="15000" required class="input-field">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="proposal_stock" class="field-label">Stok tersedia</label>
                        <input id="proposal_stock" type="number" name="stock_available" value="100" min="1" required class="input-field">
                    </div>
                    <div>
                        <label for="proposal_moq" class="field-label">MOQ grosir</label>
                        <input id="proposal_moq" type="number" name="moq" value="10" min="1" required class="input-field">
                    </div>
                </div>
                <div>
                    <label for="proposal_spec" class="field-label">Spesifikasi (opsional)</label>
                    <textarea id="proposal_spec" name="standard_specification" rows="2" placeholder="Ciri khas, kemasan, mutu…" class="input-field"></textarea>
                </div>
                <p class="field-hint">Usulan masuk antrean review admin. Setelah disetujui, produk tampil di katalog master dan tier harga bisa diatur.</p>
                <div class="flex flex-col-reverse sm:flex-row-reverse sm:justify-end gap-3 pt-3 border-t border-black/[0.06]">
                    <button type="submit" class="btn-primary text-sm py-3 px-6 w-full sm:w-auto">Kirim Usulan</button>
                    <button type="button" @click="openProposal = false" class="btn-ghost text-sm py-3 w-full sm:w-auto">Batal</button>
                </div>
            </form>
        </x-modal>
    </div>
</x-layouts.app>
