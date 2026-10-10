<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', "Kula'an") }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <noscript><style>[data-reveal] { opacity: 1 !important; transform: none !important; }</style></noscript>
</head>
<body class="min-h-[100dvh] flex flex-col justify-center items-center py-12 px-4" style="background: linear-gradient(160deg, #FBF8F2 0%, #EFF1E8 50%, #FBF8F2 100%);">
    <div class="w-full max-w-[26rem]" data-reveal>
        <div class="text-center mb-10">
            <a href="/" class="inline-flex items-center gap-3 group" aria-label="Kula'an beranda">
                <span class="w-11 h-11 rounded-2xl bg-brand-primary text-white flex items-center justify-center font-display font-semibold text-xl shadow-[0_2px_12px_-2px_rgba(45,106,79,0.35)] transition-all duration-300 group-hover:shadow-[0_4px_20px_-2px_rgba(45,106,79,0.45)] group-hover:-translate-y-0.5">K</span>
                <span class="font-display font-semibold text-[1.75rem] text-brand-dark tracking-tight">Kula'an</span>
            </a>
            <p class="text-[0.8125rem] text-brand-slate mt-2.5 tracking-[-0.01em]">Pengadaan Kolektif Bahan Baku UMKM</p>
        </div>

        <div class="card-shell">
            <div class="card-core p-7 md:p-9">
                {{ $slot }}
            </div>
        </div>

        <p class="text-center text-[0.6875rem] text-brand-slate mt-8 tracking-wide">
            &copy; {{ date('Y') }} Kula'an. Hak Cipta Dilindungi.
        </p>
    </div>
</body>
</html>
