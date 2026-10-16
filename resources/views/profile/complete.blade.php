<x-layouts.app title="Pengaturan Profil Akun">
    {{-- Header Banner --}}
    <div class="-mx-3 sm:-mx-6 lg:-mx-8 -mt-6 sm:-mt-8 mb-6 sm:mb-8 bg-gradient-to-br from-brand-forest via-brand-dark to-brand-primary text-white py-10 sm:py-12 px-4 sm:px-8 shadow-sm">
        <div class="max-w-4xl mx-auto flex flex-col sm:flex-row items-center gap-6">
            <div class="w-20 h-20 rounded-2xl bg-white/10 backdrop-blur border border-white/20 flex items-center justify-center shrink-0 shadow-inner">
                <span class="font-display font-extrabold text-3xl text-brand-mint">
                    {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                </span>
            </div>
            <div class="text-center sm:text-left flex-1 min-w-0">
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mb-1.5">
                    <span class="px-3 py-0.5 rounded-full text-[0.6875rem] font-bold bg-brand-mint/20 text-brand-mint border border-brand-mint/30 uppercase tracking-wider">
                        {{ $isUmkm ? 'Pemilik UMKM' : ($isDistributor ? 'Distributor' : ($isCourier ? 'Mitra Kurir' : 'Pengguna')) }}
                    </span>
                    @if (!blank($user->phone) && !blank($user->address))
                        <span class="px-2.5 py-0.5 rounded-full text-[0.625rem] font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            ✓ Profil Terverifikasi
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[0.625rem] font-semibold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                            ! Belum Lengkap
                        </span>
                    @endif
                </div>
                <h1 class="font-display font-bold text-2xl sm:text-3xl text-white truncate">
                    {{ $user->name }}
                </h1>
                <p class="text-xs sm:text-sm text-white/70 mt-1">
                    {{ $user->email }} {{ $user->phone ? '• ' . $user->phone : '' }}
                </p>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto space-y-6">
        @if (session('info'))
            <div class="p-4 rounded-2xl bg-brand-ochre-soft text-brand-brass border border-brand-accent/30 text-sm font-medium flex items-center gap-3 shadow-xs">
                <svg class="w-5 h-5 shrink-0 text-brand-brass" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 text-emerald-800 border border-emerald-200 text-sm font-medium flex items-center gap-3 shadow-xs">
                <svg class="w-5 h-5 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('profile.complete.update') }}" class="space-y-6" x-data="{ showPass: false }">
            @csrf

            {{-- 1. Data Akun & Kontak --}}
            <section class="bg-white rounded-2xl border border-brand-line p-5 sm:p-7 shadow-xs">
                <div class="flex items-center gap-3 pb-4 mb-5 border-b border-brand-line/60">
                    <div class="w-10 h-10 rounded-xl bg-brand-mint/60 text-brand-primary flex items-center justify-center shrink-0 border border-brand-primary/10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-brand-dark">Informasi Akun & Kontak</h2>
                        <p class="text-xs text-brand-slate">Identitas diri dan kontak utama untuk komunikasi & pemberitahuan pesanan.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="name" class="block text-xs font-bold text-brand-dark uppercase tracking-wider mb-1.5">Nama Lengkap <span class="text-brand-brick">*</span></label>
                        <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required class="input-field">
                        @error('name')
                            <p class="text-xs text-brand-brick mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-bold text-brand-dark uppercase tracking-wider mb-1.5">Alamat Email <span class="text-brand-brick">*</span></label>
                        <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required class="input-field">
                        @error('email')
                            <p class="text-xs text-brand-brick mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="phone" class="block text-xs font-bold text-brand-dark uppercase tracking-wider mb-1.5">Nomor WhatsApp / HP <span class="text-brand-brick">*</span></label>
                        <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="081234567890" required class="input-field">
                        <p class="text-[0.6875rem] text-brand-slate mt-1">Nomor ini digunakan untuk kirim notifikasi pesanan & koordinasi kurir/distributor.</p>
                        @error('phone')
                            <p class="text-xs text-brand-brick mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- 2. Alamat & Wilayah Lengkap --}}
            <section class="bg-white rounded-2xl border border-brand-line p-5 sm:p-7 shadow-xs">
                <div class="flex items-center gap-3 pb-4 mb-5 border-b border-brand-line/60">
                    <div class="w-10 h-10 rounded-xl bg-brand-mint/60 text-brand-primary flex items-center justify-center shrink-0 border border-brand-primary/10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-brand-dark">Alamat & Wilayah Operasional</h2>
                        <p class="text-xs text-brand-slate">Penting untuk mencocokkan grup pengadaan Kula'an, estimasi ongkir, dan pengiriman.</p>
                    </div>
                </div>

                <div class="space-y-5">
                    <div>
                        <label for="address" class="block text-xs font-bold text-brand-dark uppercase tracking-wider mb-1.5">Alamat Jalan / Bangunan Lengkap <span class="text-brand-brick">*</span></label>
                        <textarea id="address" name="address" rows="3" required placeholder="Contoh: Jl. Ahmad Yani No. 45, RT 02/RW 03, dekat Masjid Agam" class="input-field">{{ old('address', $user->address) }}</textarea>
                        @error('address')
                            <p class="text-xs text-brand-brick mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-brand-dark uppercase tracking-wider mb-2">Pilih Wilayah Administrasi</label>
                        <x-region-selects :values="[
                            'province' => old('province', $umkm?->province ?? $distributor?->province),
                            'city' => old('city', $umkm?->city ?? $distributor?->city),
                            'district' => old('district', $umkm?->district ?? $distributor?->district),
                            'village' => old('village', $umkm?->village ?? $distributor?->village),
                        ]" />
                        @error('province')
                            <p class="text-xs text-brand-brick mt-1.5">{{ $message }}</p>
                        @enderror
                        @error('city')
                            <p class="text-xs text-brand-brick mt-1.5">{{ $message }}</p>
                        @enderror
                        @error('district')
                            <p class="text-xs text-brand-brick mt-1.5">{{ $message }}</p>
                        @enderror
                        @error('village')
                            <p class="text-xs text-brand-brick mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- 3. Data Usaha / Perusahaan / Kendaraan --}}
            @if ($isUmkm || $isDistributor || $isCourier)
                <section class="bg-white rounded-2xl border border-brand-line p-5 sm:p-7 shadow-xs">
                    <div class="flex items-center gap-3 pb-4 mb-5 border-b border-brand-line/60">
                        <div class="w-10 h-10 rounded-xl bg-brand-mint/60 text-brand-primary flex items-center justify-center shrink-0 border border-brand-primary/10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-brand-dark">
                                {{ $isUmkm ? 'Detail Usaha UMKM' : ($isDistributor ? 'Detail Perusahaan Distributor' : 'Detail Armada Kurir') }}
                            </h2>
                            <p class="text-xs text-brand-slate">Informasi profil spesifik sesuai dengan peran Anda di Kula'an.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        @if ($isUmkm)
                            <div>
                                <label for="business_name" class="block text-xs font-bold text-brand-dark uppercase tracking-wider mb-1.5">Nama Usaha UMKM</label>
                                <input id="business_name" type="text" name="business_name"
                                    value="{{ old('business_name', $umkm?->business_name) }}"
                                    class="input-field" placeholder="Contoh: Warung Berkah Kuliner">
                                @error('business_name')
                                    <p class="text-xs text-brand-brick mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="business_type" class="block text-xs font-bold text-brand-dark uppercase tracking-wider mb-1.5">Kategori / Jenis Usaha</label>
                                <select id="business_type" name="business_type" class="input-field bg-white">
                                    @foreach (['Kuliner', 'Minuman', 'Sembako', 'Kerajinan', 'Jasa', 'Lainnya'] as $type)
                                        <option value="{{ $type }}" @selected(old('business_type', $umkm?->business_type) === $type)>{{ $type }}</option>
                                    @endforeach
                                </select>
                                @error('business_type')
                                    <p class="text-xs text-brand-brick mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>
                        @elseif ($isDistributor)
                            <div>
                                <label for="business_name" class="block text-xs font-bold text-brand-dark uppercase tracking-wider mb-1.5">Nama Perusahaan / PT / CV</label>
                                <input id="business_name" type="text" name="business_name"
                                    value="{{ old('business_name', $distributor?->company_name) }}"
                                    class="input-field" placeholder="Contoh: UD Kalimantan Agro Pangan">
                                @error('business_name')
                                    <p class="text-xs text-brand-brick mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="delivery_lead_time_days" class="block text-xs font-bold text-brand-dark uppercase tracking-wider mb-1.5">Estimasi Waktu Kirim (Hari)</label>
                                <div class="relative">
                                    <input id="delivery_lead_time_days" type="number" name="delivery_lead_time_days"
                                        value="{{ old('delivery_lead_time_days', $distributor?->delivery_lead_time_days ?? 2) }}"
                                        min="1" max="30" class="input-field pr-16">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-brand-slate">Hari</span>
                                </div>
                                @error('delivery_lead_time_days')
                                    <p class="text-xs text-brand-brick mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>
                        @elseif ($isCourier)
                            <div>
                                <label for="vehicle_type" class="block text-xs font-bold text-brand-dark uppercase tracking-wider mb-1.5">Tipe Kendaraan</label>
                                <select id="vehicle_type" name="vehicle_type" class="input-field bg-white">
                                    @foreach (['Motor', 'Mobil Pick-Up', 'Mobil Box', 'Truk Engkel', 'Lainnya'] as $vType)
                                        <option value="{{ $vType }}" @selected(old('vehicle_type', $courier?->vehicle_type) === $vType)>{{ $vType }}</option>
                                    @endforeach
                                </select>
                                @error('vehicle_type')
                                    <p class="text-xs text-brand-brick mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="vehicle_number" class="block text-xs font-bold text-brand-dark uppercase tracking-wider mb-1.5">Nomor Polisi Kendaraan</label>
                                <input id="vehicle_number" type="text" name="vehicle_number"
                                    value="{{ old('vehicle_number', $courier?->vehicle_number) }}"
                                    class="input-field uppercase" placeholder="Contoh: DA 1234 ABC">
                                @error('vehicle_number')
                                    <p class="text-xs text-brand-brick mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                    </div>
                </section>
            @endif

            {{-- 4. Pengaturan Kata Sandi --}}
            <section class="bg-white rounded-2xl border border-brand-line p-5 sm:p-7 shadow-xs">
                <div class="flex items-center gap-3 pb-4 mb-5 border-b border-brand-line/60">
                    <div class="w-10 h-10 rounded-xl bg-brand-mint/60 text-brand-primary flex items-center justify-center shrink-0 border border-brand-primary/10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-brand-dark">Atur / Ubah Kata Sandi</h2>
                        <p class="text-xs text-brand-slate">Kosongkan jika Anda tidak ingin mengubah kata sandi saat ini.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="password" class="block text-xs font-bold text-brand-dark uppercase tracking-wider mb-1.5">Kata Sandi Baru</label>
                        <div class="relative">
                            <input id="password" :type="showPass ? 'text' : 'password'" name="password" class="input-field pr-14" autocomplete="new-password">
                            <button type="button" @click="showPass = !showPass"
                                class="absolute right-1 top-1/2 -translate-y-1/2 min-h-10 px-3 flex items-center justify-center text-brand-slate hover:text-brand-dark text-xs font-bold cursor-pointer">
                                <span x-text="showPass ? 'Sembunyi' : 'Lihat'"></span>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-xs text-brand-brick mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold text-brand-dark uppercase tracking-wider mb-1.5">Ulangi Kata Sandi Baru</label>
                        <input id="password_confirmation" :type="showPass ? 'text' : 'password'" name="password_confirmation" class="input-field" autocomplete="new-password">
                    </div>
                </div>
            </section>

            {{-- Bottom Sticky Bar --}}
            <div class="sticky bottom-6 z-20 bg-white/90 backdrop-blur-md rounded-2xl border border-brand-line p-4 shadow-lg flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="text-xs text-brand-slate hidden sm:block">
                    Pastikan seluruh data wajib beranda Anda telah terisi dengan benar.
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                    <a href="{{ route('dashboard') }}" class="btn-ghost py-3 px-5 text-xs font-bold w-full sm:w-auto text-center">
                        Batal / Kembali
                    </a>
                    <button type="submit" class="btn-primary py-3 px-6 text-xs font-bold w-full sm:w-auto shadow-md">
                        Simpan Perubahan Profil
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.app>
