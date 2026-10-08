<x-layouts.guest>
    <div class="mb-7">
        <h1 class="text-[1.375rem] font-bold text-brand-dark tracking-tight">Atur Ulang Kata Sandi</h1>
        <p class="text-[0.8125rem] text-brand-slate mt-1.5">Masukkan kata sandi baru untuk akun Anda.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5" x-data="{ showPass: false }">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Alamat Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus class="input-field">
            @error('email')
                <p class="text-[0.75rem] text-brand-brick mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Kata Sandi Baru</label>
            <div class="relative">
                <input id="password" :type="showPass ? 'text' : 'password'" name="password" required class="input-field pr-10">
                <button type="button" @click="showPass = !showPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-brand-slate hover:text-brand-dark text-xs font-semibold cursor-pointer">
                    <span x-text="showPass ? 'Sembunyikan' : 'Lihat'"></span>
                </button>
            </div>
            @error('password')
                <p class="text-[0.75rem] text-brand-brick mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-[0.8125rem] font-medium text-brand-dark mb-1.5">Konfirmasi Kata Sandi Baru</label>
            <input id="password_confirmation" :type="showPass ? 'text' : 'password'" name="password_confirmation" required class="input-field">
        </div>

        <button type="submit" class="btn-primary w-full justify-center py-3 text-[0.875rem]">
            Perbarui Kata Sandi
        </button>
    </form>
</x-layouts.guest>
