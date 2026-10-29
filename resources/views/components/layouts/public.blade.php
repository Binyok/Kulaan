@props(['title' => 'Kulaan', 'active' => ''])

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#143726">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.svg') }}">
    <title>{{ $title }} - Kulaan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-cream text-ink min-h-screen font-sans pb-16 md:pb-0">
    <header class="bg-white border-b border-line sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-5 lg:px-8 h-16 flex items-center justify-between gap-6">
            <a href="{{ route('home') }}" class="shrink-0" aria-label="Kulaan">
                <img src="{{ asset('images/logo.svg') }}" alt="Kulaan" class="w-32 h-auto">
            </a>
            <nav class="hidden md:flex items-center gap-7 text-sm font-semibold" aria-label="Navigasi utama">
                <a href="{{ route('home') }}" class="text-muted hover:text-forest transition">Beranda</a>
                <a href="{{ route('public.catalog.index') }}" class="{{ request()->routeIs('public.catalog.*') ? 'text-forest font-bold border-b-2 border-brass-gold pb-1' : 'text-muted hover:text-forest' }} transition">Jelajahi Produk</a>
                <a href="{{ route('public.distributors.map') }}" class="{{ request()->routeIs('public.distributors.*') ? 'text-forest font-bold border-b-2 border-brass-gold pb-1' : 'text-muted hover:text-forest' }} transition">Peta Distributor</a>
                <a href="{{ route('home') }}#cara-kerja" class="text-muted hover:text-forest transition">Cara Kerja</a>
            </nav>
            <div class="flex items-center gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="bg-forest text-white rounded-xl px-4 py-2 text-xs font-bold">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="hidden sm:inline-flex border border-line text-ink rounded-xl px-4 py-2 text-xs font-bold">Masuk</a>
                    <a href="{{ route('register') }}" class="bg-forest text-white rounded-xl px-4 py-2 text-xs font-bold">Daftar Jadi UMKM</a>
                @endauth
            </div>
        </div>
    </header>

    <main>{{ $slot }}</main>

    <!-- NAVIGASI BAWAH MOBILE (GUEST / PUBLIK) -->
    <nav class="md:hidden fixed bottom-0 inset-x-0 bg-white border-t border-line z-40 px-3 py-2 flex items-center justify-around text-center shadow-lg">
        <a href="{{ route('home') }}" class="flex flex-col items-center gap-1 text-[10px] font-bold {{ request()->routeIs('home') ? 'text-forest' : 'text-muted' }}">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2.25 12l8.95-8.95a1.125 1.125 0 011.6 0l8.95 8.95M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125h13.5c.621 0 1.125-.504 1.125-1.125V9.75"/></svg>
            Beranda
        </a>
        <a href="{{ route('public.catalog.index') }}" class="flex flex-col items-center gap-1 text-[10px] font-bold {{ request()->routeIs('public.catalog.*') ? 'text-forest' : 'text-muted' }}">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            Jelajahi
        </a>
        <a href="{{ route('public.distributors.map') }}" class="flex flex-col items-center gap-1 text-[10px] font-bold {{ request()->routeIs('public.distributors.*') ? 'text-forest' : 'text-muted' }}">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M19.5 10.5c0 7.14-7.5 11.25-7.5 11.25S4.5 17.64 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
            Peta
        </a>
        @auth
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 text-[10px] font-bold text-forest">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0"/></svg>
                Dashboard
            </a>
        @else
            <a href="{{ route('login') }}" class="flex flex-col items-center gap-1 text-[10px] font-bold text-muted">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l3 3m0 0l-3 3m3-3H8.25"/></svg>
                Masuk
            </a>
        @endauth
    </nav>

    <footer class="bg-forest-deep text-cream mt-16">
        <div class="max-w-7xl mx-auto px-5 lg:px-8 py-10 flex flex-col sm:flex-row justify-between gap-6">
            <div>
                <p class="font-bold">Kulaan</p>
                <p class="text-sm text-cream/65 mt-2">Kulakan kolektif UMKM Indonesia</p>
                <p class="text-xs text-cream/45 mt-5">GTNIC 2026 · Web Development</p>
            </div>
            <div class="flex flex-wrap gap-x-6 gap-y-3 text-sm text-cream/75">
                <a href="{{ route('public.catalog.index') }}">Jelajahi Produk</a>
                <a href="{{ route('public.distributors.map') }}">Peta Distributor</a>
                <a href="{{ route('register') }}">Daftar Jadi UMKM</a>
                <a href="{{ route('login') }}">Masuk</a>
            </div>
        </div>
    </footer>
</body>
</html>
