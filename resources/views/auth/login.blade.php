<x-layouts.guest>
    <div class="mb-7">
        <h1 class="text-[1.375rem] font-bold text-brand-dark tracking-tight">Masuk ke Akun Anda</h1>
        <p class="text-[0.8125rem] text-brand-slate mt-1.5">Silakan masuk menggunakan akun Google atau kredensial terdaftar.</p>
    </div>

    @if (session('status'))
        <div class="mb-5 text-[0.8125rem] font-medium text-brand-emerald bg-brand-mint p-3.5 rounded-xl border border-brand-primary/15">
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-5 text-[0.8125rem] font-medium text-brand-brick bg-red-50 p-3.5 rounded-xl border border-brand-brick/20">
            {{ session('error') }}
        </div>
    @endif

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
        <span class="bg-white px-3 text-[0.6875rem] font-semibold text-brand-slate uppercase tracking-wider absolute">atau</span>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-5" x-data="{ showPassword: false }">
        @csrf

        <div>
            <label for="email" class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Alamat Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="input-field">
            @error('email')
                <p class="text-[0.75rem] text-brand-brick mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <div class="flex justify-between items-center mb-1.5">
                <label for="password" class="block text-[0.8125rem] font-medium text-brand-dark">Kata Sandi</label>
                <a href="{{ route('password.request') }}" class="text-xs text-brand-primary font-semibold hover:underline">
                    Lupa Sandi?
                </a>
            </div>
            <div class="relative">
                <input id="password" :type="showPassword ? 'text' : 'password'" name="password" required class="input-field pr-12">
                <button type="button" @click="showPassword = !showPassword" 
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-brand-slate hover:text-brand-dark text-xs font-semibold cursor-pointer p-1">
                    <span x-text="showPassword ? 'Tutup' : 'Lihat'"></span>
                </button>
            </div>
            @error('password')
                <p class="text-[0.75rem] text-brand-brick mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center">
            <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="checkbox" name="remember" class="w-4 h-4 rounded-md border-brand-soft-grey text-brand-primary focus:ring-brand-primary/30 focus:ring-offset-0">
                <span class="text-[0.8125rem] text-brand-slate">Ingat Saya</span>
            </label>
        </div>

        <button type="submit" class="btn-primary w-full justify-center py-3 text-[0.875rem]">
            Masuk
        </button>

        <div class="text-center pt-5 border-t border-black/[0.04]">
            <p class="text-[0.8125rem] text-brand-slate">
                Belum memiliki akun?
                <a href="{{ route('register') }}" class="text-brand-primary font-semibold hover:underline underline-offset-2 decoration-brand-primary/30">Daftar Akun Baru</a>
            </p>
        </div>
    </form>
</x-layouts.guest>
