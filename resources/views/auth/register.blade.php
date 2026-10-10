<x-layouts.guest>
    <div class="mb-7">
        <h1 class="font-display font-semibold text-[1.625rem] text-brand-dark tracking-tight text-balance">Pendaftaran Akun Kula'an</h1>
        <p class="text-[0.8125rem] text-brand-slate mt-1.5">Pilih peran, buat akun, lalu lengkapi data usaha di langkah berikutnya.</p>
    </div>

    <!-- Google OAuth Button -->
    <div class="mb-6">
        <a href="{{ route('auth.google') }}" class="btn-ghost w-full justify-center py-3 text-[0.875rem] font-semibold border-black/[0.1] hover:border-black/20 hover:bg-black/[0.02] shadow-sm">
            <svg class="w-5 h-5" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
            </svg>
            <span>Lanjutkan dengan Google</span>
        </a>
    </div>

    <div class="relative flex items-center justify-center my-6">
        <div class="border-t border-black/[0.06] w-full"></div>
        <span class="bg-white px-3 text-[0.6875rem] font-semibold text-brand-slate uppercase tracking-wider absolute">atau daftar manual</span>
    </div>

    <form method="POST" action="{{ route('register') }}" x-data="{ role: '{{ old('role', 'umkm') }}', showPass: false }" class="space-y-5">
        @csrf

        <div>
            <span class="field-label">Daftar sebagai</span>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                @foreach (['umkm' => 'Pemilik UMKM', 'distributor' => 'Distributor', 'kurir' => 'Kurir'] as $value => $label)
                    <label :class="role === '{{ $value }}' ? 'border-brand-primary bg-brand-mint/60 text-brand-primary ring-1 ring-brand-primary/20' : 'border-black/[0.06] bg-white text-brand-slate hover:border-black/[0.12]'"
                        class="min-h-11 flex items-center justify-center border rounded-xl px-3 py-2.5 text-center cursor-pointer font-semibold text-xs transition-colors">
                        <input type="radio" name="role" value="{{ $value }}" x-model="role" class="sr-only">
                        {{ $label }}
                    </label>
                @endforeach
            </div>
            @error('role')
                <p class="text-[0.75rem] text-brand-brick mt-1.5" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="name" class="field-label">
                <span x-text="role === 'umkm' ? 'Nama usaha / pemilik' : (role === 'distributor' ? 'Nama perusahaan' : 'Nama lengkap')"></span>
            </label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name" class="input-field">
            @error('name')
                <p class="text-[0.75rem] text-brand-brick mt-1.5" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="field-label">Alamat email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="input-field">
            @error('email')
                <p class="text-[0.75rem] text-brand-brick mt-1.5" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="field-label">Kata sandi</label>
            <div class="relative">
                <input id="password" :type="showPass ? 'text' : 'password'" name="password" required autocomplete="new-password" class="input-field pr-14">
                <button type="button" @click="showPass = !showPass"
                    class="absolute right-1 top-1/2 -translate-y-1/2 w-11 h-11 inline-flex items-center justify-center rounded-xl text-brand-slate hover:text-brand-dark hover:bg-black/[0.04] transition-colors cursor-pointer"
                    :aria-label="showPass ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                    :aria-pressed="showPass.toString()">
                    <svg x-show="!showPass" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                    <svg x-show="showPass" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                </button>
            </div>
            <p class="field-hint">Minimal 8 karakter.</p>
            @error('password')
                <p class="text-[0.75rem] text-brand-brick mt-1.5" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="field-label">Konfirmasi kata sandi</label>
            <input id="password_confirmation" :type="showPass ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password" class="input-field">
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
