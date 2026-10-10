<x-layouts.app>
    <x-slot name="title">Dashboard Administrator</x-slot>

    {{-- Forest band: page header --}}
    <div class="-mx-3 sm:-mx-6 lg:-mx-8 -mt-6 sm:-mt-8 mb-6 sm:mb-8">
        <div class="texture-grain bg-brand-forest text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
                <div class="max-w-2xl" data-reveal>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[0.625rem] font-semibold bg-white/10 text-[#D8E8DD] uppercase tracking-[0.14em]">
                        Kontrol Pusat
                    </span>
                    <h1 class="font-display font-semibold tracking-tight text-balance text-4xl sm:text-5xl mt-4">
                        Dashboard Administrator
                    </h1>
                    <p class="text-sm text-white/70 leading-relaxed mt-3">
                        Pantau denyut ekosistem: siapa gabung, berapa hemat tercipta, dan seberapa sehat mesin SPK-nya.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6 sm:space-y-8">
        {{-- Stat strip: 4 metrics --}}
        <dl class="bg-white rounded-[20px] border border-brand-line grid grid-cols-2 lg:grid-cols-4 overflow-hidden" data-reveal>
            <div class="p-5 sm:p-6 flex items-center gap-4 border-b lg:border-b-0 border-r border-brand-line/60">
                <span class="w-11 h-11 rounded-full bg-brand-mint text-brand-primary flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="m3.3 7 8.7 5 8.7-5M12 22V12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <div class="min-w-0">
                    <dd class="font-display font-extrabold text-2xl sm:text-3xl text-brand-dark tabular-nums leading-none">{{ $totalUmkm }}</dd>
                    <dt class="text-[0.6875rem] text-brand-slate font-medium mt-1.5">UMKM terdaftar</dt>
                </div>
            </div>
            <div class="p-5 sm:p-6 flex items-center gap-4 border-b lg:border-b-0 border-brand-line/60">
                <span class="w-11 h-11 rounded-full bg-brand-mint text-brand-primary flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-4h6v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <div class="min-w-0">
                    <dd class="font-display font-extrabold text-2xl sm:text-3xl text-brand-dark tabular-nums leading-none">{{ $totalDistributors }}</dd>
                    <dt class="text-[0.6875rem] text-brand-slate font-medium mt-1.5">Distributor rekanan</dt>
                </div>
            </div>
            <div class="p-5 sm:p-6 flex items-center gap-4 border-r border-brand-line/60">
                <span class="w-11 h-11 rounded-full bg-brand-mint text-brand-primary flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 17h-2v-11a1 1 0 0 1 1-1h9v12m-8 0h8m4 0h2v-6l-3-4h-3v10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="7.5" cy="17.5" r="1.8" stroke="currentColor" stroke-width="1.8"/><circle cx="17.5" cy="17.5" r="1.8" stroke="currentColor" stroke-width="1.8"/></svg>
                </span>
                <div class="min-w-0">
                    <dd class="font-display font-extrabold text-2xl sm:text-3xl text-brand-dark tabular-nums leading-none">{{ $totalCouriers }}</dd>
                    <dt class="text-[0.6875rem] text-brand-slate font-medium mt-1.5">Armada kurir</dt>
                </div>
            </div>
            <div class="p-5 sm:p-6 flex items-center gap-4">
                <span class="w-11 h-11 rounded-full bg-brand-ochre-soft text-brand-brass flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 1v22m5-18H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <div class="min-w-0">
                    <dd class="font-display font-extrabold text-xl sm:text-2xl text-brand-brass tabular-nums leading-none">Rp {{ number_format($totalSavingsSystem, 0, ',', '.') }}</dd>
                    <dt class="text-[0.6875rem] text-brand-slate font-medium mt-1.5">Total hemat ekosistem</dt>
                </div>
            </div>
        </dl>

        {{-- Management shortcuts --}}
        <section data-reveal>
            <h2 class="text-base font-bold text-brand-dark tracking-tight mb-4">Kelola ekosistem</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-5">
                <a href="{{ route('admin.categories.index') }}" class="group bg-white rounded-[20px] border border-brand-line p-6 flex gap-4 items-start hover:border-brand-primary/40 hover:-translate-y-0.5 transition-all">
                    <span class="w-11 h-11 rounded-full bg-brand-mint text-brand-primary flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7a2 2 0 0 1 2-2h4l2 2h6a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
                    </span>
                    <span class="min-w-0">
                        <span class="block font-bold text-brand-dark group-hover:text-brand-primary transition-colors">Master Kategori</span>
                        <span class="block text-[0.75rem] text-brand-slate mt-1 leading-relaxed">Kategori produk baku &amp; standar mutu.</span>
                    </span>
                </a>
                <a href="{{ route('admin.products.index') }}" class="group bg-white rounded-[20px] border border-brand-line p-6 flex gap-4 items-start hover:border-brand-primary/40 hover:-translate-y-0.5 transition-all">
                    <span class="w-11 h-11 rounded-full bg-brand-mint text-brand-primary flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="m3.3 7 8.7 5 8.7-5M12 22V12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <span class="min-w-0">
                        <span class="block font-bold text-brand-dark group-hover:text-brand-primary transition-colors">Master Produk Baku</span>
                        <span class="block text-[0.75rem] text-brand-slate mt-1 leading-relaxed">Produk acuan, harga benchmark &amp; unit standar.</span>
                    </span>
                </a>
                <a href="{{ route('admin.spk.index') }}" class="group bg-brand-forest rounded-[20px] p-6 flex gap-4 items-start text-white hover:-translate-y-0.5 transition-all shadow-[0_12px_32px_-12px_rgba(20,52,42,0.5)]">
                    <span class="w-11 h-11 rounded-full bg-brand-accent/20 border border-brand-accent/40 text-brand-accent flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 20h18M6 16v-5m6 5V8m6 8v-9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </span>
                    <span class="min-w-0">
                        <span class="block font-bold">Kriteria SPK (AHP)</span>
                        <span class="block text-[0.75rem] text-white/70 mt-1 leading-relaxed">Bobot perbandingan berpasangan &amp; uji konsistensi — jantungnya rekomendasi.</span>
                    </span>
                </a>
            </div>
        </section>
    </div>
</x-layouts.app>
