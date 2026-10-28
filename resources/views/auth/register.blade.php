<x-layouts.guest wide>
    <div>
        <h1 class="font-extrabold text-[1.35rem] lg:text-[1.625rem] text-ink tracking-tight">Pendaftaran Akun Kula'an</h1>
        <p class="text-[0.8125rem] text-muted mt-1.5">Pilih peran, buat akun, lalu lengkapi data usaha di langkah berikutnya.</p>

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
            <span class="bg-white px-3 text-[0.6875rem] font-bold text-muted uppercase tracking-wider absolute">atau daftar manual</span>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-4 lg:space-y-5"
            x-data="{
                role: '{{ old('role', 'umkm') }}',
                showPass: false,
                showConfirm: false,
                labels: { umkm: 'Nama usaha / pemilik', distributor: 'Nama perusahaan', kurir: 'Nama lengkap' }
            }">
            @csrf

            <div>
                <span class="field-label">Daftar sebagai</span>
                <div class="grid grid-cols-3 gap-2 lg:gap-2.5">
                    <label class="role-card" :class="role === 'umkm' ? 'active' : ''">
                        <input type="radio" name="role" value="umkm" x-model="role">
                        <svg class="w-6 h-6 lg:w-7 lg:h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0h18.1m-9.54 0v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H5.36"/></svg>
                        Pemilik UMKM
                    </label>
                    <label class="role-card" :class="role === 'distributor' ? 'active' : ''">
                        <input type="radio" name="role" value="distributor" x-model="role">
                        <svg class="w-6 h-6 lg:w-7 lg:h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.25 7.5l-.63 10.63a2.25 2.25 0 0 1-2.25 2.12H6.63a2.25 2.25 0 0 1-2.25-2.12L3.75 7.5M10 11.25h4M3.38 7.5h17.25c.62 0 1.12-.5 1.12-1.12v-1.5C21.75 4.26 21.25 3.75 20.63 3.75H3.38c-.62 0-1.13.5-1.13 1.12v1.5c0 .63.5 1.13 1.12 1.13Z"/></svg>
                        Distributor
                    </label>
                    <label class="role-card" :class="role === 'kurir' ? 'active' : ''">
                        <input type="radio" name="role" value="kurir" x-model="role">
                        <svg class="w-6 h-6 lg:w-7 lg:h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.9 17.9 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                        Kurir
                    </label>
                </div>
                @error('role')
                    <p class="text-xs text-danger font-semibold mt-1.5" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-4 gap-y-4 lg:gap-y-5">
                <div>
                    <label class="field-label" for="name" x-text="labels[role]">Nama usaha / pemilik</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="cth: Warung Berkah Jaya" class="input-field">
                    @error('name')
                        <p class="text-xs text-danger font-semibold mt-1.5" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="field-label" for="email">Alamat email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="nama@usaha.id" class="input-field">
                    @error('email')
                        <p class="text-xs text-danger font-semibold mt-1.5" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="field-label" for="password">Kata sandi</label>
                    <div class="relative">
                        <input id="password" :type="showPass ? 'text' : 'password'" name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter" class="input-field pr-12">
                        <button type="button" @click="showPass = !showPass"
                            class="absolute right-1 top-1/2 -translate-y-1/2 w-10 h-10 inline-flex items-center justify-center rounded-xl text-muted hover:text-ink hover:bg-black/[0.04]"
                            :aria-label="showPass ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                            <svg x-show="!showPass" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                            <svg x-show="showPass" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                        </button>
                    </div>
                    <p class="field-hint">Minimal 8 karakter.</p>
                    @error('password')
                        <p class="text-xs text-danger font-semibold mt-1.5" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="field-label" for="password_confirmation">Konfirmasi kata sandi</label>
                    <div class="relative">
                        <input id="password_confirmation" :type="showConfirm ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi kata sandi" class="input-field pr-12">
                        <button type="button" @click="showConfirm = !showConfirm"
                            class="absolute right-1 top-1/2 -translate-y-1/2 w-10 h-10 inline-flex items-center justify-center rounded-xl text-muted hover:text-ink hover:bg-black/[0.04]"
                            :aria-label="showConfirm ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                            <svg x-show="!showConfirm" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                            <svg x-show="showConfirm" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                        </button>
                    </div>
                    @error('password_confirmation')
                        <p class="text-xs text-danger font-semibold mt-1.5" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <button type="submit" class="w-full bg-forest hover:bg-forest-dark text-white font-bold rounded-2xl py-3.5 text-sm transition-colors cursor-pointer !mt-6">
                Daftar Sekarang
            </button>

            <div class="text-center pt-4 lg:pt-5 border-t border-black/[0.04]">
                <p class="text-[0.8125rem] text-muted">Sudah punya akun? <a href="{{ route('login') }}" class="text-forest font-bold hover:underline">Masuk</a></p>
            </div>
        </form>
    </div>
</x-layouts.guest>
