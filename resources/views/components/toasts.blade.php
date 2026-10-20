@php
    $toasts = collect([
        session('success') ? ['message' => session('success'), 'tone' => 'success'] : null,
        session('error') ? ['message' => session('error'), 'tone' => 'error'] : null,
        session('info') ? ['message' => session('info'), 'tone' => 'info'] : null,
    ])->filter()->values()->all();
@endphp

@if ($toasts !== [])
    <div x-data="{ toasts: @js($toasts), }"
         class="fixed top-4 left-1/2 -translate-x-1/2 z-50 w-[calc(100%-2rem)] max-w-md space-y-2 pointer-events-none">
        <template x-for="(toast, index) in toasts" :key="index">
            <template x-if="toast.open !== false">
                <div :role="toast.tone === 'error' ? 'alert' : 'status'"
                     x-init="setTimeout(() => toast.open = false, toast.tone === 'error' ? 6000 : 4000)"
                     x-transition.opacity.duration.300ms
                     :class="{
                        'bg-forest/10 text-forest border-forest/20': toast.tone === 'success',
                        'bg-danger/10 text-danger border-danger/20': toast.tone === 'error',
                        'bg-brass-soft text-brass border-brass/30': toast.tone === 'info'
                     }"
                     class="p-4 rounded-2xl border text-xs font-bold flex items-center gap-2.5 shadow-lg pointer-events-auto">
                    <span class="w-2 h-2 rounded-full shrink-0"
                          :class="{ 'bg-forest': toast.tone === 'success', 'bg-danger': toast.tone === 'error', 'bg-brass': toast.tone === 'info' }"></span>
                    <span class="flex-1" x-text="toast.message"></span>
                    <button type="button" @click="toast.open = false"
                            class="shrink-0 -mr-1 w-6 h-6 flex items-center justify-center rounded-lg hover:bg-black/5 cursor-pointer"
                            aria-label="Tutup notifikasi">&times;</button>
                </div>
            </template>
        </template>
    </div>
@endif
