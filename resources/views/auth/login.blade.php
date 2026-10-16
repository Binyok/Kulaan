<x-layouts.guest>
    <div>
        <h1 class="font-extrabold text-[1.35rem] lg:text-[1.625rem] text-ink tracking-tight">Masuk ke Akun Anda</h1>
        <p class="text-[0.8125rem] text-muted mt-1.5">Silakan masuk menggunakan akun Google atau kredensial terdaftar.</p>

        @if (session('status'))
            <div class="mt-4 text-xs font-bold text-forest bg-forest/10 p-3.5 rounded-2xl border border-forest/20" role="status">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mt-4 text-xs font-bold text-danger bg-danger/10 p-3.5 rounded-2xl border border-danger/20" role="alert">
                {{ session('error') }}
            </div>
        @endif

        <a href="{{ route('auth.google') }}" class="mt-5 lg:mt-6 w-full flex items-center justify-center gap-2.5 border border-black/10 hover:border-black/20 hover:bg-black/[0.02] rounded-2xl py-3 text-sm font-bold text-ink shadow-sm transition-colors">
            <svg class="w-5 h-5" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
            </svg>
            <span>Lanjutkan dengan Google</span>
        </a>

        <div class="relative flex items-center justify-center my-5 lg:my-6">
            <div class="border-t border-black/[0.06] w-full"></div>
            <span class="bg-white px-3 text-[0.6875rem] font-bold text-muted uppercase tracking-wider absolute">atau</span>
        </div>

        <form method="POST" action="{{ route('login') }}" class="space-y-4 lg:space-y-5" x-data="{ showPassword: false }">
            @csrf

            <div>
                <label class="field-label" for="email">Alamat email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="nama@usaha.id" class="input-field">
                @error('email')
                    <p class="text-xs text-danger font-semibold mt-1.5" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <div class="flex justify-between items-center mb-1.5">
                    <label class="field-label !mb-0" for="password">Kata sandi</label>
                    <a href="{{ route('password.request') }}" class="text-xs text-forest font-bold hover:underline shrink-0">Lupa Sandi?</a>
                </div>
                <div class="relative">
                    <input id="password" :type="showPassword ? 'text' : 'password'" name="password" required autocomplete="current-password" placeholder="••••••••" class="input-field pr-12">
                    <button type="button" @click="showPassword = !showPassword"
                        class="absolute right-1 top-1/2 -translate-y-1/2 w-10 h-10 inline-flex items-center justify-center rounded-xl text-muted hover:text-ink hover:bg-black/[0.04]"
                        :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                        <svg x-show="!showPassword" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                        <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                    </button>
                </div>
                @error('password')
                    <p class="text-xs text-danger font-semibold mt-1.5" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-2.5 cursor-pointer min-h-11">
                <input type="checkbox" name="remember" class="w-4 h-4 rounded border-line accent-[#2D6A4F]">
                <span class="text-[0.8125rem] text-muted">Ingat Saya</span>
            </label>

            <button type="submit" class="w-full bg-forest hover:bg-forest-dark text-white font-bold rounded-2xl py-3.5 text-sm transition-colors cursor-pointer">
                Masuk
            </button>

            <div class="text-center pt-4 lg:pt-5 border-t border-black/[0.04]">
                <p class="text-[0.8125rem] text-muted">Belum memiliki akun? <a href="{{ route('register') }}" class="text-forest font-bold hover:underline">Daftar Akun Baru</a></p>
            </div>
        </form>
    </div>
</x-layouts.guest>
