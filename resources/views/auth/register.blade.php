<x-layouts.guest>
    <div class="mb-7">
        <h1 class="text-[1.375rem] font-bold text-brand-dark tracking-tight">Pendaftaran Akun Kula'an</h1>
        <p class="text-[0.8125rem] text-brand-slate mt-1.5">Pilih peran dan lengkapi data usaha Anda.</p>
    </div>

    <!-- Google OAuth Button -->
    <div class="mb-6">
        <a href="{{ route('auth.google') }}" class="btn-ghost w-full justify-center py-3 text-[0.875rem] font-semibold border-black/[0.1] hover:border-black/20 hover:bg-black/[0.02] shadow-sm">
            <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
            </svg>
            <span>Continue with Google</span>
        </a>
    </div>

    <div class="relative flex items-center justify-center my-6">
        <div class="border-t border-black/[0.06] w-full"></div>
        <span class="bg-white px-3 text-[0.6875rem] font-semibold text-brand-slate uppercase tracking-wider absolute">atau daftar manual</span>
    </div>

    <form method="POST" action="{{ route('register') }}" x-data="{ role: '{{ old('role', 'umkm') }}', showPass: false }" class="space-y-5">
        @csrf

        <div>
            <label class="block text-[0.8125rem] font-medium text-brand-dark mb-2.5">Daftar Sebagai</label>
            <div class="grid grid-cols-3 gap-2">
                @foreach (['umkm' => 'Pemilik UMKM', 'distributor' => 'Distributor', 'kurir' => 'Kurir'] as $value => $label)
                    <label :class="role === '{{ $value }}' ? 'border-brand-primary bg-brand-mint/60 text-brand-primary ring-1 ring-brand-primary/20' : 'border-black/[0.06] bg-white text-brand-slate hover:border-black/[0.12]'"
                        class="border rounded-xl p-2.5 text-center cursor-pointer font-semibold text-[0.6875rem] transition-all duration-300 ease-[cubic-bezier(0.32,0.72,0,1)]">
                        <input type="radio" name="role" value="{{ $value }}" x-model="role" class="sr-only">
                        {{ $label }}
                    </label>
                @endforeach
            </div>
            @error('role')
                <p class="text-[0.75rem] text-brand-brick mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="name" class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">
                <span x-text="role === 'umkm' ? 'Nama Usaha / Pemilik' : (role === 'distributor' ? 'Nama Perusahaan' : 'Nama Lengkap')"></span>
            </label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required class="input-field">
            @error('name')
                <p class="text-[0.75rem] text-brand-brick mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <template x-if="role === 'umkm'">
            <div>
                <label for="business_type" class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Jenis Usaha</label>
                <select id="business_type" name="business_type" class="input-field bg-white">
                    <option value="Kuliner">Kuliner & Makanan</option>
                    <option value="Minuman">Minuman & Kafe</option>
                    <option value="Sembako">Toko Kelontong / Sembako</option>
                    <option value="Kerajinan">Kerajinan & Olahan</option>
                </select>
            </div>
        </template>

        <template x-if="role === 'kurir'">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="vehicle_type" class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Jenis Kendaraan</label>
                    <select id="vehicle_type" name="vehicle_type" class="input-field bg-white">
                        <option value="Motor">Motor</option>
                        <option value="Mobil Box">Mobil Box</option>
                        <option value="Pickup">Pickup</option>
                    </select>
                </div>
                <div>
                    <label for="vehicle_number" class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Plat Nomor</label>
                    <input id="vehicle_number" type="text" name="vehicle_number" placeholder="DA 1234 AB" class="input-field">
                </div>
            </div>
        </template>

        <div>
            <label for="email" class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Alamat Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required class="input-field">
            @error('email')
                <p class="text-[0.75rem] text-brand-brick mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="phone" class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Nomor WhatsApp / Telepon</label>
            <input id="phone" type="text" name="phone" value="{{ old('phone') }}" placeholder="081234567890" required class="input-field">
            @error('phone')
                <p class="text-[0.75rem] text-brand-brick mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="district" class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Kecamatan</label>
                <select id="district" name="district" required class="input-field bg-white">
                    <option value="Banjarmasin Utara">Banjarmasin Utara</option>
                    <option value="Banjarmasin Barat">Banjarmasin Barat</option>
                    <option value="Banjarmasin Timur">Banjarmasin Timur</option>
                    <option value="Banjarmasin Selatan">Banjarmasin Selatan</option>
                    <option value="Banjarmasin Tengah">Banjarmasin Tengah</option>
                </select>
            </div>
            <div>
                <label class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Kota</label>
                <input type="text" value="Banjarmasin" readonly class="input-field bg-brand-cream/50 text-brand-slate">
            </div>
        </div>

        <div>
            <label for="address" class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Alamat Lengkap</label>
            <textarea id="address" name="address" rows="2" required class="input-field">{{ old('address') }}</textarea>
            @error('address')
                <p class="text-[0.75rem] text-brand-brick mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="password" class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Kata Sandi</label>
                <div class="relative">
                    <input id="password" :type="showPass ? 'text' : 'password'" name="password" required class="input-field pr-10">
                    <button type="button" @click="showPass = !showPass" 
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-brand-slate hover:text-brand-dark text-[11px] font-semibold cursor-pointer">
                        <span x-text="showPass ? 'Tutup' : 'Lihat'"></span>
                    </button>
                </div>
                @error('password')
                    <p class="text-[0.75rem] text-brand-brick mt-1.5">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password_confirmation" class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Konfirmasi</label>
                <input id="password_confirmation" :type="showPass ? 'text' : 'password'" name="password_confirmation" required class="input-field">
            </div>
        </div>

        <button type="submit" class="btn-primary w-full justify-center py-3 text-[0.875rem] mt-2">
            Daftar Sekarang
        </button>

        <div class="text-center pt-5 border-t border-black/[0.04]">
            <p class="text-[0.8125rem] text-brand-slate">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="text-brand-primary font-semibold hover:underline underline-offset-2 decoration-brand-primary/30">Masuk</a>
            </p>
        </div>
    </form>
</x-layouts.guest>
