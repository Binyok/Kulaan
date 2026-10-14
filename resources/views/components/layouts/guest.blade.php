@props(['wide' => false])

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
<body class="min-h-[100dvh] font-sans text-brand-dark" style="background-color: var(--color-brand-cream);">
    <div class="min-h-[100dvh] grid lg:grid-cols-[5fr_6fr]">
        <!-- Brand panel: logo menempel atas, lokasi menempel bawah, konten mengisi tengah. -->
        <aside class="texture-grain bg-brand-forest text-white px-6 py-8 sm:px-10 lg:p-12 flex flex-col gap-8 lg:min-h-[100dvh] lg:sticky lg:top-0">
            <a href="/" class="inline-flex items-center gap-3 self-start" aria-label="Kula'an beranda">
                <span class="w-11 h-11 rounded-2xl bg-white/10 border border-white/15 text-white flex items-center justify-center font-display font-semibold text-xl shrink-0">K</span>
                <span class="font-display font-semibold text-[1.75rem] tracking-tight">Kula'an</span>
            </a>

            <div class="my-auto flex flex-col gap-6 lg:gap-8">
                <h2 class="font-display font-semibold tracking-tight text-balance text-3xl lg:text-[2.5rem] lg:leading-[1.12]">
                    Belanja bahan baku barengan, hemat sampai 28%.
                </h2>

            <ul class="hidden lg:block space-y-6 mt-10">
                <li class="flex gap-4">
                    <span class="font-display font-semibold text-brand-accent text-lg leading-none tnum">01</span>
                    <div>
                        <p class="font-bold text-white text-sm">Harga grosir tanpa MOQ besar</p>
                        <p class="text-white/65 text-xs mt-1 leading-relaxed">Kebutuhan digabung otomatis dengan UMKM sekitar sampai target grosir tercapai.</p>
                    </div>
                </li>
                <li class="flex gap-4">
                    <span class="font-display font-semibold text-brand-accent text-lg leading-none tnum">02</span>
                    <div>
                        <p class="font-bold text-white text-sm">Kamu yang pilih distributor</p>
                        <p class="text-white/65 text-xs mt-1 leading-relaxed">Metode AHP + TOPSIS memberi rekomendasi harga, kualitas, dan kecepatan pengiriman.</p>
                    </div>
                </li>
                <li class="flex gap-4">
                    <span class="font-display font-semibold text-brand-accent text-lg leading-none tnum">03</span>
                    <div>
                        <p class="font-bold text-white text-sm">Kurir terlacak realtime</p>
                        <p class="text-white/65 text-xs mt-1 leading-relaxed">Pantau posisi armada di peta sampai barang tiba di tempat usaha.</p>
                    </div>
                </li>
            </ul>
            </div>

            <p class="hidden lg:block text-white/50 text-xs">Sidoarjo, Jawa Timur</p>
        </aside>

        <!-- Form -->
        <main class="flex flex-col justify-center items-center px-4 py-10 sm:py-14">
            <div class="w-full {{ $wide ? 'max-w-3xl' : 'max-w-[26rem]' }}" data-reveal>
                <div class="card-shell">
                    <div class="card-core p-7 md:p-9">
                        {{ $slot }}
                    </div>
                </div>

                <p class="text-center text-[0.6875rem] text-brand-slate mt-8 tracking-wide">
                    &copy; {{ date('Y') }} Kula'an. Hak Cipta Dilindungi.
                </p>
            </div>
        </main>
    </div>
</body>
</html>
