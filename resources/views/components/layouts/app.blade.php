<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#143726">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.svg') }}">

    @auth
        <meta name="kulaan-live-endpoint" content="{{ route('live.revision') }}">
        <meta name="kulaan-live-interval" content="10000">
    @endauth

    <title>{{ $title ?? "Kulaan" }} - Platform Pengadaan Kolektif</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <noscript><style>[data-reveal] { opacity: 1 !important; transform: none !important; }</style></noscript>
</head>
<body class="bg-cream text-ink min-h-screen">
    <x-toasts />

    {{ $slot }}

    <!-- Global App Confirmation Modal -->
    <div x-data="kulaanConfirmModal"
         x-show="open"
         x-cloak
         @keydown.escape.window="open = false"
         class="modal-overlay z-50 fixed inset-0 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs transition-opacity duration-200">
        <div @click.away="open = false"
             class="bg-white rounded-3xl border border-line shadow-2xl max-w-sm w-full p-6 text-center transform transition-all">
            <div class="w-12 h-12 rounded-2xl mx-auto flex items-center justify-center mb-4"
                 :class="isDanger ? 'bg-danger/10 text-danger border border-danger/20' : 'bg-forest/10 text-forest border border-forest/20'">
                <svg x-show="isDanger" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <svg x-show="!isDanger" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="font-extrabold text-lg text-ink" x-text="title"></h3>
            <p class="text-xs text-muted mt-2 leading-relaxed" x-text="message"></p>

            <div class="mt-6 flex items-center justify-center gap-3">
                <button type="button"
                        @click="open = false"
                        class="flex-1 py-2.5 px-4 text-xs font-bold rounded-2xl border border-line text-ink hover:bg-cream transition cursor-pointer"
                        x-text="cancelText">
                </button>
                <button type="button"
                        @click="proceed()"
                        :class="isDanger ? 'bg-danger hover:bg-danger/90 text-white' : 'bg-forest hover:bg-forest-dark text-white'"
                        class="flex-1 py-2.5 px-4 text-xs font-bold rounded-2xl shadow-sm transition-colors text-center cursor-pointer"
                        x-text="confirmText">
                </button>
            </div>
        </div>
    </div>
</body>
</html>
