<x-layouts.app title="Lengkapi Profil">
    {{-- Forest band: page header --}}
    <div class="-mx-3 sm:-mx-6 lg:-mx-8 -mt-6 sm:-mt-8 mb-6 sm:mb-8">
        <div class="texture-grain bg-brand-forest text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
                <div class="max-w-3xl mx-auto" data-reveal>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[0.625rem] font-semibold bg-white/10 text-[#D8E8DD] uppercase tracking-[0.14em]">
                        Profil
                    </span>
                    <h1 class="font-display font-semibold tracking-tight text-balance text-4xl sm:text-5xl mt-4">
                        Lengkapi Profilmu
                    </h1>
                    <p class="text-sm text-white/70 leading-relaxed mt-3">
                        Data ini dipakai buat pengiriman, tagihan, dan nyocokin kamu ke grup pengadaan kolektif yang pas.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-3xl mx-auto">
        @if (session('info'))
            <div class="mb-6 p-4 rounded-2xl bg-brand-ochre-soft text-brand-brass border border-brand-accent/20 text-[0.8125rem] font-medium flex items-center gap-2.5">
                <span class="w-2 h-2 rounded-full bg-brand-accent shrink-0"></span>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('profile.complete.update') }}" class="space-y-6" x-data="{ showPass: false }">
            @csrf

            {{-- Data akun --}}
            <section class="bg-white rounded-[20px] border border-brand-line p-5 sm:p-7" data-reveal>
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-10 h-10 rounded-full bg-brand-mint text-brand-primary flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="1.8"/><path d="M4 21c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </span>
                    <h2 class="text-[0.9375rem] font-bold text-brand-dark">Data akun</h2>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Nama lengkap</label>
                        <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required class="input-field">
                        @error('name')
                            <p class="text-[0.75rem] text-brand-brick mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="phone" class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Nomor WhatsApp / telepon</label>
                        <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="081234567890" required class="input-field">
                        @error('phone')
                            <p class="text-[0.75rem] text-brand-brick mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <label for="address" class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Alamat lengkap</label>
                    <textarea id="address" name="address" rows="2" required class="input-field">{{ old('address', $user->address) }}</textarea>
                    @error('address')
                        <p class="text-[0.75rem] text-brand-brick mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid sm:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label for="district" class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Kecamatan</label>
                        <select id="district" name="district" required class="input-field bg-white">
                            @foreach (['Banjarmasin Utara', 'Banjarmasin Barat', 'Banjarmasin Timur', 'Banjarmasin Selatan', 'Banjarmasin Tengah'] as $area)
                                <option value="{{ $area }}" @selected(old('district', $umkm?->district ?? $distributor?->district) === $area)>{{ $area }}</option>
                            @endforeach
                        </select>
                        @error('district')
                            <p class="text-[0.75rem] text-brand-brick mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="city" class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Kota</label>
                        <input id="city" type="text" name="city" value="{{ old('city', $umkm?->city ?? $distributor?->city ?? 'Banjarmasin') }}" required class="input-field">
                        @error('city')
                            <p class="text-[0.75rem] text-brand-brick mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            @if ($isUmkm || $isDistributor)
                {{-- Data usaha --}}
                <section class="bg-brand-sand/70 rounded-[20px] p-5 sm:p-7" data-reveal>
                    <div class="flex items-center gap-3 mb-5">
                        <span class="w-10 h-10 rounded-full bg-white text-brand-primary flex items-center justify-center shrink-0 border border-brand-line">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-4h6v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <h2 class="text-[0.9375rem] font-bold text-brand-dark">Data usaha</h2>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label for="business_name" class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">
                                {{ $isDistributor ? 'Nama perusahaan' : 'Nama usaha' }}
                            </label>
                            <input id="business_name" type="text" name="business_name"
                                value="{{ old('business_name', $umkm?->business_name ?? $distributor?->company_name) }}"
                                class="input-field bg-white" placeholder="cth. Warung Berkah Kuliner">
                            @error('business_name')
                                <p class="text-[0.75rem] text-brand-brick mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                        @if ($isUmkm)
                            <div>
                                <label for="business_type" class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Jenis usaha</label>
                                <select id="business_type" name="business_type" class="input-field bg-white">
                                    @foreach (['Kuliner', 'Minuman', 'Sembako', 'Kerajinan'] as $type)
                                        <option value="{{ $type }}" @selected(old('business_type', $umkm?->business_type) === $type)>{{ $type }}</option>
                                    @endforeach
                                </select>
                                @error('business_type')
                                    <p class="text-[0.75rem] text-brand-brick mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                    </div>
                </section>
            @endif

            {{-- Kata sandi --}}
            <section class="bg-white rounded-[20px] border border-brand-line p-5 sm:p-7" data-reveal>
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-10 h-10 rounded-full bg-brand-mint text-brand-primary flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="4" y="10" width="16" height="10" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M8 10V7a4 4 0 0 1 8 0v3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </span>
                    <div>
                        <h2 class="text-[0.9375rem] font-bold text-brand-dark">Atur kata sandi</h2>
                        <p class="text-[0.75rem] text-brand-slate">Opsional — isi kalau kamu mau bisa login pakai email juga.</p>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label for="password" class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Kata sandi baru</label>
                        <div class="relative">
                            <input id="password" :type="showPass ? 'text' : 'password'" name="password" class="input-field pr-12" autocomplete="new-password">
                            <button type="button" @click="showPass = !showPass"
                                class="absolute right-1 top-1/2 -translate-y-1/2 min-h-11 min-w-11 flex items-center justify-center text-brand-slate hover:text-brand-dark text-xs font-semibold cursor-pointer">
                                <span x-text="showPass ? 'Tutup' : 'Lihat'"></span>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-[0.75rem] text-brand-brick mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Ulangi kata sandi</label>
                        <input id="password_confirmation" :type="showPass ? 'text' : 'password'" name="password_confirmation" class="input-field" autocomplete="new-password">
                    </div>
                </div>
            </section>

            {{-- Actions: sticky on mobile --}}
            <div class="sticky bottom-20 sm:bottom-6 z-10 bg-brand-cream/90 backdrop-blur rounded-[20px] border border-brand-line p-4 flex flex-col sm:flex-row gap-3">
                <button type="submit" class="btn-primary justify-center py-3.5 text-sm font-bold sm:flex-1 shadow-md shadow-brand-primary/20">
                    Simpan Profil
                </button>
                @if (blank($user->phone) || blank($user->address))
                    <button type="button" disabled aria-disabled="true"
                        class="btn-ghost justify-center py-3.5 text-sm opacity-50 cursor-not-allowed sm:flex-1">
                        Lengkapi data wajib dulu
                    </button>
                @else
                    <a href="{{ route('dashboard') }}" class="btn-ghost justify-center py-3.5 text-sm sm:flex-1">Kembali ke Dashboard</a>
                @endif
            </div>
        </form>
    </div>
</x-layouts.app>
