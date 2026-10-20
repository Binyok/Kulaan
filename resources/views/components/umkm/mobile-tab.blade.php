@props(['active' => ''])

@php
    $cartCount = app(\App\Services\CartService::class)->count();

    $tabs = [
        ['key' => 'dashboard', 'label' => 'Beranda', 'url' => route('dashboard'), 'icon' => '<path d="m2.25 12 8.95-8.95a1.13 1.13 0 0 1 1.6 0l8.95 8.95M4.5 9.75V19.5a.75.75 0 0 0 .75.75h13.5a.75.75 0 0 0 .75-.75V9.75"/>'],
        ['key' => 'catalog', 'label' => 'Katalog', 'url' => route('umkm.catalog.index'), 'icon' => '<path d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/>'],
        ['key' => 'cart', 'label' => 'Keranjang', 'url' => route('umkm.cart.index'), 'icon' => '<path d="M2.25 3h1.39c.51 0 .96.34 1.09.84l.38 1.44M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.22c1.12-2.3 2.1-4.68 2.92-7.14a60.11 60.11 0 0 0-16.54-1.84M7.5 14.25 5.11 5.27M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>', 'badge' => $cartCount],
        ['key' => 'orders', 'label' => 'Pesanan', 'url' => route('umkm.orders.index'), 'icon' => '<path d="M20.25 7.5l-.63 10.63a2.25 2.25 0 0 1-2.25 2.12H6.63a2.25 2.25 0 0 1-2.25-2.12L3.75 7.5M10 11.25h4M3.38 7.5h17.25c.62 0 1.12-.5 1.12-1.12v-1.5C21.75 4.26 21.25 3.75 20.63 3.75H3.38c-.62 0-1.13.5-1.13 1.12v1.5c0 .63.5 1.13 1.12 1.13Z"/>'],
        ['key' => 'profile', 'label' => 'Akun', 'url' => route('profile.complete'), 'icon' => '<path d="M17.98 18.72a6 6 0 0 0-8.46 0M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm-9.75 8.25a9 9 0 1 1 13.5 0"/>'],
    ];
@endphp

<div class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-white border-t border-line px-2 pt-2 pb-5 flex items-start justify-around">
    @foreach ($tabs as $tab)
        @php($isActive = $tab['key'] === $active)
        <a href="{{ $tab['url'] }}"
           @if ($isActive) aria-current="page" @endif
           class="flex flex-col items-center gap-1 w-14 relative {{ $isActive ? 'text-forest' : 'text-muted' }}">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">{!! $tab['icon'] !!}</svg>
            <span class="text-[10px] {{ $isActive ? 'font-bold' : 'font-semibold' }}">{{ $tab['label'] }}</span>
            @if (! empty($tab['badge']) && $tab['badge'] > 0)
                <span class="absolute top-0 right-2 min-w-[18px] h-[18px] px-1 rounded-full bg-brass text-white text-[10px] font-bold flex items-center justify-center">{{ $tab['badge'] }}</span>
            @endif
        </a>
    @endforeach
</div>
