@props(['active' => ''])

@php
    $cartCount = app(\App\Services\CartService::class)->count();

    $links = [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => '<path d="m2.25 12 8.95-8.95a1.13 1.13 0 0 1 1.6 0l8.95 8.95M4.5 9.75V19.5a.75.75 0 0 0 .75.75h13.5a.75.75 0 0 0 .75-.75V9.75"/>'],
        ['key' => 'catalog', 'label' => 'Katalog', 'url' => route('umkm.catalog.index'), 'icon' => '<path d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/>'],
        ['key' => 'cart', 'label' => 'Keranjang', 'url' => route('umkm.cart.index'), 'icon' => '<path d="M2.25 3h1.39c.51 0 .96.34 1.09.84l.38 1.44M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.22c1.12-2.3 2.1-4.68 2.92-7.14a60.11 60.11 0 0 0-16.54-1.84M7.5 14.25 5.11 5.27M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>', 'badge' => $cartCount],
        ['key' => 'requests', 'label' => 'Pengajuan', 'url' => route('umkm.requests.index'), 'icon' => '<path d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>'],
        ['key' => 'orders', 'label' => 'Pesanan', 'url' => route('umkm.orders.index'), 'icon' => '<path d="M20.25 7.5l-.63 10.63a2.25 2.25 0 0 1-2.25 2.12H6.63a2.25 2.25 0 0 1-2.25-2.12L3.75 7.5M10 11.25h4M3.38 7.5h17.25c.62 0 1.12-.5 1.12-1.12v-1.5C21.75 4.26 21.25 3.75 20.63 3.75H3.38c-.62 0-1.13.5-1.13 1.12v1.5c0 .63.5 1.13 1.12 1.13Z"/>'],
        ['key' => 'tracking', 'label' => 'Pelacakan', 'url' => route('umkm.tracking.index'), 'icon' => '<path d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path d="M19.5 10.5c0 7.14-7.5 11.25-7.5 11.25S4.5 17.64 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>'],
    ];
@endphp

<nav class="flex-1 px-4 py-6 space-y-1 text-sm font-semibold">
    @foreach ($links as $link)
        @php($isActive = $link['key'] === $active)
        <a href="{{ $link['url'] }}"
           @if ($isActive) aria-current="page" @endif
           class="flex items-center gap-3 px-4 py-3 rounded-2xl {{ $isActive ? 'bg-forest text-white' : 'text-muted hover:bg-cream hover:text-ink transition' }}">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">{!! $link['icon'] !!}</svg>
            {{ $link['label'] }}
            @if (! empty($link['badge']) && $link['badge'] > 0)
                <span class="ml-auto bg-brass text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $link['badge'] }}</span>
            @endif
        </a>
    @endforeach
</nav>
