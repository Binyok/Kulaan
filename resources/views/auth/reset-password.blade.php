<x-layouts.guest>
    <div class="mb-7">
        <h1 class="font-display font-semibold text-[1.625rem] text-brand-dark tracking-tight text-balance">Atur Ulang Kata Sandi</h1>
        <p class="text-[0.8125rem] text-brand-slate mt-1.5">Masukkan kata sandi baru untuk akun Anda.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5" x-data="{ showPass: false }">
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
                    class="absolute right-1.5 top-1/2 -translate-y-1/2 min-h-11 px-2 flex items-center text-brand-slate hover:text-brand-dark text-xs font-semibold cursor-pointer"
                    :aria-label="showPass ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                    <span x-text="showPass ? 'Sembunyikan' : 'Lihat'"></span>
                </button>
            </div>
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
