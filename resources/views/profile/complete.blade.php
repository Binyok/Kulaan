<x-layouts.app title="Pengaturan Profil Akun">
    @php
        $isAdmin = ! $isUmkm && ! $isDistributor && ! $isCourier;
        $greetingEyebrow = $isAdmin ? 'Kontrol Pusat,' : 'Selamat pagi,';
        $displayName = $isUmkm
            ? ($umkm?->business_name ?? $user->name)
            : ($isDistributor ? ($distributor?->company_name ?? $user->name) : $user->name);
        $greetingName = $isDistributor ? ($distributor?->company_name ?? $user->name) : $displayName;
        $initials = strtoupper(substr($user->name, 0, 2));
        $roleLabel = $isUmkm ? 'Pemilik UMKM' : ($isDistributor ? 'Distributor Mitra' : ($isCourier ? 'Kurir Armada' : 'Administrator'));
        $roleSubtitle = $isUmkm
            ? (($umkm?->city ?? 'Sidoarjo') . ', ' . ($umkm?->province ?? 'Jawa Timur'))
            : ($isDistributor
                ? (($distributor?->city ?? 'Sidoarjo') . ', ' . ($distributor?->province ?? 'Jawa Timur'))
                : ($isCourier
                    ? (($courier?->vehicle_type ?? 'Motor') . ' - ' . ($courier?->vehicle_number ?? '-'))
                    : 'Kontrol Pusat'));
    @endphp

    {{-- MOBILE --}}
    <div class="lg:hidden pb-24">
        <div class="px-4 pt-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="relative" x-data="{ menuOpen: false }">
                    <button type="button" @click="menuOpen = !menuOpen" class="w-11 h-11 rounded-2xl bg-forest text-white flex items-center justify-center font-extrabold text-sm cursor-pointer">{{ $initials }}</button>
                    <div x-show="menuOpen" @click.away="menuOpen = false" x-cloak class="absolute left-0 mt-2 w-48 bg-white border border-line rounded-2xl shadow-xl p-2 z-40 text-xs">
                        <a href="{{ route('profile.complete') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-ink hover:bg-cream font-medium">Profil Saya</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2 px-3 py-2.5 rounded-xl text-danger hover:bg-danger/10 font-bold transition text-left cursor-pointer">Keluar</button>
                        </form>
                    </div>
                </div>
                <div>
                    <p class="text-[11px] text-muted">{{ $greetingEyebrow }}</p>
                    <p class="text-[15px] font-extrabold leading-tight">{{ $greetingName }}</p>
                </div>
            </div>
            <div class="relative" x-data="{ notifOpen: false }">
                <button type="button" @click="notifOpen = !notifOpen" class="w-10 h-10 rounded-2xl bg-white border border-line flex items-center justify-center text-ink relative cursor-pointer">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14.86 17.08a23.85 23.85 0 0 0 5.45-1.31A8.97 8.97 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.97 8.97 0 0 1-2.31 6.02c1.73.64 3.56 1.09 5.45 1.31m5.72 0a24.26 24.26 0 0 1-5.72 0m5.72 0a3 3 0 1 1-5.72 0"/></svg>
                </button>
                <div x-show="notifOpen" @click.away="notifOpen = false" x-cloak class="absolute right-0 mt-2 w-72 bg-white border border-line rounded-2xl shadow-xl p-4 z-40 text-left">
                    <p class="text-sm font-extrabold text-ink">Notifikasi</p>
                    <div class="mt-3 space-y-1">
                        @if ($isUmkm)
                            <a href="{{ route('umkm.orders.index') }}" class="flex items-start gap-2 rounded-xl p-2 hover:bg-cream">
                                <span class="w-2 h-2 mt-1.5 rounded-full bg-brass shrink-0"></span>
                                <span class="text-xs text-ink">Tagihan menunggu pembayaran</span>
                            </a>
                            <a href="{{ route('umkm.tracking.index') }}" class="flex items-start gap-2 rounded-xl p-2 hover:bg-cream">
                                <span class="w-2 h-2 mt-1.5 rounded-full bg-forest shrink-0"></span>
                                <span class="text-xs text-ink">Pengiriman sedang berjalan</span>
                            </a>
                        @elseif ($isDistributor)
                            <a href="{{ route('distributor.orders.index') }}" class="flex items-start gap-2 rounded-xl p-2 hover:bg-cream">
                                <span class="w-2 h-2 mt-1.5 rounded-full bg-brass shrink-0"></span>
                                <span class="text-xs text-ink">Pesanan perlu diproses</span>
                            </a>
                        @elseif ($isCourier)
                            <a href="{{ route('kurir.shipments.index') }}" class="flex items-start gap-2 rounded-xl p-2 hover:bg-cream">
                                <span class="w-2 h-2 mt-1.5 rounded-full bg-brass shrink-0"></span>
                                <span class="text-xs text-ink">Tugas menunggu</span>
                            </a>
                        @else
                            <a href="{{ route('admin.proposals.index') }}" class="flex items-start gap-2 rounded-xl p-2 hover:bg-cream">
                                <span class="w-2 h-2 mt-1.5 rounded-full bg-brass shrink-0"></span>
                                <span class="text-xs text-ink">Usulan produk menunggu review</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 px-4 pt-4 pb-1">
            <a href="{{ route('dashboard') }}" class="w-10 h-10 rounded-2xl bg-white border border-line flex items-center justify-center text-ink">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
            </a>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-brass">Profil</p>
                <h1 class="text-lg font-extrabold text-ink tracking-tight">Lengkapi Profilmu</h1>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.complete.update') }}" x-data="{ showPass: false }">
            @csrf
            <div class="px-4 mt-4 space-y-3">

                <div class="bg-white rounded-2xl border border-line p-4">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-bold text-ink">Kelengkapan profil</p>
                        <p class="text-xs font-extrabold text-forest">75%</p>
                    </div>
                    <div class="mt-2.5 h-2 rounded-full bg-cream border border-line overflow-hidden">
                        <div class="h-full w-3/4 rounded-full bg-gradient-to-r from-forest to-success"></div>
                    </div>
                    <p class="text-[11px] text-muted mt-2">Data ini dipakai buat pengiriman, tagihan, dan nyocokin kamu ke grup pengadaan kolektif yang pas.</p>
                </div>

                <section class="bg-white rounded-3xl border border-line p-4 shadow-sm">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-10 h-10 rounded-full bg-success/10 text-forest flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5"/></svg>
                        </span>
                        <h2 class="text-sm font-extrabold text-ink">Data akun</h2>
                    </div>
                    <div class="space-y-3.5">
                        <div>
                            <label for="name-mobile" class="block text-xs font-semibold text-ink mb-1.5">Nama lengkap</label>
                            <input id="name-mobile" type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full bg-cream/50 border border-line rounded-2xl px-4 py-3 text-sm outline-none focus:border-forest">
                            @error('name')
                                <p class="text-xs text-danger font-semibold mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="email-mobile" class="block text-xs font-semibold text-ink mb-1.5">Alamat email</label>
                            <input id="email-mobile" type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full bg-cream/50 border border-line rounded-2xl px-4 py-3 text-sm outline-none focus:border-forest">
                            @error('email')
                                <p class="text-xs text-danger font-semibold mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="phone-mobile" class="block text-xs font-semibold text-ink mb-1.5">Nomor WhatsApp / telepon</label>
                            <input id="phone-mobile" type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="081234567890" required class="w-full bg-cream/50 border border-line rounded-2xl px-4 py-3 text-sm outline-none placeholder:text-muted/60 focus:border-forest">
                            @error('phone')
                                <p class="text-xs text-danger font-semibold mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="address-mobile" class="block text-xs font-semibold text-ink mb-1.5">Alamat lengkap</label>
                            <textarea id="address-mobile" name="address" rows="2" required placeholder="Nama jalan, nomor bangunan, RT/RW" class="w-full bg-cream/50 border border-line rounded-2xl px-4 py-3 text-sm outline-none placeholder:text-muted/60 focus:border-forest">{{ old('address', $user->address) }}</textarea>
                            @error('address')
                                <p class="text-xs text-danger font-semibold mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-ink mb-2">Wilayah administrasi</label>
                            <x-region-selects :values="[
                                'province' => old('province', $umkm?->province ?? $distributor?->province),
                                'city' => old('city', $umkm?->city ?? $distributor?->city),
                                'district' => old('district', $umkm?->district ?? $distributor?->district),
                                'village' => old('village', $umkm?->village ?? $distributor?->village),
                            ]" />
                        </div>
                    </div>
                </section>

                @if ($isUmkm || $isDistributor || $isCourier)
                    <section class="bg-white rounded-3xl border border-line p-4 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="w-10 h-10 rounded-full bg-brass-soft text-brass flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-4h6v4"/></svg>
                            </span>
                            <h2 class="text-sm font-extrabold text-ink">Data usaha</h2>
                        </div>
                        <div class="space-y-3.5">
                            @if ($isUmkm)
                                <div>
                                    <label for="business_name-mobile" class="block text-xs font-semibold text-ink mb-1.5">Nama usaha</label>
                                    <input id="business_name-mobile" type="text" name="business_name" value="{{ old('business_name', $umkm?->business_name) }}" placeholder="cth. Warung Berkah Kuliner" class="w-full bg-cream/50 border border-line rounded-2xl px-4 py-3 text-sm outline-none placeholder:text-muted/60 focus:border-forest">
                                </div>
                                <div>
                                    <label for="business_type-mobile" class="block text-xs font-semibold text-ink mb-1.5">Jenis usaha</label>
                                    <select id="business_type-mobile" name="business_type" class="w-full bg-cream/50 border border-line rounded-2xl px-4 py-3 text-sm outline-none focus:border-forest">
                                        @foreach (['Kuliner', 'Minuman', 'Sembako', 'Kerajinan', 'Jasa', 'Lainnya'] as $type)
                                            <option value="{{ $type }}" @selected(old('business_type', $umkm?->business_type) === $type)>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @elseif ($isDistributor)
                                <div>
                                    <label for="business_name-mobile" class="block text-xs font-semibold text-ink mb-1.5">Nama Perusahaan / Distributor</label>
                                    <input id="business_name-mobile" type="text" name="business_name" value="{{ old('business_name', $distributor?->company_name) }}" class="w-full bg-cream/50 border border-line rounded-2xl px-4 py-3 text-sm outline-none focus:border-forest">
                                </div>
                                <div>
                                    <label for="delivery_lead_time_days-mobile" class="block text-xs font-semibold text-ink mb-1.5">Estimasi Kirim (Hari)</label>
                                    <input id="delivery_lead_time_days-mobile" type="number" min="1" max="30" name="delivery_lead_time_days" value="{{ old('delivery_lead_time_days', $distributor?->delivery_lead_time_days ?? 2) }}" class="w-full bg-cream/50 border border-line rounded-2xl px-4 py-3 text-sm outline-none focus:border-forest">
                                </div>
                            @elseif ($isCourier)
                                <div>
                                    <label for="vehicle_type-mobile" class="block text-xs font-semibold text-ink mb-1.5">Tipe Kendaraan</label>
                                    <select id="vehicle_type-mobile" name="vehicle_type" class="w-full bg-cream/50 border border-line rounded-2xl px-4 py-3 text-sm outline-none focus:border-forest">
                                        @foreach (['Motor', 'Mobil Pick-Up', 'Mobil Box', 'Truk Engkel', 'Lainnya'] as $vType)
                                            <option value="{{ $vType }}" @selected(old('vehicle_type', $courier?->vehicle_type) === $vType)>{{ $vType }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="vehicle_number-mobile" class="block text-xs font-semibold text-ink mb-1.5">Nomor Polisi</label>
                                    <input id="vehicle_number-mobile" type="text" name="vehicle_number" value="{{ old('vehicle_number', $courier?->vehicle_number) }}" placeholder="DA 1234 ABC" class="w-full bg-cream/50 border border-line rounded-2xl px-4 py-3 text-sm uppercase outline-none placeholder:text-muted/60 focus:border-forest">
                                </div>
                            @endif
                        </div>
                    </section>
                @endif

                <section class="bg-white rounded-3xl border border-line p-4 shadow-sm">
                    <div class="flex items-center gap-3 mb-1">
                        <span class="w-10 h-10 rounded-full bg-success/10 text-forest flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                        </span>
                        <div>
                            <h2 class="text-sm font-extrabold text-ink">Atur kata sandi</h2>
                            <p class="text-[11px] text-muted">Opsional - isi kalau mau login pakai email juga.</p>
                        </div>
                    </div>
                    <div class="space-y-3.5 mt-3">
                        <div>
                            <label for="password-mobile" class="block text-xs font-semibold text-ink mb-1.5">Kata sandi baru</label>
                            <div class="relative">
                                <input id="password-mobile" :type="showPass ? 'text' : 'password'" name="password" autocomplete="new-password" class="w-full bg-cream/50 border border-line rounded-2xl px-4 py-3 pr-16 text-sm outline-none focus:border-forest">
                                <button type="button" @click="showPass = !showPass" class="absolute right-2 top-1/2 -translate-y-1/2 text-xs font-bold text-muted px-3 py-2 cursor-pointer" x-text="showPass ? 'Tutup' : 'Lihat'">Lihat</button>
                            </div>
                            @error('password')
                                <p class="text-xs text-danger font-semibold mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="password_confirmation-mobile" class="block text-xs font-semibold text-ink mb-1.5">Ulangi kata sandi</label>
                            <input id="password_confirmation-mobile" :type="showPass ? 'text' : 'password'" name="password_confirmation" autocomplete="new-password" class="w-full bg-cream/50 border border-line rounded-2xl px-4 py-3 text-sm outline-none focus:border-forest">
                        </div>
                    </div>
                </section>

                <div class="flex gap-3">
                    <a href="{{ route('dashboard') }}" class="flex-1 border border-line text-muted text-sm font-bold py-3.5 rounded-2xl bg-white flex items-center justify-center">Kembali ke Dashboard</a>
                    <button type="submit" class="flex-1 bg-forest text-white text-sm font-bold py-3.5 rounded-2xl shadow-lg shadow-forest/25 cursor-pointer">Simpan Profil</button>
                </div>
            </div>
        </form>

        <div class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-white border-t border-line px-2 pt-2 pb-5 flex items-start justify-around">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 w-14 text-muted">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m2.25 12 8.95-8.95a1.13 1.13 0 0 1 1.6 0l8.95 8.95M4.5 9.75V19.5a.75.75 0 0 0 .75.75h13.5a.75.75 0 0 0 .75-.75V9.75"/></svg>
                <span class="text-[10px] font-semibold">Beranda</span>
            </a>
            @if ($isUmkm)
                <a href="{{ route('umkm.catalog.index') }}" class="flex flex-col items-center gap-1 w-14 text-muted">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>
                    <span class="text-[10px] font-semibold">Katalog</span>
                </a>
                <a href="{{ route('umkm.cart.index') }}" class="flex flex-col items-center gap-1 w-14 text-muted">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2.25 3h1.39c.51 0 .96.34 1.09.84l.38 1.44M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.22c1.12-2.3 2.1-4.68 2.92-7.14a60.11 60.11 0 0 0-16.54-1.84M7.5 14.25 5.11 5.27M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                    <span class="text-[10px] font-semibold">Keranjang</span>
                </a>
                <a href="{{ route('umkm.orders.index') }}" class="flex flex-col items-center gap-1 w-14 text-muted">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.25 7.5l-.63 10.63a2.25 2.25 0 0 1-2.25 2.12H6.63a2.25 2.25 0 0 1-2.25-2.12L3.75 7.5M10 11.25h4M3.38 7.5h17.25c.62 0 1.12-.5 1.12-1.12v-1.5C21.75 4.26 21.25 3.75 20.63 3.75H3.38c-.62 0-1.13.5-1.13 1.12v1.5c0 .63.5 1.13 1.12 1.13Z"/></svg>
                    <span class="text-[10px] font-semibold">Pesanan</span>
                </a>
            @elseif ($isDistributor)
                <a href="{{ route('distributor.products.index') }}" class="flex flex-col items-center gap-1 w-14 text-muted">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5M12 22V12"/></svg>
                    <span class="text-[10px] font-semibold">Katalog</span>
                </a>
                <a href="{{ route('distributor.orders.index') }}" class="flex flex-col items-center gap-1 w-14 text-muted">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 17h6m-5 4h4M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/><path d="M9 8h6M9 12h4"/></svg>
                    <span class="text-[10px] font-semibold">Pesanan</span>
                </a>
            @elseif ($isCourier)
                <a href="{{ route('kurir.shipments.index') }}" class="flex flex-col items-center gap-1 w-14 text-muted">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 17h6m-5 4h4M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/><path d="M9 8h6M9 12h4"/></svg>
                    <span class="text-[10px] font-semibold">Tugas</span>
                </a>
            @else
                <a href="{{ route('admin.proposals.index') }}" class="flex flex-col items-center gap-1 w-14 text-muted">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5v14m-7-7h14"/></svg>
                    <span class="text-[10px] font-semibold">Usulan</span>
                </a>
                <a href="{{ route('admin.spk.index') }}" class="flex flex-col items-center gap-1 w-14 text-muted">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 20h18M6 16v-5m6 5V8m6 8v-9"/></svg>
                    <span class="text-[10px] font-semibold">SPK</span>
                </a>
            @endif
            <a href="{{ route('profile.complete') }}" class="flex flex-col items-center gap-1 w-14 text-forest">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17.98 18.72a6 6 0 0 0-8.46 0M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm-9.75 8.25a9 9 0 1 1 13.5 0"/></svg>
                <span class="text-[10px] font-bold">Akun</span>
            </a>
        </div>
    </div>

    {{-- DESKTOP --}}
    <div class="hidden lg:flex min-h-screen">
        <aside class="w-64 shrink-0 bg-white border-r border-line flex flex-col sticky top-0 h-screen">
            <div class="flex items-center gap-2.5 px-6 h-[72px] border-b border-line">
                <span class="w-10 h-10 rounded-2xl bg-forest flex items-center justify-center text-white">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.25 7.5l-.63 10.63a2.25 2.25 0 0 1-2.25 2.12H6.63a2.25 2.25 0 0 1-2.25-2.12L3.75 7.5M10 11.25h4M3.38 7.5h17.25c.62 0 1.12-.5 1.12-1.12v-1.5C21.75 4.26 21.25 3.75 20.63 3.75H3.38c-.62 0-1.13.5-1.13 1.12v1.5c0 .63.5 1.13 1.12 1.13Z"/></svg>
                </span>
                <span class="text-xl font-extrabold tracking-tight">Kulaan</span>
            </div>
            <nav class="flex-1 px-4 py-6 space-y-1 text-sm font-semibold">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m2.25 12 8.95-8.95a1.13 1.13 0 0 1 1.6 0l8.95 8.95M4.5 9.75V19.5a.75.75 0 0 0 .75.75h13.5a.75.75 0 0 0 .75-.75V9.75"/></svg>
                    Dashboard
                </a>
                @if ($isUmkm)
                    <a href="{{ route('umkm.catalog.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>
                        Katalog
                    </a>
                    <a href="{{ route('umkm.cart.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2.25 3h1.39c.51 0 .96.34 1.09.84l.38 1.44M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.22c1.12-2.3 2.1-4.68 2.92-7.14a60.11 60.11 0 0 0-16.54-1.84M7.5 14.25 5.11 5.27M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                        Keranjang
                    </a>
                    <a href="{{ route('umkm.orders.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.25 7.5l-.63 10.63a2.25 2.25 0 0 1-2.25 2.12H6.63a2.25 2.25 0 0 1-2.25-2.12L3.75 7.5M10 11.25h4M3.38 7.5h17.25c.62 0 1.12-.5 1.12-1.12v-1.5C21.75 4.26 21.25 3.75 20.63 3.75H3.38c-.62 0-1.13.5-1.13 1.12v1.5c0 .63.5 1.13 1.12 1.13Z"/></svg>
                        Pesanan
                    </a>
                    <a href="{{ route('umkm.tracking.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path d="M19.5 10.5c0 7.14-7.5 11.25-7.5 11.25S4.5 17.64 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                        Tracking
                    </a>
                @elseif ($isDistributor)
                    <a href="{{ route('distributor.products.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5M12 22V12"/></svg>
                        Katalog Produk
                    </a>
                    <a href="{{ route('distributor.orders.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 17h6m-5 4h4M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/><path d="M9 8h6M9 12h4"/></svg>
                        Pesanan
                    </a>
                @elseif ($isCourier)
                    <a href="{{ route('kurir.shipments.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 17h6m-5 4h4M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/><path d="M9 8h6M9 12h4"/></svg>
                        Daftar Tugas
                    </a>
                @else
                    <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7a2 2 0 0 1 2-2h4l2 2h6a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7Z"/></svg>
                        Master Kategori
                    </a>
                    <a href="{{ route('admin.products.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5M12 22V12"/></svg>
                        Master Produk
                    </a>
                    <a href="{{ route('admin.proposals.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5v14m-7-7h14"/></svg>
                        Usulan Produk
                    </a>
                    <a href="{{ route('admin.spk.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-muted hover:bg-cream hover:text-ink transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 20h18M6 16v-5m6 5V8m6 8v-9"/></svg>
                        Kriteria SPK
                    </a>
                @endif
            </nav>
            <div class="p-4 border-t border-line">
                <a href="{{ route('profile.complete') }}" class="flex items-center gap-3 px-3 py-2 rounded-2xl bg-cream">
                    <span class="w-10 h-10 rounded-2xl bg-forest text-white flex items-center justify-center font-extrabold text-xs">{{ $initials }}</span>
                    <div class="min-w-0">
                        <p class="text-sm font-bold truncate">{{ $displayName }}</p>
                        <p class="text-[11px] text-muted truncate">{{ $roleSubtitle }}</p>
                    </div>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2.5 text-xs font-bold text-danger bg-danger/10 hover:bg-danger/15 rounded-xl transition cursor-pointer">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3h4a3 3 0 0 1 3 3v1"/></svg>
                        Keluar
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex-1 min-w-0">
            <header class="bg-white/70 backdrop-blur border-b border-line sticky top-0 z-10">
                <div class="px-8 h-[72px] flex items-center gap-4">
                    <div>
                        <h1 class="text-lg font-extrabold leading-tight">{{ $greetingEyebrow }} {{ $greetingName }}</h1>
                        <p class="text-xs text-muted">{{ now()->translatedFormat('l, d F Y') }}</p>
                    </div>
                    <div class="ml-auto flex items-center gap-3">
                        @if ($isUmkm)
                            <form method="GET" action="{{ route('umkm.catalog.index') }}" class="hidden lg:flex items-center gap-2 bg-white border border-line rounded-2xl pl-4 pr-2 py-2 w-72">
                                <svg class="w-4 h-4 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21 21-5.2-5.2m0 0A7.5 7.5 0 1 0 5.2 5.2a7.5 7.5 0 0 0 10.6 10.6Z"/></svg>
                                <input name="q" class="flex-1 bg-transparent text-sm outline-none placeholder:text-muted/70" placeholder="Cari pesanan, produk...">
                            </form>
                        @elseif ($isCourier)
                            <span class="hidden md:flex items-center gap-2 bg-forest/10 text-forest text-xs font-bold px-4 py-2.5 rounded-2xl">
                                <span class="relative flex w-2 h-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success opacity-60"></span><span class="relative inline-flex w-2 h-2 rounded-full bg-success"></span></span>
                                GPS Aktif
                            </span>
                        @elseif ($isAdmin)
                            <div class="hidden lg:flex items-center gap-2 bg-white border border-line rounded-2xl pl-4 pr-2 py-2 w-72">
                                <svg class="w-4 h-4 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21 21-5.2-5.2m0 0A7.5 7.5 0 1 0 5.2 5.2a7.5 7.5 0 0 0 10.6 10.6Z"/></svg>
                                <input class="flex-1 bg-transparent text-sm outline-none placeholder:text-muted/70" placeholder="Cari UMKM, distributor...">
                            </div>
                        @else
                            <div class="hidden lg:flex items-center gap-2 bg-white border border-line rounded-2xl pl-4 pr-2 py-2 w-72">
                                <svg class="w-4 h-4 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21 21-5.2-5.2m0 0A7.5 7.5 0 1 0 5.2 5.2a7.5 7.5 0 0 0 10.6 10.6Z"/></svg>
                                <input class="flex-1 bg-transparent text-sm outline-none placeholder:text-muted/70" placeholder="Cari pesanan, produk...">
                            </div>
                        @endif
                        <div class="relative" x-data="{ notifOpen: false }">
                            <button type="button" @click="notifOpen = !notifOpen" class="w-11 h-11 rounded-2xl bg-white border border-line flex items-center justify-center relative cursor-pointer">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14.86 17.08a23.85 23.85 0 0 0 5.45-1.31A8.97 8.97 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.97 8.97 0 0 1-2.31 6.02c1.73.64 3.56 1.09 5.45 1.31m5.72 0a24.26 24.26 0 0 1-5.72 0m5.72 0a3 3 0 1 1-5.72 0"/></svg>
                            </button>
                            <div x-show="notifOpen" @click.away="notifOpen = false" x-cloak class="absolute right-0 mt-2 w-72 bg-white border border-line rounded-2xl shadow-xl p-4 z-40 text-left">
                                <p class="text-sm font-extrabold text-ink">Notifikasi</p>
                                <div class="mt-3 space-y-1">
                                    @if ($isUmkm)
                                        <a href="{{ route('umkm.orders.index') }}" class="flex items-start gap-2 rounded-xl p-2 hover:bg-cream">
                                            <span class="w-2 h-2 mt-1.5 rounded-full bg-brass shrink-0"></span>
                                            <span class="text-xs text-ink">Tagihan menunggu pembayaran</span>
                                        </a>
                                        <a href="{{ route('umkm.tracking.index') }}" class="flex items-start gap-2 rounded-xl p-2 hover:bg-cream">
                                            <span class="w-2 h-2 mt-1.5 rounded-full bg-forest shrink-0"></span>
                                            <span class="text-xs text-ink">Pengiriman sedang berjalan</span>
                                        </a>
                                    @elseif ($isDistributor)
                                        <a href="{{ route('distributor.orders.index') }}" class="flex items-start gap-2 rounded-xl p-2 hover:bg-cream">
                                            <span class="w-2 h-2 mt-1.5 rounded-full bg-brass shrink-0"></span>
                                            <span class="text-xs text-ink">Pesanan perlu diproses</span>
                                        </a>
                                    @elseif ($isCourier)
                                        <a href="{{ route('kurir.shipments.index') }}" class="flex items-start gap-2 rounded-xl p-2 hover:bg-cream">
                                            <span class="w-2 h-2 mt-1.5 rounded-full bg-brass shrink-0"></span>
                                            <span class="text-xs text-ink">Tugas menunggu</span>
                                        </a>
                                    @else
                                        <a href="{{ route('admin.proposals.index') }}" class="flex items-start gap-2 rounded-xl p-2 hover:bg-cream">
                                            <span class="w-2 h-2 mt-1.5 rounded-full bg-brass shrink-0"></span>
                                            <span class="text-xs text-ink">Usulan produk menunggu review</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="px-8 py-6">
                <form method="POST" action="{{ route('profile.complete.update') }}" x-data="{ showPass: false }">
                    @csrf

                    <section class="relative overflow-hidden rounded-[2rem] bg-gradient-to-r from-forest-deep via-forest to-forest-dark text-white p-8 sm:p-10">
                        <div class="absolute -right-16 -top-20 w-72 h-72 rounded-full bg-white/10 pointer-events-none"></div>
                        <div class="absolute right-40 -bottom-24 w-56 h-56 rounded-full bg-brass/20 pointer-events-none"></div>
                        <div class="relative max-w-2xl">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-bold bg-white/10 text-white/85 uppercase tracking-[0.14em]">Profil</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-bold bg-white/10 text-white/85 uppercase tracking-[0.14em]">{{ $roleLabel }}</span>
                                @if (!blank($user->phone) && !blank($user->address))
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-bold bg-success/20 text-success border border-success/30">Terverifikasi</span>
                                @endif
                            </div>
                            <h1 class="text-4xl font-extrabold mt-3 tracking-tight">Lengkapi Profilmu</h1>
                            <p class="text-sm text-white/70 mt-2">Data ini dipakai buat pengiriman, tagihan, dan nyocokin kamu ke grup pengadaan kolektif yang pas.</p>
                        </div>
                    </section>

                    <div class="max-w-3xl mx-auto">
                        <div class="bg-white rounded-3xl border border-line p-6 mt-6 mb-6">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-bold text-ink">Kelengkapan profil</p>
                                <p class="text-sm font-extrabold text-forest">75%</p>
                            </div>
                            <div class="mt-3 h-2.5 rounded-full bg-cream border border-line overflow-hidden">
                                <div class="h-full w-3/4 rounded-full bg-gradient-to-r from-forest to-success"></div>
                            </div>
                        </div>

                        <section class="bg-white rounded-3xl border border-line p-6 sm:p-8 mb-6">
                            <div class="flex items-center gap-3 mb-6">
                                <span class="w-11 h-11 rounded-full bg-success/10 text-forest flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5"/></svg>
                                </span>
                                <h2 class="font-extrabold text-ink">Data akun</h2>
                            </div>
                            <div class="grid sm:grid-cols-2 gap-5">
                                <div>
                                    <label for="name" class="block text-sm font-semibold text-ink mb-2">Nama lengkap</label>
                                    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full bg-cream/50 border border-line rounded-2xl px-4 py-3.5 text-sm outline-none focus:border-forest">
                                    @error('name')
                                        <p class="text-xs text-danger font-semibold mt-1.5">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="phone" class="block text-sm font-semibold text-ink mb-2">Nomor WhatsApp / telepon</label>
                                    <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="081234567890" required class="w-full bg-cream/50 border border-line rounded-2xl px-4 py-3.5 text-sm outline-none placeholder:text-muted/60 focus:border-forest">
                                    @error('phone')
                                        <p class="text-xs text-danger font-semibold mt-1.5">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div class="mt-5">
                                <label for="email" class="block text-sm font-semibold text-ink mb-2">Alamat email</label>
                                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full bg-cream/50 border border-line rounded-2xl px-4 py-3.5 text-sm outline-none focus:border-forest">
                                @error('email')
                                    <p class="text-xs text-danger font-semibold mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="mt-5">
                                <label for="address" class="block text-sm font-semibold text-ink mb-2">Alamat lengkap</label>
                                <textarea id="address" name="address" rows="2" required placeholder="Nama jalan, nomor bangunan, RT/RW" class="w-full bg-cream/50 border border-line rounded-2xl px-4 py-3.5 text-sm outline-none placeholder:text-muted/60 focus:border-forest">{{ old('address', $user->address) }}</textarea>
                                @error('address')
                                    <p class="text-xs text-danger font-semibold mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="mt-5">
                                <label class="block text-sm font-semibold text-ink mb-2">Wilayah administrasi</label>
                                <x-region-selects :values="[
                                    'province' => old('province', $umkm?->province ?? $distributor?->province),
                                    'city' => old('city', $umkm?->city ?? $distributor?->city),
                                    'district' => old('district', $umkm?->district ?? $distributor?->district),
                                    'village' => old('village', $umkm?->village ?? $distributor?->village),
                                ]" />
                            </div>
                        </section>

                        @if ($isUmkm || $isDistributor || $isCourier)
                            <section class="bg-white rounded-3xl border border-line p-6 sm:p-8 mb-6">
                                <div class="flex items-center gap-3 mb-6">
                                    <span class="w-11 h-11 rounded-full bg-brass-soft text-brass flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-4h6v4"/></svg>
                                    </span>
                                    <h2 class="font-extrabold text-ink">Data usaha</h2>
                                </div>
                                <div class="grid sm:grid-cols-2 gap-5">
                                    @if ($isUmkm)
                                        <div>
                                            <label for="business_name" class="block text-sm font-semibold text-ink mb-2">Nama usaha</label>
                                            <input id="business_name" type="text" name="business_name" value="{{ old('business_name', $umkm?->business_name) }}" placeholder="cth. Warung Berkah Kuliner" class="w-full bg-cream/50 border border-line rounded-2xl px-4 py-3.5 text-sm outline-none placeholder:text-muted/60 focus:border-forest">
                                        </div>
                                        <div>
                                            <label for="business_type" class="block text-sm font-semibold text-ink mb-2">Jenis usaha</label>
                                            <select id="business_type" name="business_type" class="w-full bg-cream/50 border border-line rounded-2xl px-4 py-3.5 text-sm outline-none focus:border-forest">
                                                @foreach (['Kuliner', 'Minuman', 'Sembako', 'Kerajinan', 'Jasa', 'Lainnya'] as $type)
                                                    <option value="{{ $type }}" @selected(old('business_type', $umkm?->business_type) === $type)>{{ $type }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @elseif ($isDistributor)
                                        <div>
                                            <label for="business_name" class="block text-sm font-semibold text-ink mb-2">Nama Perusahaan / Distributor</label>
                                            <input id="business_name" type="text" name="business_name" value="{{ old('business_name', $distributor?->company_name) }}" class="w-full bg-cream/50 border border-line rounded-2xl px-4 py-3.5 text-sm outline-none focus:border-forest">
                                        </div>
                                        <div>
                                            <label for="delivery_lead_time_days" class="block text-sm font-semibold text-ink mb-2">Estimasi Kirim (Hari)</label>
                                            <input id="delivery_lead_time_days" type="number" min="1" max="30" name="delivery_lead_time_days" value="{{ old('delivery_lead_time_days', $distributor?->delivery_lead_time_days ?? 2) }}" class="w-full bg-cream/50 border border-line rounded-2xl px-4 py-3.5 text-sm outline-none focus:border-forest">
                                        </div>
                                    @elseif ($isCourier)
                                        <div>
                                            <label for="vehicle_type" class="block text-sm font-semibold text-ink mb-2">Tipe Kendaraan</label>
                                            <select id="vehicle_type" name="vehicle_type" class="w-full bg-cream/50 border border-line rounded-2xl px-4 py-3.5 text-sm outline-none focus:border-forest">
                                                @foreach (['Motor', 'Mobil Pick-Up', 'Mobil Box', 'Truk Engkel', 'Lainnya'] as $vType)
                                                    <option value="{{ $vType }}" @selected(old('vehicle_type', $courier?->vehicle_type) === $vType)>{{ $vType }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label for="vehicle_number" class="block text-sm font-semibold text-ink mb-2">Nomor Polisi</label>
                                            <input id="vehicle_number" type="text" name="vehicle_number" value="{{ old('vehicle_number', $courier?->vehicle_number) }}" placeholder="DA 1234 ABC" class="w-full bg-cream/50 border border-line rounded-2xl px-4 py-3.5 text-sm uppercase outline-none placeholder:text-muted/60 focus:border-forest">
                                        </div>
                                    @endif
                                </div>
                            </section>
                        @endif

                        <section class="bg-white rounded-3xl border border-line p-6 sm:p-8 mb-6">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="w-11 h-11 rounded-full bg-success/10 text-forest flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                                </span>
                                <div>
                                    <h2 class="font-extrabold text-ink">Atur kata sandi</h2>
                                    <p class="text-xs text-muted">Opsional - isi kalau kamu mau bisa login pakai email juga.</p>
                                </div>
                            </div>
                            <div class="grid sm:grid-cols-2 gap-5 mt-5">
                                <div>
                                    <label for="password" class="block text-sm font-semibold text-ink mb-2">Kata sandi baru</label>
                                    <div class="relative">
                                        <input id="password" :type="showPass ? 'text' : 'password'" name="password" autocomplete="new-password" class="w-full bg-cream/50 border border-line rounded-2xl px-4 py-3.5 pr-20 text-sm outline-none focus:border-forest">
                                        <button type="button" @click="showPass = !showPass" class="absolute right-2 top-1/2 -translate-y-1/2 text-xs font-bold text-muted px-3 py-2 cursor-pointer" x-text="showPass ? 'Tutup' : 'Lihat'">Lihat</button>
                                    </div>
                                    @error('password')
                                        <p class="text-xs text-danger font-semibold mt-1.5">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="password_confirmation" class="block text-sm font-semibold text-ink mb-2">Ulangi kata sandi</label>
                                    <input id="password_confirmation" :type="showPass ? 'text' : 'password'" name="password_confirmation" autocomplete="new-password" class="w-full bg-cream/50 border border-line rounded-2xl px-4 py-3.5 text-sm outline-none focus:border-forest">
                                </div>
                            </div>
                        </section>

                        <div class="sticky bottom-6 z-10 bg-cream/90 backdrop-blur rounded-3xl border border-line p-4 flex flex-col sm:flex-row gap-3">
                            <a href="{{ route('dashboard') }}" class="flex-1 border border-line text-muted text-sm font-bold py-3.5 rounded-2xl bg-white flex items-center justify-center">Kembali ke Dashboard</a>
                            <button type="submit" class="flex-1 bg-forest text-white text-sm font-bold py-3.5 rounded-2xl shadow-lg shadow-forest/25 cursor-pointer">Simpan Profil</button>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>
</x-layouts.app>
