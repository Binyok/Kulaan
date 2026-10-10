<x-layouts.guest>
    <div class="mb-7">
        <h1 class="font-display font-semibold text-[1.625rem] text-brand-dark tracking-tight text-balance">Lupa Kata Sandi?</h1>
        <p class="text-[0.8125rem] text-brand-slate mt-1.5">Masukkan alamat email Anda dan kami akan mengirimkan tautan untuk mengatur ulang kata sandi Anda.</p>
    </div>

    @if (session('status'))
        <div class="mb-5 text-[0.8125rem] font-medium text-brand-emerald bg-brand-mint p-3.5 rounded-xl" role="status">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="field-label">Alamat email terdaftar</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus class="input-field">
            @error('email')
                <p class="text-[0.75rem] text-brand-brick mt-1.5" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn-primary w-full justify-center py-3 text-[0.875rem]">
            Kirim Tautan Reset Sandi
        </button>

        <div class="text-center pt-5 border-t border-black/[0.04]">
            <p class="text-[0.8125rem] text-brand-slate">
                Ingat kata sandi Anda?
                <a href="{{ route('login') }}" class="text-brand-primary font-semibold hover:underline underline-offset-2 decoration-brand-primary/30">Kembali ke Login</a>
            </p>
        </div>
    </form>
</x-layouts.guest>
