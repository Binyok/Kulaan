@props(['align' => 'right', 'size' => 'lg'])

@php
    $count = $navNotificationCount ?? 0;
    $items = $navNotifications ?? collect();
    $side = $align === 'left' ? 'left-0' : 'right-0';
    $button = $size === 'sm' ? 'w-10 h-10' : 'w-11 h-11';
@endphp

<div class="relative" x-data="{ notifOpen: false }">
    <button type="button" @click="notifOpen = !notifOpen"
            class="{{ $button }} rounded-2xl bg-white border border-line flex items-center justify-center text-ink relative cursor-pointer"
            aria-label="Buka notifikasi">
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14.86 17.08a23.85 23.85 0 0 0 5.45-1.31A8.97 8.97 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.97 8.97 0 0 1-2.31 6.02c1.73.64 3.56 1.09 5.45 1.31m5.72 0a24.26 24.26 0 0 1-5.72 0m5.72 0a3 3 0 1 1-5.72 0"/></svg>
        @if ($count > 0)
            <span class="absolute -top-1.5 -right-1.5 min-w-[20px] h-5 px-1 rounded-full bg-danger text-white text-[11px] font-bold flex items-center justify-center">{{ $count > 99 ? '99+' : $count }}</span>
        @endif
    </button>
    <div x-show="notifOpen" @click.away="notifOpen = false" x-cloak
         class="absolute {{ $side }} mt-2 w-72 bg-white border border-line rounded-2xl shadow-xl p-4 z-40 text-left">
        <p class="text-sm font-extrabold text-ink">Notifikasi</p>
        <div class="mt-3 space-y-1">
            @forelse ($items as $item)
                <a href="{{ $item['url'] }}" class="flex items-start gap-2 rounded-xl p-2 hover:bg-cream">
                    <span class="w-2 h-2 mt-1.5 rounded-full shrink-0 bg-{{ $item['tone'] }}"></span>
                    <span class="text-xs text-ink"><b>{{ $item['count'] }}</b> {{ $item['label'] }}</span>
                </a>
            @empty
                <p class="text-xs text-muted px-2 py-3">Belum ada notifikasi baru.</p>
            @endforelse
        </div>
    </div>
</div>
