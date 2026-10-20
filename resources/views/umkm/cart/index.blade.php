<x-layouts.app>
    <x-slot name="title">Keranjang Belanja</x-slot>

    @php
        $displayName = $umkm->business_name ?: 'UMKM';
        $initials = strtoupper(substr($displayName, 0, 2));
        $itemCount = count($items);
    @endphp

    {{-- MOBILE --}}
    <div class="lg:hidden pb-24">
        {{-- greeting header --}}
        <div class="px-4 pt-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="relative" x-data="{ menuOpen: false }">
                    <button type="button" @click="menuOpen = !menuOpen" class="w-11 h-11 rounded-2xl bg-forest text-white flex items-center justify-center font-extrabold text-sm cursor-pointer">{{ $initials }}</button>
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
                    <p class="text-[15px] font-extrabold leading-tight">{{ $displayName }}</p>
                </div>
            </div>
            <x-notification-bell />
        </div>

        {{-- page header --}}
        <div class="px-4 mt-5 flex items-center gap-3">
            <a href="{{ route('umkm.catalog.index') }}" class="w-10 h-10 rounded-2xl bg-white border border-line flex items-center justify-center text-ink">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
            </a>
            <h1 class="text-lg font-extrabold">Keranjang</h1>
            <span class="bg-forest text-white text-[11px] font-bold px-2.5 py-1 rounded-full"><span id="summary-count">{{ $itemCount }}</span> item</span>
        </div>

        @if (! empty($items))
            {{-- kulaan progress banner --}}
            <div class="px-4 mt-4">
                <div class="bg-brass-soft border border-brass/25 rounded-3xl p-4">
                    <div class="flex items-start gap-2.5">
                        <span class="w-9 h-9 rounded-2xl bg-brass text-white flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.25 7.5l-.63 10.63a2.25 2.25 0 0 1-2.25 2.12H6.63a2.25 2.25 0 0 1-2.25-2.12L3.75 7.5M10 11.25h4M3.38 7.5h17.25c.62 0 1.12-.5 1.12-1.12v-1.5C21.75 4.26 21.25 3.75 20.63 3.75H3.38c-.62 0-1.13.5-1.13 1.12v1.5c0 .63.5 1.13 1.12 1.13Z"/></svg>
                        </span>
                        <div class="flex-1">
                            <p class="text-[13px] font-bold text-ink leading-snug {{ $moqProgress['target'] > 0 && $moqProgress['remaining'] <= 0 ? 'hidden' : '' }}" data-moq-pending>Kurang <span class="text-brass tnum" data-moq-remaining>{{ number_format($moqProgress['remaining'], 0, ',', '.') }} {{ $moqProgress['unit'] }}</span> lagi menuju minimum pemesanan &mdash; harga grosir menanti</p>
                            <p class="text-[13px] font-bold text-success leading-snug {{ $moqProgress['target'] > 0 && $moqProgress['remaining'] <= 0 ? '' : 'hidden' }}" data-moq-met>Kuota minimum terpenuhi &mdash; harga grosir aktif</p>
                            <div class="h-2 rounded-full bg-white/70 border border-brass/20 overflow-hidden mt-2.5">
                                <div class="h-full rounded-full bg-gradient-to-r from-brass to-forest" style="width: {{ $moqProgress['percent'] }}%" data-moq-bar></div>
                            </div>
                            <p class="text-[11px] text-muted mt-1.5">Terkumpul <span data-moq-gathered>{{ number_format($moqProgress['gathered'], 0, ',', '.') }}</span>/<span data-moq-target>{{ number_format($moqProgress['target'], 0, ',', '.') }}</span> <span data-moq-unit>{{ $moqProgress['unit'] }}</span> di kelompokmu</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- items --}}
            <div class="px-4 mt-4 space-y-3" id="cartItemsList">
                @foreach ($items as $item)
                    @php
                        $prod = $item['product'];
                        $dist = $item['distributor'];
                    @endphp
                    <div class="bg-white rounded-3xl border border-line p-3.5 shadow-sm"
                         id="cart-card-{{ $item['key'] }}"
                         data-item-key="{{ $item['key'] }}">
                        <div class="flex gap-3">
                            <div class="w-20 h-20 rounded-2xl {{ $loop->odd ? 'bg-forest/10' : 'bg-brass-soft' }} flex items-center justify-center shrink-0">
                                <span class="text-3xl font-extrabold {{ $loop->odd ? 'text-forest/30' : 'text-brass/30' }}">{{ strtoupper(substr($prod->name, 0, 1)) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-extrabold leading-tight">
                                    <a href="{{ route('umkm.catalog.show', $prod) }}" class="hover:text-forest transition-colors">{{ $prod->name }}</a>
                                </p>
                                <p class="text-[11px] text-muted mt-0.5">
                                    {{ $dist->company_name ?? '-' }}
                                    @if ($dist && $dist->rating)
                                        <span class="text-brass font-bold">&middot; &#9733; {{ number_format((float) $dist->rating, 1) }}</span>
                                    @endif
                                </p>
                                <span class="inline-block mt-1.5 bg-forest/10 text-forest text-[10px] font-bold px-2 py-0.5 rounded-full"
                                      id="tier-badge-{{ $item['key'] }}">{{ $item['tier_label'] }}</span>
                                @if (! $item['is_available'])
                                    <span class="inline-block mt-1.5 bg-danger/10 text-danger text-[10px] font-bold px-2 py-0.5 rounded-full">Distributor Tidak Aktif</span>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('umkm.cart.remove') }}" id="remove-cart-m-{{ $item['key'] }}" class="shrink-0">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $item['product_id'] }}">
                                <input type="hidden" name="distributor_id" value="{{ $item['distributor_id'] }}">
                                <button type="button"
                                        @click="kulaanConfirm({
                                            title: 'Hapus dari Keranjang?',
                                            message: 'Apakah Anda yakin ingin menghapus {{ addslashes($prod->name) }} dari keranjang belanja?',
                                            confirmText: 'Ya, Hapus',
                                            cancelText: 'Batal',
                                            isDanger: true,
                                            onConfirm: () => document.getElementById('remove-cart-m-{{ $item['key'] }}').submit()
                                        })"
                                        class="text-muted hover:text-danger self-start cursor-pointer">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m14.74 9-.35 9m-4.79 0L9.26 9m9.97-3.21c.34.05.68.11 1.02.17m-1.02-.17L18.16 19.67a2.25 2.25 0 0 1-2.24 2.08H8.08a2.25 2.25 0 0 1-2.24-2.08L4.77 5.79m14.46 0a48.11 48.11 0 0 0-3.48-.4m-12 .56c.34-.06.68-.11 1.02-.17m0 0a48.11 48.11 0 0 1 3.48-.4m7.5 0v-.92c0-1.18-.91-2.16-2.09-2.2a51.96 51.96 0 0 0-3.32 0c-1.18.04-2.09 1.02-2.09 2.2v.92m7.5 0a48.67 48.67 0 0 0-7.5 0"/></svg>
                                </button>
                            </form>
                        </div>
                        <div class="flex items-center justify-between mt-3">
                            <div class="flex items-center gap-2.5 bg-cream border border-line rounded-xl p-1">
                                <button type="button"
                                        onclick="changeCartQty('{{ $item['product_id'] }}', '{{ $item['distributor_id'] }}', '{{ $item['key'] }}', -1)"
                                        class="w-8 h-8 rounded-lg bg-white border border-line flex items-center justify-center cursor-pointer">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14"/></svg>
                                </button>
                                <span class="text-sm font-extrabold w-6 text-center tabular-nums"
                                      id="qty-label-{{ $item['key'] }}">{{ $item['quantity'] == floor($item['quantity']) ? number_format($item['quantity'], 0) : number_format($item['quantity'], 1) }}</span>
                                <button type="button"
                                        onclick="changeCartQty('{{ $item['product_id'] }}', '{{ $item['distributor_id'] }}', '{{ $item['key'] }}', 1)"
                                        class="w-8 h-8 rounded-lg bg-forest text-white flex items-center justify-center cursor-pointer">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                </button>
                            </div>
                            <div class="text-right">
                                <p class="text-[11px] text-muted line-through tabular-nums">Rp <span id="retail-total-{{ $item['key'] }}">{{ number_format($item['retail_total'], 0, ',', '.') }}</span></p>
                                <p class="text-base font-extrabold tabular-nums">Rp <span id="subtotal-{{ $item['key'] }}">{{ number_format($item['subtotal'], 0, ',', '.') }}</span></p>
                                <span class="inline-block bg-success/10 text-success text-[10px] font-extrabold px-2 py-0.5 rounded-full mt-0.5 {{ $item['savings'] > 0 ? '' : 'hidden' }}"
                                      id="savings-badge-{{ $item['key'] }}">HEMAT Rp <span id="savings-amount-{{ $item['key'] }}">{{ number_format($item['savings'], 0, ',', '.') }}</span></span>
                            </div>
                        </div>
                    </div>
                @endforeach

                <a href="{{ route('umkm.catalog.index') }}" class="block w-full text-center py-3 rounded-2xl border-2 border-dashed border-line text-sm font-bold text-muted hover:border-forest/40 hover:text-forest transition">+ Tambah produk lain</a>
            </div>

            {{-- summary --}}
            <div class="px-4 mt-4">
                <div class="bg-white rounded-3xl border border-line p-5 shadow-sm">
                    <h3 class="font-extrabold">Ringkasan Belanja</h3>
                    <div class="mt-3 space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-muted">Subtotal <span class="text-[11px]">(estimasi)</span></span><span class="font-bold tabular-nums">Rp {{ number_format($total, 0, ',', '.') }}</span></div>
                        <div class="flex justify-between"><span class="text-success font-semibold">Estimasi hemat vs eceran</span><span class="font-extrabold text-success tabular-nums">&minus;Rp <span id="summary-savings">{{ number_format($totalSavings, 0, ',', '.') }}</span></span></div>
                        <div class="border-t border-line pt-2.5 flex justify-between items-center">
                            <span class="font-bold">Total bayar <span class="text-[11px] text-muted font-medium">(estimasi)</span></span>
                            <span class="text-xl font-extrabold tabular-nums">Rp <span id="summary-total">{{ number_format($total, 0, ',', '.') }}</span></span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('umkm.cart.checkout') }}" class="mt-4">
                        @csrf
                        <button type="submit" class="w-full py-3.5 rounded-2xl bg-forest text-white font-bold shadow-md cursor-pointer">Checkout</button>
                    </form>
                    <div class="flex items-center justify-center gap-1.5 mt-3 text-[11px] text-muted">
                        <svg class="w-4 h-4 text-success" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 12.75 11.25 15 15 9.75m-3-7.04A11.96 11.96 0 0 1 3.6 6 12 12 0 0 0 3 9.75c0 5.59 3.82 10.29 9 11.62 5.18-1.33 9-6.03 9-11.62 0-1.31-.21-2.57-.6-3.75h-.15c-3.2 0-6.1-1.25-8.25-3.29Z"/></svg>
                        Pembayaran aman via Midtrans
                    </div>
                    <p class="text-[11px] text-muted text-center mt-2 leading-relaxed">Harga final mengikuti total quantity<br>kelompok kulaan &mdash; bisa lebih murah lagi.</p>
                </div>
            </div>
        @else
            {{-- empty state --}}
            <div class="px-4 mt-5">
                <section class="bg-white rounded-3xl border border-line p-10 text-center shadow-sm">
                    <div class="w-14 h-14 rounded-2xl bg-forest/10 text-forest flex items-center justify-center mx-auto font-extrabold text-2xl">K</div>
                    <h2 class="font-extrabold text-ink text-lg mt-4">Keranjang Belanja Masih Kosong</h2>
                    <p class="text-xs text-muted mt-2 leading-relaxed">
                        Anda belum menambahkan bahan baku ke keranjang. Jelajahi katalog distributor untuk mulai mengumpulkan kuota kulaan bersama UMKM lain.
                    </p>
                    <a href="{{ route('umkm.catalog.index') }}" class="bg-forest text-white inline-flex text-sm font-bold px-5 py-3 rounded-2xl mt-5">Lihat Katalog Bahan Baku &rarr;</a>
                </section>
            </div>
        @endif
    </div>

    {{-- DESKTOP --}}
    <div class="hidden lg:flex min-h-screen">
        <aside class="w-64 shrink-0 bg-white border-r border-line flex flex-col sticky top-0 h-screen">
            <div class="flex items-center gap-2.5 px-6 h-[72px] border-b border-line">
                <span class="w-10 h-10 rounded-2xl bg-forest flex items-center justify-center text-white">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.25 7.5l-.63 10.63a2.25 2.25 0 0 1-2.25 2.12H6.63a2.25 2.25 0 0 1-2.25-2.12L3.75 7.5M10 11.25h4M3.38 7.5h17.25c.62 0 1.12-.5 1.12-1.12v-1.5C21.75 4.26 21.25 3.75 20.63 3.75H3.38c-.62 0-1.13.5-1.13 1.12v1.5c0 .63.5 1.13 1.12 1.13Z"/></svg>
                </span>
                <span class="text-xl font-extrabold tracking-tight">Kulaan</span>
            </div>
            <x-umkm.sidebar-nav active="cart" />
            <div class="p-4 border-t border-line">
                <a href="{{ route('profile.complete') }}" class="flex items-center gap-3 px-2">
                    <span class="w-10 h-10 rounded-2xl bg-forest text-white flex items-center justify-center font-extrabold text-xs">{{ $initials }}</span>
                    <div class="min-w-0">
                        <p class="text-sm font-bold truncate">{{ $displayName }}</p>
                        <p class="text-[11px] text-muted">{{ $umkm->city ?? 'Sidoarjo' }}, {{ $umkm->province ?? 'Jawa Timur' }}</p>
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
                        <h1 class="text-lg font-extrabold leading-tight">Selamat pagi, {{ $displayName }}</h1>
                        <p class="text-xs text-muted">{{ now()->translatedFormat('l, d F Y') }}</p>
                    </div>
                    <div class="ml-auto flex items-center gap-3">
                        <form method="GET" action="{{ route('umkm.catalog.index') }}" class="hidden lg:flex items-center gap-2 bg-white border border-line rounded-2xl pl-4 pr-2 py-2 w-72">
                            <svg class="w-4 h-4 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21 21-5.2-5.2m0 0A7.5 7.5 0 1 0 5.2 5.2a7.5 7.5 0 0 0 10.6 10.6Z"/></svg>
                            <input name="q" class="flex-1 bg-transparent text-sm outline-none placeholder:text-muted/70" placeholder="Cari pesanan, produk...">
                        </form>
                        <x-notification-bell />
                    </div>
                </div>
            </header>

            <main class="px-8 py-6 max-w-6xl">
                <div class="mt-2 flex items-center gap-3">
                    <h1 class="text-2xl font-extrabold tracking-tight">Keranjang Belanja</h1>
                    <span class="bg-forest text-white text-xs font-bold px-3 py-1 rounded-full"><span id="summary-count">{{ $itemCount }}</span> item</span>
                </div>

                @if (! empty($items))
                    {{-- kulaan progress banner --}}
                    <div class="mt-5 bg-brass-soft border border-brass/25 rounded-3xl p-5 flex items-center gap-4">
                        <span class="w-12 h-12 rounded-2xl bg-brass text-white flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.25 7.5l-.63 10.63a2.25 2.25 0 0 1-2.25 2.12H6.63a2.25 2.25 0 0 1-2.25-2.12L3.75 7.5M10 11.25h4M3.38 7.5h17.25c.62 0 1.12-.5 1.12-1.12v-1.5C21.75 4.26 21.25 3.75 20.63 3.75H3.38c-.62 0-1.13.5-1.13 1.12v1.5c0 .63.5 1.13 1.12 1.13Z"/></svg>
                        </span>
                        <div class="flex-1">
                            <p class="font-bold {{ $moqProgress['target'] > 0 && $moqProgress['remaining'] <= 0 ? 'hidden' : '' }}" data-moq-pending>Kurang <span class="text-brass tnum" data-moq-remaining>{{ number_format($moqProgress['remaining'], 0, ',', '.') }} {{ $moqProgress['unit'] }}</span> lagi menuju minimum pemesanan &mdash; harga grosir menanti</p>
                            <p class="font-bold text-success {{ $moqProgress['target'] > 0 && $moqProgress['remaining'] <= 0 ? '' : 'hidden' }}" data-moq-met>Kuota minimum terpenuhi &mdash; harga grosir aktif</p>
                            <div class="h-2.5 rounded-full bg-white/70 border border-brass/20 overflow-hidden mt-2 max-w-xl">
                                <div class="h-full rounded-full bg-gradient-to-r from-brass to-forest" style="width: {{ $moqProgress['percent'] }}%" data-moq-bar></div>
                            </div>
                        </div>
                        <p class="text-sm text-muted whitespace-nowrap">Terkumpul <span class="font-bold text-ink"><span data-moq-gathered>{{ number_format($moqProgress['gathered'], 0, ',', '.') }}</span>/<span data-moq-target>{{ number_format($moqProgress['target'], 0, ',', '.') }}</span> <span data-moq-unit>{{ $moqProgress['unit'] }}</span></span></p>
                    </div>

                    <div class="grid lg:grid-cols-[1fr_380px] gap-6 mt-6 items-start">
                        {{-- items --}}
                        <div class="space-y-4" id="cartItemsList">
                            @foreach ($items as $item)
                                @php
                                    $prod = $item['product'];
                                    $dist = $item['distributor'];
                                @endphp
                                <div class="bg-white rounded-3xl border border-line p-5 shadow-sm flex gap-5"
                                     id="cart-card-{{ $item['key'] }}"
                                     data-item-key="{{ $item['key'] }}">
                                    <div class="w-28 h-28 rounded-2xl {{ $loop->odd ? 'bg-forest/10' : 'bg-brass-soft' }} flex items-center justify-center shrink-0">
                                        <span class="text-5xl font-extrabold {{ $loop->odd ? 'text-forest/30' : 'text-brass/30' }}">{{ strtoupper(substr($prod->name, 0, 1)) }}</span>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="font-extrabold text-lg">
                                                    <a href="{{ route('umkm.catalog.show', $prod) }}" class="hover:text-forest transition-colors">{{ $prod->name }}</a>
                                                </p>
                                                <p class="text-xs text-muted mt-0.5">
                                                    {{ $dist->company_name ?? '-' }}
                                                    @if ($dist && $dist->rating)
                                                        <span class="text-brass font-bold">&middot; &#9733; {{ number_format((float) $dist->rating, 1) }}</span>
                                                    @endif
                                                </p>
                                                <span class="inline-block mt-2 bg-forest/10 text-forest text-[11px] font-bold px-2.5 py-1 rounded-full"
                                                      id="tier-badge-{{ $item['key'] }}">{{ $item['tier_label'] }}</span>
                                                @if (! $item['is_available'])
                                                    <span class="inline-block mt-2 bg-danger/10 text-danger text-[11px] font-bold px-2.5 py-1 rounded-full">Distributor Tidak Aktif</span>
                                                @endif
                                            </div>
                                            <form method="POST" action="{{ route('umkm.cart.remove') }}" id="remove-cart-d-{{ $item['key'] }}" class="shrink-0">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $item['product_id'] }}">
                                                <input type="hidden" name="distributor_id" value="{{ $item['distributor_id'] }}">
                                                <button type="button"
                                                        @click="kulaanConfirm({
                                                            title: 'Hapus dari Keranjang?',
                                                            message: 'Apakah Anda yakin ingin menghapus {{ addslashes($prod->name) }} dari keranjang belanja?',
                                                            confirmText: 'Ya, Hapus',
                                                            cancelText: 'Batal',
                                                            isDanger: true,
                                                            onConfirm: () => document.getElementById('remove-cart-d-{{ $item['key'] }}').submit()
                                                        })"
                                                        class="text-xs font-bold text-muted hover:text-danger flex items-center gap-1 cursor-pointer">
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m14.74 9-.35 9m-4.79 0L9.26 9m9.97-3.21c.34.05.68.11 1.02.17m-1.02-.17L18.16 19.67a2.25 2.25 0 0 1-2.24 2.08H8.08a2.25 2.25 0 0 1-2.24-2.08L4.77 5.79m14.46 0a48.11 48.11 0 0 0-3.48-.4m-12 .56c.34-.06.68-.11 1.02-.17m0 0a48.11 48.11 0 0 1 3.48-.4m7.5 0v-.92c0-1.18-.91-2.16-2.09-2.2a51.96 51.96 0 0 0-3.32 0c-1.18.04-2.09 1.02-2.09 2.2v.92m7.5 0a48.67 48.67 0 0 0-7.5 0"/></svg>
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                        <div class="flex items-center justify-between mt-4">
                                            <div class="flex items-center gap-3 bg-cream border border-line rounded-xl p-1">
                                                <button type="button"
                                                        onclick="changeCartQty('{{ $item['product_id'] }}', '{{ $item['distributor_id'] }}', '{{ $item['key'] }}', -1)"
                                                        class="w-9 h-9 rounded-lg bg-white border border-line flex items-center justify-center cursor-pointer">
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14"/></svg>
                                                </button>
                                                <span class="font-extrabold w-8 text-center tabular-nums"
                                                      id="qty-label-{{ $item['key'] }}">{{ $item['quantity'] == floor($item['quantity']) ? number_format($item['quantity'], 0) : number_format($item['quantity'], 1) }}</span>
                                                <button type="button"
                                                        onclick="changeCartQty('{{ $item['product_id'] }}', '{{ $item['distributor_id'] }}', '{{ $item['key'] }}', 1)"
                                                        class="w-9 h-9 rounded-lg bg-forest text-white flex items-center justify-center cursor-pointer">
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                                </button>
                                                <span class="text-xs text-muted pr-2">{{ $prod->unit }}</span>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-xs text-muted line-through tabular-nums">Rp <span id="retail-total-{{ $item['key'] }}">{{ number_format($item['retail_total'], 0, ',', '.') }}</span></p>
                                                <p class="text-xl font-extrabold tabular-nums">Rp <span id="subtotal-{{ $item['key'] }}">{{ number_format($item['subtotal'], 0, ',', '.') }}</span></p>
                                                <span class="inline-block bg-success/10 text-success text-[11px] font-extrabold px-2.5 py-1 rounded-full mt-1 {{ $item['savings'] > 0 ? '' : 'hidden' }}"
                                                      id="savings-badge-{{ $item['key'] }}">HEMAT Rp <span id="savings-amount-{{ $item['key'] }}">{{ number_format($item['savings'], 0, ',', '.') }}</span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <a href="{{ route('umkm.catalog.index') }}" class="block w-full text-center py-4 rounded-3xl border-2 border-dashed border-line text-sm font-bold text-muted hover:border-forest/40 hover:text-forest transition">+ Tambah produk lain dari katalog</a>
                        </div>

                        {{-- summary --}}
                        <aside class="bg-white rounded-3xl border border-line p-6 shadow-sm lg:sticky lg:top-24">
                            <h3 class="font-extrabold text-lg">Ringkasan Belanja</h3>
                            <div class="mt-4 space-y-2.5 text-sm">
                                <div class="flex justify-between"><span class="text-muted">Subtotal <span class="text-[11px]">(estimasi)</span></span><span class="font-bold tabular-nums">Rp {{ number_format($total, 0, ',', '.') }}</span></div>
                                <div class="flex justify-between"><span class="text-success font-semibold">Estimasi hemat vs eceran</span><span class="font-extrabold text-success tabular-nums">&minus;Rp <span id="summary-savings">{{ number_format($totalSavings, 0, ',', '.') }}</span></span></div>
                                <div class="flex justify-between"><span class="text-muted">Ongkir</span><span class="font-bold text-success">Dihitung saat checkout</span></div>
                                <div class="border-t border-line pt-3 flex justify-between items-center">
                                    <span class="font-bold">Total bayar <span class="text-[11px] text-muted font-medium">(estimasi)</span></span>
                                    <span class="text-2xl font-extrabold tabular-nums">Rp <span id="summary-total">{{ number_format($total, 0, ',', '.') }}</span></span>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('umkm.cart.checkout') }}" class="mt-5">
                                @csrf
                                <button type="submit" class="w-full py-4 rounded-2xl bg-forest text-white font-bold shadow-md hover:bg-forest-dark transition cursor-pointer">Checkout</button>
                            </form>
                            <div class="flex items-center justify-center gap-1.5 mt-4 text-xs text-muted">
                                <svg class="w-4 h-4 text-success" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 12.75 11.25 15 15 9.75m-3-7.04A11.96 11.96 0 0 1 3.6 6 12 12 0 0 0 3 9.75c0 5.59 3.82 10.29 9 11.62 5.18-1.33 9-6.03 9-11.62 0-1.31-.21-2.57-.6-3.75h-.15c-3.2 0-6.1-1.25-8.25-3.29Z"/></svg>
                                Pembayaran aman via Midtrans &middot; 4.000+ ulasan terverifikasi
                            </div>
                            <p class="text-[11px] text-muted text-center mt-3 leading-relaxed">Harga final mengikuti total quantity kelompok kulaan &mdash; bisa lebih murah lagi dari estimasi ini.</p>
                        </aside>
                    </div>
                @else
                    {{-- empty state --}}
                    <section class="bg-white rounded-3xl border border-line p-10 sm:p-16 text-center max-w-lg mx-auto shadow-sm mt-6">
                        <div class="w-14 h-14 rounded-2xl bg-forest/10 text-forest flex items-center justify-center mx-auto font-extrabold text-2xl">K</div>
                        <h2 class="font-extrabold text-ink text-lg sm:text-xl mt-4">Keranjang Belanja Masih Kosong</h2>
                        <p class="text-xs sm:text-sm text-muted mt-2 leading-relaxed">
                            Anda belum menambahkan bahan baku ke keranjang. Jelajahi katalog distributor untuk mulai mengumpulkan kuota kulaan bersama UMKM lain.
                        </p>
                        <a href="{{ route('umkm.catalog.index') }}" class="bg-forest text-white inline-flex text-sm font-bold px-5 py-3 rounded-2xl mt-5">Lihat Katalog Bahan Baku &rarr;</a>
                    </section>
                @endif
            </main>

            <footer class="border-t border-line mt-4">
                <div class="px-8 py-8 flex items-center justify-between">
                    <p class="text-sm font-extrabold">Kulaan <span class="text-muted font-medium">&mdash; Kulakan kolektif UMKM Indonesia</span></p>
                    <p class="text-xs text-muted">GTNIC 2026 &middot; Web Development</p>
                </div>
            </footer>
        </div>
    </div>

    {{-- bottom tab --}}
    <x-umkm.mobile-tab active="cart" />

    <script>
        const cartCurrentQtys = {
            @foreach ($items as $item)
                '{{ $item['key'] }}': {{ (float) $item['quantity'] }},
            @endforeach
        };

        function formatRupiah(num) {
            return Math.round(num || 0).toLocaleString('id-ID');
        }

        function applyMoqProgress(progress) {
            if (!progress) {
                return;
            }

            const unit = progress.unit || '';
            const met = progress.target > 0 && progress.remaining <= 0;

            document.querySelectorAll('[data-moq-remaining]').forEach((el) => {
                el.textContent = `${formatRupiah(progress.remaining)} ${unit}`.trim();
            });
            document.querySelectorAll('[data-moq-gathered]').forEach((el) => {
                el.textContent = formatRupiah(progress.gathered);
            });
            document.querySelectorAll('[data-moq-target]').forEach((el) => {
                el.textContent = formatRupiah(progress.target);
            });
            document.querySelectorAll('[data-moq-unit]').forEach((el) => {
                el.textContent = unit;
            });
            document.querySelectorAll('[data-moq-bar]').forEach((el) => {
                el.style.width = `${progress.percent}%`;
            });
            document.querySelectorAll('[data-moq-pending]').forEach((el) => {
                el.classList.toggle('hidden', met);
            });
            document.querySelectorAll('[data-moq-met]').forEach((el) => {
                el.classList.toggle('hidden', !met);
            });
        }

        async function changeCartQty(productId, distributorId, key, delta) {
            const current = cartCurrentQtys[key] || 1;
            const nextQty = Math.max(0, Math.round(current + delta));

            if (nextQty <= 0) {
                if (window.kulaanConfirm) {
                    window.kulaanConfirm({
                        title: 'Hapus dari Keranjang?',
                        message: 'Jumlah menjadi 0. Apakah Anda ingin menghapus item ini dari keranjang belanja?',
                        confirmText: 'Ya, Hapus',
                        cancelText: 'Batal',
                        isDanger: true,
                        onConfirm: () => performCartQtyChange(productId, distributorId, key, nextQty)
                    });
                    return;
                }
            }

            await performCartQtyChange(productId, distributorId, key, nextQty);
        }

        async function performCartQtyChange(productId, distributorId, key, nextQty) {

            try {
                const response = await fetch("{{ route('umkm.cart.update') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        distributor_id: distributorId,
                        quantity: nextQty
                    })
                });

                const data = await response.json();
                if (!data.success) {
                    alert('Gagal memperbarui kuantitas');
                    return;
                }

                if (data.removed) {
                    const card = document.getElementById(`cart-card-${key}`);
                    if (card) card.remove();
                    delete cartCurrentQtys[key];
                    if (data.count === 0) {
                        location.reload();
                        return;
                    }
                } else {
                    cartCurrentQtys[key] = nextQty;
                    const qtyLabel = document.getElementById(`qty-label-${key}`);
                    if (qtyLabel) qtyLabel.textContent = nextQty;

                    const unitPriceSpan = document.getElementById(`unit-price-${key}`);
                    if (unitPriceSpan) unitPriceSpan.textContent = formatRupiah(data.item.unit_price);

                    const subtotalSpan = document.getElementById(`subtotal-${key}`);
                    if (subtotalSpan) subtotalSpan.textContent = formatRupiah(data.item.subtotal);

                    const retailTotalSpan = document.getElementById(`retail-total-${key}`);
                    if (retailTotalSpan) retailTotalSpan.textContent = formatRupiah(data.item.retail_total);

                    const tierBadge = document.getElementById(`tier-badge-${key}`);
                    if (tierBadge && data.item.tier_label) tierBadge.textContent = data.item.tier_label;

                    const savingsBadge = document.getElementById(`savings-badge-${key}`);
                    const savingsAmountSpan = document.getElementById(`savings-amount-${key}`);
                    if (savingsBadge && savingsAmountSpan) {
                        if (data.item.savings > 0) {
                            savingsBadge.classList.remove('hidden');
                            savingsAmountSpan.textContent = formatRupiah(data.item.savings);
                        } else {
                            savingsBadge.classList.add('hidden');
                        }
                    }
                }

                const summaryCount = document.getElementById('summary-count');
                if (summaryCount) summaryCount.textContent = data.count;

                const summaryTotal = document.getElementById('summary-total');
                if (summaryTotal) summaryTotal.textContent = formatRupiah(data.total);

                const summarySavings = document.getElementById('summary-savings');
                if (summarySavings) summarySavings.textContent = formatRupiah(data.total_savings);

                applyMoqProgress(data.moq_progress);

            } catch (err) {
                console.error(err);
            }
        }
    </script>
</x-layouts.app>
