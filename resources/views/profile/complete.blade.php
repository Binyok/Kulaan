<x-layouts.app title="Lengkapi Profil">
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <p class="eyebrow">Profil</p>
            <h1 class="text-[1.5rem] font-bold text-brand-dark tracking-tight">Lengkapi Profil Anda</h1>
            <p class="text-[0.875rem] text-brand-slate mt-1.5">
                Data ini dipakai untuk pengiriman, tagihan, dan kecocokan pengadaan kolektif.
            </p>
        </div>

        @if (session('info'))
            <div class="mb-6 p-4 rounded-2xl bg-brand-ochre-soft text-brand-brass border border-brand-accent/20 text-[0.8125rem] font-medium">
                {{ session('info') }}
            </div>
        @endif

        <form method="POST" action="{{ route('profile.complete.update') }}" class="card-shell p-5 sm:p-7 space-y-6" x-data="{ showPass: false }">
            @csrf

            <div>
                <h2 class="text-[0.9375rem] font-semibold text-brand-dark mb-4">Data Akun</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Nama Lengkap</label>
                        <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required class="input-field">
                        @error('name')
                            <p class="text-[0.75rem] text-brand-brick mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="phone" class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Nomor WhatsApp / Telepon</label>
                        <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="081234567890" required class="input-field">
                        @error('phone')
                            <p class="text-[0.75rem] text-brand-brick mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <label for="address" class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Alamat Lengkap</label>
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
            </div>

            @if ($isUmkm || $isDistributor)
                <div class="pt-5 border-t border-black/[0.06]">
                    <h2 class="text-[0.9375rem] font-semibold text-brand-dark mb-4">Data Usaha</h2>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label for="business_name" class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">
                                {{ $isDistributor ? 'Nama Perusahaan' : 'Nama Usaha' }}
                            </label>
                            <input id="business_name" type="text" name="business_name"
                                value="{{ old('business_name', $umkm?->business_name ?? $distributor?->company_name) }}"
                                class="input-field" placeholder="cth. Warung Berkah Kuliner">
                            @error('business_name')
                                <p class="text-[0.75rem] text-brand-brick mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                        @if ($isUmkm)
                            <div>
                                <label for="business_type" class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Jenis Usaha</label>
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
                </div>
            @endif

            <div class="pt-5 border-t border-black/[0.06]">
                <h2 class="text-[0.9375rem] font-semibold text-brand-dark mb-1.5">Atur Kata Sandi</h2>
                <p class="text-[0.75rem] text-brand-slate mb-4">
                    Opsional: isi hanya jika Anda ingin mengaktifkan login lewat email untuk akun Google ini.
                </p>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Kata Sandi Baru</label>
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
                        <label for="password_confirmation" class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Ulangi Kata Sandi</label>
                        <input id="password_confirmation" :type="showPass ? 'text' : 'password'" name="password_confirmation" class="input-field" autocomplete="new-password">
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                <button type="submit" class="btn-primary justify-center py-3 text-[0.875rem] sm:flex-1">
                    Simpan Profil
                </button>
                @if (blank($user->phone) || blank($user->address))
                    <button type="button" disabled aria-disabled="true"
                        class="btn-ghost justify-center py-3 text-[0.875rem] opacity-50 cursor-not-allowed sm:flex-1">
                        Lengkapi data wajib terlebih dahulu
                    </button>
                @else
                    <a href="{{ route('dashboard') }}" class="btn-ghost justify-center py-3 text-[0.875rem] sm:flex-1">Kembali ke Dashboard</a>
                @endif
            </div>
        </form>
    </div>
</x-layouts.app>
