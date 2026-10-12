<x-layouts.guest>
    <div class="mb-7">
        <h1 class="font-display font-semibold text-[1.625rem] text-brand-dark tracking-tight text-balance">Atur Ulang Kata Sandi</h1>
        <p class="text-[0.8125rem] text-brand-slate mt-1.5">Masukkan kata sandi baru untuk akun Anda.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5" x-data="{ showPass: false }" data-loading>
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="field-label">Alamat email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autocomplete="email" autofocus class="input-field">
            @error('email')
                <p class="text-[0.75rem] text-brand-brick mt-1.5" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="field-label">Kata sandi baru</label>
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
            <label for="password_confirmation" class="field-label">Konfirmasi kata sandi baru</label>
            <input id="password_confirmation" :type="showPass ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password" class="input-field">
        </div>

        <button type="submit" class="btn-primary w-full justify-center py-3 text-[0.875rem]">
            Perbarui Kata Sandi
        </button>
    </form>
</x-layouts.guest>
