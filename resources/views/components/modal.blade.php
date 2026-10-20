@props(['open', 'title', 'eyebrow' => null, 'subtitle' => null, 'size' => 'lg'])

@php($widthClass = match ($size) {
    'sm' => 'max-w-md',
    'xl' => 'max-w-xl',
    '2xl' => 'max-w-2xl',
    default => 'max-w-lg',
})

<div x-show="{{ $open }}" x-cloak
     @keydown.escape.window="{{ $open }} = false"
     x-effect="(document.body.style.overflow = {{ $open }} ? 'hidden' : '', {{ $open }} && $nextTick(() => $el.querySelector('.modal-content').focus()))"
     class="modal-overlay"
     role="dialog"
     aria-modal="true"
     aria-label="{{ $title }}">
    <div @click.away="{{ $open }} = false"
         tabindex="-1"
         class="modal-content {{ $widthClass }}">
        <div class="flex justify-between items-start gap-4 pb-4 border-b border-black/[0.06]">
            <div class="min-w-0">
                @if ($eyebrow)
                    <span class="eyebrow mb-1.5">{{ $eyebrow }}</span>
                @endif
                <h2 class="text-lg sm:text-xl font-bold text-brand-dark tracking-tight">{{ $title }}</h2>
                @if ($subtitle)
                    <p class="text-xs text-brand-slate mt-1 leading-relaxed">{{ $subtitle }}</p>
                @endif
            </div>
            <button type="button" @click="{{ $open }} = false" aria-label="Tutup dialog" class="btn-icon shrink-0 -mt-1 -mr-2 text-2xl font-light">&times;</button>
        </div>

        {{ $slot }}
    </div>
</div>
