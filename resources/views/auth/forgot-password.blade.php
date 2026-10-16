<x-layouts.guest>
    <div>
        <div class="w-12 h-12 rounded-2xl bg-brass-soft flex items-center justify-center mb-4 lg:mb-5">
            <svg class="w-6 h-6 text-brass" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z"/></svg>
        </div>
        <h1 class="font-extrabold text-[1.35rem] lg:text-[1.625rem] text-ink tracking-tight">Lupa Kata Sandi?</h1>
        <p class="text-[0.8125rem] text-muted mt-1.5">Masukkan alamat email Anda dan kami akan mengirimkan tautan untuk mengatur ulang kata sandi Anda.</p>

        @if (session('status'))
            <div class="mt-4 text-xs font-bold text-forest bg-forest/10 p-3.5 rounded-2xl border border-forest/20" role="status">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4 lg:space-y-5 mt-5 lg:mt-6">
            @csrf

            <div>
                <label class="field-label" for="email">Alamat email terdaftar</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="nama@usaha.id" class="input-field">
                @error('email')
                    <p class="text-xs text-danger font-semibold mt-1.5" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full bg-forest hover:bg-forest-dark text-white font-bold rounded-2xl py-3.5 text-sm transition-colors cursor-pointer">
                Kirim Tautan Reset Sandi
            </button>

            <div class="text-center pt-4 lg:pt-5 border-t border-black/[0.04]">
                <p class="text-[0.8125rem] text-muted">Ingat kata sandi Anda? <a href="{{ route('login') }}" class="text-forest font-bold hover:underline">Kembali ke Login</a></p>
            </div>
        </form>
    </div>
</x-layouts.guest>
