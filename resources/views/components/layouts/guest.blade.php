@props(['wide' => false])

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="turbo-cache-control" content="no-cache">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#143726">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <title>{{ config('app.name', "Kula'an") }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <noscript><style>[data-reveal] { opacity: 1 !important; transform: none !important; }</style></noscript>
</head>
<body class="bg-cream text-ink min-h-screen">
    <div class="min-h-screen lg:grid lg:grid-cols-[5fr_6fr]">
        <!-- BRAND: compact header on mobile, sticky side panel on desktop -->
        <aside class="texture-grain bg-forest text-white px-5 pt-6 pb-10 lg:px-10 lg:py-12 flex flex-col lg:gap-8 lg:min-h-screen lg:sticky lg:top-0">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5 lg:gap-3 self-start" aria-label="Kulaan">
                <img src="{{ asset('images/logo-reversed.svg') }}" alt="Kulaan" class="h-9 lg:h-10 w-auto">
            </a>

            <h2 class="lg:hidden font-extrabold tracking-tight text-[1.65rem] leading-[1.15] mt-5">Belanja bahan baku barengan, hemat sampai 28%.</h2>

            <div class="hidden lg:flex my-auto flex-col gap-6">
                <h2 class="font-extrabold tracking-tight text-[2.5rem] leading-[1.12]">Belanja bahan baku barengan, hemat sampai 28%.</h2>
                <ul class="space-y-6 mt-6">
                    <li class="flex gap-4">
                        <span class="font-extrabold text-brass text-lg leading-none">01</span>
                        <div>
                            <p class="font-bold text-white text-sm">Harga grosir tanpa MOQ besar</p>
                            <p class="text-white/65 text-xs mt-1 leading-relaxed">Kebutuhan digabung otomatis dengan UMKM sekitar sampai target grosir tercapai.</p>
                        </div>
                    </li>
                    <li class="flex gap-4">
                        <span class="font-extrabold text-brass text-lg leading-none">02</span>
                        <div>
                            <p class="font-bold text-white text-sm">Kamu yang pilih distributor</p>
                            <p class="text-white/65 text-xs mt-1 leading-relaxed">Metode AHP + TOPSIS memberi rekomendasi harga, kualitas, dan kecepatan pengiriman.</p>
                        </div>
                    </li>
                    <li class="flex gap-4">
                        <span class="font-extrabold text-brass text-lg leading-none">03</span>
                        <div>
                            <p class="font-bold text-white text-sm">Kurir terlacak realtime</p>
                            <p class="text-white/65 text-xs mt-1 leading-relaxed">Pantau posisi armada di peta sampai barang tiba di tempat usaha.</p>
                        </div>
                    </li>
                </ul>
            </div>

            <p class="hidden lg:block text-white/50 text-xs">Sidoarjo, Jawa Timur</p>
        </aside>

        <!-- FORM CONTAINER: overlapping card on mobile, centered on desktop -->
        <main class="flex flex-col items-center px-4 -mt-6 pb-6 lg:mt-0 lg:justify-center lg:px-6 lg:py-14">
            <div class="w-full {{ $wide ? 'max-w-xl' : 'max-w-[26rem]' }}">
                <div class="bg-white rounded-3xl border border-line shadow-sm p-6 lg:p-9">
                    {{ $slot }}
                </div>
                <p class="text-center text-[0.6875rem] text-muted mt-5 lg:mt-8 tracking-wide">&copy; {{ date('Y') }} Kula'an. Hak Cipta Dilindungi.</p>
            </div>
        </main>
    </div>
</body>
</html>
