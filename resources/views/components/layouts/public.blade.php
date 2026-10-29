@props(['title' => 'Kulaan'])

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
<body class="bg-cream text-ink min-h-screen font-sans">
    <header class="bg-white border-b border-line">
        <div class="max-w-7xl mx-auto px-5 lg:px-8 h-16 flex items-center justify-between gap-6">
            <a href="{{ route('home') }}" class="shrink-0" aria-label="Kulaan">
                <img src="{{ asset('images/logo.svg') }}" alt="Kulaan" class="w-32 h-auto">
            </a>
            <nav class="hidden md:flex items-center gap-7 text-sm font-semibold" aria-label="Navigasi utama">
                <a href="{{ route('home') }}" class="text-muted hover:text-forest transition">Beranda</a>
                <a href="{{ route('public.catalog.index') }}" class="text-muted hover:text-forest transition">Jelajahi Produk</a>
                <a href="{{ route('public.distributors.map') }}" class="text-muted hover:text-forest transition">Peta Distributor</a>
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
