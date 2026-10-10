<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? "Dashboard" }} - Kula'an</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-[100dvh] flex flex-col font-sans" style="background-color: var(--color-brand-cream);">
    <!-- Fluid Floating Island Navigation -->
    <header class="sticky top-0 z-30 pt-3 px-3 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white/90 backdrop-blur-xl border border-black/[0.06] shadow-[0_2px_16px_-4px_rgba(0,0,0,0.06)] rounded-2xl min-h-16 px-4 sm:px-6 py-2 sm:py-0 flex flex-wrap sm:flex-nowrap items-center justify-between gap-2 transition-all duration-500 ease-[cubic-bezier(0.32,0.72,0,1)]">
                <div class="flex items-center gap-4 sm:gap-8">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group">
                        <span class="w-9 h-9 rounded-xl bg-brand-primary text-white flex items-center justify-center font-bold text-base shadow-[0_2px_8px_-1px_rgba(45,106,79,0.3)] transition-all duration-500 ease-[cubic-bezier(0.32,0.72,0,1)] group-hover:scale-105">K</span>
                        <span class="text-lg font-bold text-brand-dark tracking-tight">Kula'an</span>
                    </a>

                    <!-- Desktop Nav Links -->
                    <nav class="hidden md:flex items-center gap-1.5 text-[0.8125rem] font-medium">
                        <a href="{{ route('dashboard') }}" 
                           class="px-3 py-1.5 rounded-full transition-all duration-300 {{ request()->routeIs('dashboard') ? 'bg-brand-mint text-brand-primary font-bold' : 'text-brand-slate hover:text-brand-dark hover:bg-black/[0.02]' }}">
                           Dashboard
                        </a>
                        
                        @hasrole('umkm')
                            <a href="{{ route('umkm.requests.index') }}" 
                               class="px-3 py-1.5 rounded-full transition-all duration-300 {{ request()->routeIs('umkm.requests*') ? 'bg-brand-mint text-brand-primary font-bold' : 'text-brand-slate hover:text-brand-dark hover:bg-black/[0.02]' }}">
                               Ajukan Kebutuhan
                            </a>
                            <a href="{{ route('umkm.orders.index') }}" 
                               class="px-3 py-1.5 rounded-full transition-all duration-300 {{ request()->routeIs('umkm.orders*') || request()->routeIs('payment.show') ? 'bg-brand-mint text-brand-primary font-bold' : 'text-brand-slate hover:text-brand-dark hover:bg-black/[0.02]' }}">
                               Riwayat &amp; Tagihan
                            </a>
                            <a href="{{ route('umkm.tracking.index') }}" 
                               class="px-3 py-1.5 rounded-full transition-all duration-300 inline-flex items-center gap-1.5 {{ request()->routeIs('umkm.tracking*') ? 'bg-brand-mint text-brand-primary font-bold' : 'text-brand-slate hover:text-brand-dark hover:bg-black/[0.02]' }}">
                               <span>Pantau Live GPS</span>
                               <span class="w-1.5 h-1.5 rounded-full bg-brand-emerald animate-pulse"></span>
                            </a>
                        @endhasrole

                        @hasrole('distributor')
                            <a href="{{ route('distributor.products.index') }}" 
                               class="px-3 py-1.5 rounded-full transition-all duration-300 {{ request()->routeIs('distributor.products*') ? 'bg-brand-mint text-brand-primary font-bold' : 'text-brand-slate hover:text-brand-dark hover:bg-black/[0.02]' }}">
                               Katalog &amp; Harga Tier
                            </a>
                            <a href="{{ route('distributor.orders.index') }}" 
                               class="px-3 py-1.5 rounded-full transition-all duration-300 {{ request()->routeIs('distributor.orders*') ? 'bg-brand-mint text-brand-primary font-bold' : 'text-brand-slate hover:text-brand-dark hover:bg-black/[0.02]' }}">
                               Pesanan Kolektif
                            </a>
                        @endhasrole

                        @hasrole('kurir')
                            <a href="{{ route('kurir.shipments.index') }}" 
                               class="px-3 py-1.5 rounded-full transition-all duration-300 inline-flex items-center gap-1.5 {{ request()->routeIs('kurir.shipments*') ? 'bg-brand-mint text-brand-primary font-bold' : 'text-brand-slate hover:text-brand-dark hover:bg-black/[0.02]' }}">
                               <span>Tugas Pengiriman</span>
                               <span class="w-1.5 h-1.5 rounded-full bg-brand-emerald animate-pulse"></span>
                            </a>
                        @endhasrole

                        @hasrole('admin')
                            <a href="{{ route('admin.categories.index') }}" 
                               class="px-3 py-1.5 rounded-full transition-all duration-300 {{ request()->routeIs('admin.categories*') ? 'bg-brand-mint text-brand-primary font-bold' : 'text-brand-slate hover:text-brand-dark hover:bg-black/[0.02]' }}">
                               Kategori
                            </a>
                            <a href="{{ route('admin.products.index') }}" 
                               class="px-3 py-1.5 rounded-full transition-all duration-300 {{ request()->routeIs('admin.products*') ? 'bg-brand-mint text-brand-primary font-bold' : 'text-brand-slate hover:text-brand-dark hover:bg-black/[0.02]' }}">
                               Master Produk
                            </a>
                            <a href="{{ route('admin.spk.index') }}" 
                               class="px-3 py-1.5 rounded-full transition-all duration-300 {{ request()->routeIs('admin.spk*') ? 'bg-brand-mint text-brand-primary font-bold' : 'text-brand-slate hover:text-brand-dark hover:bg-black/[0.02]' }}">
                               Kriteria SPK
                            </a>
                        @endhasrole
                    </nav>
                </div>

                <div class="flex items-center gap-3" x-data="{ open: false, mobileNav: false }">
                    <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[0.6875rem] font-semibold bg-brand-ochre-soft text-brand-accent border border-brand-accent/15">
                        <span class="w-1.5 h-1.5 rounded-full bg-brand-accent"></span>
                        {{ Auth::user()->roles->first()?->name ? strtoupper(Auth::user()->roles->first()->name) : 'USER' }}
                    </span>

                    <div class="relative">
                        <button @click="open = !open" type="button" class="flex items-center gap-2 p-1 rounded-xl hover:bg-black/[0.03] transition-colors cursor-pointer">
                            <div class="w-8 h-8 rounded-lg bg-brand-primary text-white flex items-center justify-center font-bold text-xs shadow-sm">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <div class="hidden sm:block text-left pr-1">
                                <div class="text-[0.8125rem] font-semibold text-brand-dark leading-tight line-clamp-1">{{ Auth::user()->name }}</div>
                            </div>
                        </button>

                        <div x-show="open" @click.away="open = false" x-cloak
                             class="absolute right-0 mt-2 w-52 bg-white border border-black/[0.06] rounded-2xl shadow-[0_12px_32px_-8px_rgba(0,0,0,0.12)] p-1.5 z-40 text-[0.8125rem] animate-fade-up">
                            <div class="px-3 py-2 border-b border-black/[0.04]">
                                <p class="font-semibold text-brand-dark">{{ Auth::user()->name }}</p>
                                <p class="text-[0.6875rem] text-brand-slate truncate">{{ Auth::user()->email }}</p>
                            </div>
                            <a href="{{ route('profile.complete') }}" class="block w-full text-left px-3 py-2 text-brand-slate hover:bg-black/[0.03] rounded-xl font-medium transition-colors">
                                Edit Profil
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-3 py-2 text-brand-brick hover:bg-red-50 rounded-xl font-medium transition-colors cursor-pointer mt-1">
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Mobile Navigation Links Bar -->
                <div class="w-full md:hidden flex items-center gap-1 overflow-x-auto pt-2 pb-1 text-xs border-t border-black/[0.04]">
                    <a href="{{ route('dashboard') }}" class="px-3 py-1 rounded-full whitespace-nowrap {{ request()->routeIs('dashboard') ? 'bg-brand-primary text-white font-bold' : 'text-brand-slate' }}">
                        Dashboard
                    </a>
                    @hasrole('umkm')
                        <a href="{{ route('umkm.requests.index') }}" class="px-3 py-1 rounded-full whitespace-nowrap {{ request()->routeIs('umkm.requests*') ? 'bg-brand-primary text-white font-bold' : 'text-brand-slate' }}">
                            Kebutuhan
                        </a>
                        <a href="{{ route('umkm.orders.index') }}" class="px-3 py-1 rounded-full whitespace-nowrap {{ request()->routeIs('umkm.orders*') ? 'bg-brand-primary text-white font-bold' : 'text-brand-slate' }}">
                            Tagihan
                        </a>
                        <a href="{{ route('umkm.tracking.index') }}" class="px-3 py-1 rounded-full whitespace-nowrap {{ request()->routeIs('umkm.tracking*') ? 'bg-brand-primary text-white font-bold' : 'text-brand-slate' }}">
                            Live GPS
                        </a>
                    @endhasrole
                    @hasrole('distributor')
                        <a href="{{ route('distributor.products.index') }}" class="px-3 py-1 rounded-full whitespace-nowrap {{ request()->routeIs('distributor.products*') ? 'bg-brand-primary text-white font-bold' : 'text-brand-slate' }}">
                            Katalog
                        </a>
                        <a href="{{ route('distributor.orders.index') }}" class="px-3 py-1 rounded-full whitespace-nowrap {{ request()->routeIs('distributor.orders*') ? 'bg-brand-primary text-white font-bold' : 'text-brand-slate' }}">
                            Pesanan
                        </a>
                    @endhasrole
                    @hasrole('kurir')
                        <a href="{{ route('kurir.shipments.index') }}" class="px-3 py-1 rounded-full whitespace-nowrap {{ request()->routeIs('kurir.shipments*') ? 'bg-brand-primary text-white font-bold' : 'text-brand-slate' }}">
                            Tugas GPS
                        </a>
                    @endhasrole
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-3 sm:px-6 lg:px-8 py-6 sm:py-8">
        @if (session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-brand-mint text-brand-primary border border-brand-primary/15 text-[0.8125rem] font-medium flex items-center gap-2.5 animate-fade-up">
                <span class="w-2 h-2 rounded-full bg-brand-emerald"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 p-4 rounded-2xl bg-red-50 text-brand-brick border border-brand-brick/15 text-[0.8125rem] font-medium flex items-center gap-2.5 animate-fade-up">
                <span class="w-2 h-2 rounded-full bg-brand-brick"></span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="border-t border-black/[0.04] py-6 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center gap-2 text-[0.75rem] text-brand-slate">
            <p>Kula'an &mdash; Smart Collective Procurement Platform &copy; {{ date('Y') }}</p>
            <p>Banjarmasin, Kalimantan Selatan</p>
        </div>
    </footer>
</body>
</html>
