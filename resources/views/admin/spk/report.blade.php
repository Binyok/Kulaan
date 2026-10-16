<x-layouts.admin title="Laporan Hasil SPK - #SPK-{{ $history->id }}">
    @php
        $user = auth()->user();
    @endphp

    <!-- ============================ MOBILE ============================ -->
    <div class="lg:hidden pb-24 print:pb-0">
        <!-- greeting -->
        <div class="px-4 pt-2 flex items-center justify-between print:hidden">
            <div class="flex items-center gap-3">
                <div class="relative" x-data="{ menuOpen: false }">
                    <button type="button" @click="menuOpen = !menuOpen" class="w-11 h-11 rounded-2xl bg-forest text-white flex items-center justify-center font-extrabold text-sm cursor-pointer">{{ strtoupper(substr($user->name, 0, 2)) }}</button>
                    <div x-show="menuOpen" @click.away="menuOpen = false" x-cloak class="absolute left-0 mt-2 w-48 bg-white border border-line rounded-2xl shadow-xl p-2 z-40 text-xs">
                        <a href="{{ route('profile.complete') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-ink hover:bg-cream font-medium">Profil Saya</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2 px-3 py-2.5 rounded-xl text-danger hover:bg-danger/10 font-bold transition text-left cursor-pointer">Keluar</button>
                        </form>
                    </div>
                </div>
                <div>
                    <p class="text-[11px] text-muted">Kontrol Pusat,</p>
                    <p class="text-[15px] font-extrabold leading-tight">{{ $user->name }}</p>
                </div>
            </div>
            <div class="relative" x-data="{ notifOpen: false }">
                <button type="button" @click="notifOpen = !notifOpen" class="w-10 h-10 rounded-2xl bg-white border border-line flex items-center justify-center text-ink relative cursor-pointer">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14.86 17.08a23.85 23.85 0 0 0 5.45-1.31A8.97 8.97 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.97 8.97 0 0 1-2.31 6.02c1.73.64 3.56 1.09 5.45 1.31m5.72 0a24.26 24.26 0 0 1-5.72 0m5.72 0a3 3 0 1 1-5.72 0"/></svg>
                </button>
                <div x-show="notifOpen" @click.away="notifOpen = false" x-cloak class="absolute right-0 mt-2 w-72 bg-white border border-line rounded-2xl shadow-xl p-4 z-40 text-left">
                    <p class="text-sm font-extrabold text-ink">Notifikasi</p>
                    <div class="mt-3 space-y-1">
                        <a href="{{ route('admin.proposals.index') }}" class="flex items-start gap-2 rounded-xl p-2 hover:bg-cream">
                            <span class="w-2 h-2 mt-1.5 rounded-full bg-brass shrink-0"></span>
                            <span class="text-xs text-ink"><b>{{ $pendingProposals ?? 0 }}</b> usulan produk menunggu review</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="px-4 pt-3 pb-4 print:px-0">
            <div class="flex items-center justify-between gap-2 mb-4 print:hidden">
                <a href="{{ route('admin.spk.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-forest">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    SPK
                </a>
                <button type="button" onclick="window.print()" class="btn-primary !text-xs !py-2.5">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M16.5 18a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Zm-13.5 0a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Zm13.5-12.75h1.091c.473 0 .906.278 1.091.732l2.009 5.05c.189.476-.012 1.016-.488 1.203a1.125 1.125 0 0 1-.434.078H16.5V5.25Z"/></svg>
                    Cetak PDF
                </button>
            </div>

            <div class="card p-5 max-w-4xl mx-auto print:border-0 print:shadow-none print:p-0">
                <div class="border-b-2 border-forest pb-3 mb-5">
                    <h2 class="text-xl font-extrabold text-forest tracking-tight">KULA'AN &middot; UMKM SUPPLY HUB</h2>
                    <p class="text-xs text-muted mt-1">Laporan Hasil Sistem Pendukung Keputusan Penentuan Distributor Terbaik (AHP + TOPSIS + SAW)</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 bg-cream rounded-xl p-4 mb-2 text-xs">
                    <div><strong>Nomor Laporan:</strong> SPK-RPT-{{ str_pad($history->id, 5, '0', STR_PAD_LEFT) }}</div>
                    <div><strong>Waktu Kalkulasi:</strong> {{ $history->created_at->translatedFormat('d F Y, H:i') }} WITA</div>
                    <div><strong>Kode Grup Pengadaan:</strong> <span class="font-mono font-bold">{{ $history->matchingGroup?->group_code ?? '-' }}</span></div>
                    <div><strong>Komoditas Produk:</strong> {{ $history->matchingGroup?->product?->name ?? 'Multi Komoditas' }}</div>
                </div>

                <h3 class="text-sm font-extrabold text-ink border-l-4 border-forest pl-2 mt-6 mb-3">1. Pembobotan Kriteria (Analytical Hierarchy Process / AHP)</h3>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse mb-5 text-[13px]">
                        <thead>
                            <tr class="[&>th]:border [&>th]:border-line [&>th]:bg-cream [&>th]:px-3 [&>th]:py-2 [&>th]:text-left [&>th]:text-xs [&>th]:font-bold">
                                <th>Kode</th>
                                <th>Nama Kriteria</th>
                                <th>Sifat</th>
                                <th class="text-right">Bobot Hasil AHP (w<sub>j</sub>)</th>
                                <th class="text-right">Persentase</th>
                            </tr>
                        </thead>
                        <tbody class="[&>tr>td]:border [&>tr>td]:border-line [&>tr>td]:px-3 [&>tr>td]:py-2">
                            @php
                                $weights = $history->weights_used ?? [0.4245, 0.2728, 0.1772, 0.0836, 0.0419];
                            @endphp
                            @foreach ($criteria as $i => $crit)
                                <tr>
                                    <td class="font-mono text-center">{{ $crit->code }}</td>
                                    <td>{{ $crit->name }}</td>
                                    <td class="text-center">{{ strtoupper($crit->type) }}</td>
                                    <td class="text-right font-mono">{{ number_format($weights[$i] ?? 0, 4) }}</td>
                                    <td class="text-right font-mono">{{ number_format(($weights[$i] ?? 0) * 100, 2) }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <h3 class="text-sm font-extrabold text-ink border-l-4 border-forest pl-2 mt-6 mb-3">2. Matriks Keputusan Alternatif Distributor (X)</h3>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse mb-5 text-[13px]">
                        <thead>
                            <tr class="[&>th]:border [&>th]:border-line [&>th]:bg-cream [&>th]:px-3 [&>th]:py-2 [&>th]:text-left [&>th]:text-xs [&>th]:font-bold">
                                <th>Alternatif</th>
                                <th class="text-right">Harga (C<sub>1</sub>)</th>
                                <th class="text-center">Rating Kualitas (C<sub>2</sub>)</th>
                                <th class="text-center">Waktu Kirim (C<sub>3</sub>)</th>
                                <th class="text-right">Jarak Haversine (C<sub>4</sub>)</th>
                                <th class="text-right">MOQ (C<sub>5</sub>)</th>
                            </tr>
                        </thead>
                        <tbody class="[&>tr>td]:border [&>tr>td]:border-line [&>tr>td]:px-3 [&>tr>td]:py-2">
                            @foreach ($history->decision_matrix ?? [] as $alt)
                                <tr>
                                    <td><strong>{{ $alt['name'] }}</strong></td>
                                    <td class="text-right font-mono">Rp {{ number_format($alt['criteria_values'][0], 0, ',', '.') }}</td>
                                    <td class="text-center font-mono">{{ number_format($alt['criteria_values'][1], 2) }} &#9733;</td>
                                    <td class="text-center font-mono">{{ 5 - $alt['criteria_values'][2] }} Hari</td>
                                    <td class="text-right font-mono">{{ number_format($alt['criteria_values'][3], 2) }} km</td>
                                    <td class="text-right font-mono">{{ number_format($alt['criteria_values'][4], 0) }} Unit</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <h3 class="text-sm font-extrabold text-ink border-l-4 border-forest pl-2 mt-6 mb-3">3. Hasil Pemeringkatan TOPSIS (Rekomendasi Utama)</h3>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse mb-5 text-[13px]">
                        <thead>
                            <tr class="[&>th]:border [&>th]:border-line [&>th]:bg-cream [&>th]:px-3 [&>th]:py-2 [&>th]:text-left [&>th]:text-xs [&>th]:font-bold">
                                <th class="text-center">Peringkat</th>
                                <th>Nama Distributor</th>
                                <th class="text-right">Jarak Ideal Positif (D<sub>i</sub><sup>+</sup>)</th>
                                <th class="text-right">Jarak Ideal Negatif (D<sub>i</sub><sup>-</sup>)</th>
                                <th class="text-right">Preferensi Relatif (V<sub>i</sub>)</th>
                                <th class="text-center">Status Keputusan</th>
                            </tr>
                        </thead>
                        <tbody class="[&>tr>td]:border [&>tr>td]:border-line [&>tr>td]:px-3 [&>tr>td]:py-2">
                            @foreach ($history->rankings['topsis'] ?? [] as $top)
                                <tr class="{{ $top['rank'] === 1 ? 'bg-[#E8F5E9] font-bold' : '' }}">
                                    <td class="text-center font-mono font-bold">#{{ $top['rank'] }}</td>
                                    <td><strong>{{ $top['name'] }}</strong></td>
                                    <td class="text-right font-mono">{{ number_format($top['d_plus'], 4) }}</td>
                                    <td class="text-right font-mono">{{ number_format($top['d_minus'], 4) }}</td>
                                    <td class="text-right font-mono font-bold">{{ number_format($top['score'], 4) }}</td>
                                    <td class="text-center text-xs">{{ $top['rank'] === 1 ? '&#9733; TERPILIH (Rekomendasi)' : 'Alternatif' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <h3 class="text-sm font-extrabold text-ink border-l-4 border-forest pl-2 mt-6 mb-3">4. Validasi Silang (Simple Additive Weighting / SAW)</h3>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse mb-5 text-[13px]">
                        <thead>
                            <tr class="[&>th]:border [&>th]:border-line [&>th]:bg-cream [&>th]:px-3 [&>th]:py-2 [&>th]:text-left [&>th]:text-xs [&>th]:font-bold">
                                <th class="text-center">Peringkat SAW</th>
                                <th>Nama Distributor</th>
                                <th class="text-right">Nilai Preferensi Terbobot (S<sub>i</sub>)</th>
                            </tr>
                        </thead>
                        <tbody class="[&>tr>td]:border [&>tr>td]:border-line [&>tr>td]:px-3 [&>tr>td]:py-2">
                            @foreach ($history->rankings['saw'] ?? [] as $saw)
                                <tr>
                                    <td class="text-center font-mono">#{{ $saw['rank'] }}</td>
                                    <td>{{ $saw['name'] }}</td>
                                    <td class="text-right font-mono font-bold">{{ number_format($saw['saw_score'], 4) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-between text-xs mt-8 pt-4 border-t border-line">
                    <div>
                        Dicetak secara sistematis oleh:<br>
                        <strong>Platform Kula'an Decision Support Engine</strong>
                    </div>
                    <div class="text-right">
                        Sidoarjo, {{ now()->translatedFormat('d F Y') }}<br>
                        Penanggung Jawab Pengadaan / Admin Sistem
                        <div class="mt-10 font-bold">( ____________________________ )</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- bottom tab -->
    <div class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-white border-t border-line px-2 pt-2 pb-5 flex items-start justify-around print:hidden">
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 w-14 text-muted">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m2.25 12 8.95-8.95a1.13 1.13 0 0 1 1.6 0l8.95 8.95M4.5 9.75V19.5a.75.75 0 0 0 .75.75h13.5a.75.75 0 0 0 .75-.75V9.75"/></svg>
            <span class="text-[10px] font-semibold">Beranda</span>
        </a>
        <a href="{{ route('admin.proposals.index') }}" class="flex flex-col items-center gap-1 w-14 text-muted relative">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5v14m-7-7h14"/></svg>
            <span class="text-[10px] font-semibold">Usulan</span>
            @if (($pendingProposals ?? 0) > 0)
                <span class="absolute top-0 right-2 min-w-[18px] h-[18px] px-1 rounded-full bg-brass text-white text-[10px] font-bold flex items-center justify-center">{{ $pendingProposals }}</span>
            @endif
        </a>
        <a href="{{ route('admin.spk.index') }}" class="flex flex-col items-center gap-1 w-14 text-forest">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 20h18M6 16v-5m6 5V8m6 8v-9"/></svg>
            <span class="text-[10px] font-bold">SPK</span>
        </a>
        <a href="{{ route('profile.complete') }}" class="flex flex-col items-center gap-1 w-14 text-muted">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17.98 18.72a6 6 0 0 0-8.46 0M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm-9.75 8.25a9 9 0 1 1 13.5 0"/></svg>
            <span class="text-[10px] font-semibold">Akun</span>
        </a>
    </div>

    <!-- ============================ DESKTOP ============================ -->
    <div class="hidden lg:flex min-h-screen">
        <!-- SIDEBAR -->
        <aside class="w-64 shrink-0 bg-white border-r border-line flex flex-col sticky top-0 h-screen print:hidden">
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
                    @if (($pendingProposals ?? 0) > 0)
                        <span class="ml-auto bg-brass text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $pendingProposals }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.spk.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-forest text-white">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 20h18M6 16v-5m6 5V8m6 8v-9"/></svg>
                    Kriteria SPK
                </a>
            </nav>
            <div class="p-4 border-t border-line">
                <a href="{{ route('profile.complete') }}" class="flex items-center gap-3 px-2">
                    <span class="w-10 h-10 rounded-2xl bg-forest text-white flex items-center justify-center font-extrabold text-xs">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                    <div class="min-w-0">
                        <p class="text-sm font-bold truncate">{{ $user->name }}</p>
                        <p class="text-[11px] text-muted">Kontrol Pusat</p>
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

        <!-- MAIN -->
        <div class="flex-1 min-w-0">
            <header class="bg-white/70 backdrop-blur border-b border-line sticky top-0 z-10 print:hidden">
                <div class="px-8 h-[72px] flex items-center gap-4">
                    <div>
                        <h1 class="text-lg font-extrabold leading-tight">Laporan Hasil SPK</h1>
                        <p class="text-xs text-muted">{{ now()->translatedFormat('l, d F Y') }}</p>
                    </div>
                    <div class="ml-auto flex items-center gap-3">
                        <div class="hidden lg:flex items-center gap-2 bg-white border border-line rounded-2xl pl-4 pr-2 py-2 w-72">
                            <svg class="w-4 h-4 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21 21-5.2-5.2m0 0A7.5 7.5 0 1 0 5.2 5.2a7.5 7.5 0 0 0 10.6 10.6Z"/></svg>
                            <input class="flex-1 bg-transparent text-sm outline-none placeholder:text-muted/70" placeholder="Cari UMKM, distributor...">
                        </div>
                        <div class="relative" x-data="{ notifOpen: false }">
                            <button type="button" @click="notifOpen = !notifOpen" class="w-11 h-11 rounded-2xl bg-white border border-line flex items-center justify-center relative cursor-pointer">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14.86 17.08a23.85 23.85 0 0 0 5.45-1.31A8.97 8.97 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.97 8.97 0 0 1-2.31 6.02c1.73.64 3.56 1.09 5.45 1.31m5.72 0a24.26 24.26 0 0 1-5.72 0m5.72 0a3 3 0 1 1-5.72 0"/></svg>
                            </button>
                            <div x-show="notifOpen" @click.away="notifOpen = false" x-cloak class="absolute right-0 mt-2 w-72 bg-white border border-line rounded-2xl shadow-xl p-4 z-40 text-left">
                                <p class="text-sm font-extrabold text-ink">Notifikasi</p>
                                <div class="mt-3 space-y-1">
                                    <a href="{{ route('admin.proposals.index') }}" class="flex items-start gap-2 rounded-xl p-2 hover:bg-cream">
                                        <span class="w-2 h-2 mt-1.5 rounded-full bg-brass shrink-0"></span>
                                        <span class="text-xs text-ink"><b>{{ $pendingProposals ?? 0 }}</b> usulan produk menunggu review</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="px-8 py-6 max-w-6xl">
                <div class="flex justify-between items-center mb-6 print:hidden">
                    <a href="{{ route('admin.spk.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-forest">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                        Kembali ke Dashboard SPK
                    </a>
                    <button type="button" onclick="window.print()" class="btn-primary">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M16.5 18a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Zm-13.5 0a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Zm13.5-12.75h1.091c.473 0 .906.278 1.091.732l2.009 5.05c.189.476-.012 1.016-.488 1.203a1.125 1.125 0 0 1-.434.078H16.5V5.25Z"/></svg>
                        Cetak / Simpan sebagai PDF
                    </button>
                </div>

                <div class="card p-8 max-w-4xl mx-auto print:border-0 print:shadow-none print:p-0">
                    <div class="border-b-2 border-forest pb-3 mb-5">
                        <h2 class="text-xl font-extrabold text-forest tracking-tight">KULA'AN &middot; UMKM SUPPLY HUB</h2>
                        <p class="text-xs text-muted mt-1">Laporan Hasil Sistem Pendukung Keputusan Penentuan Distributor Terbaik (AHP + TOPSIS + SAW)</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 bg-cream rounded-xl p-4 mb-2 text-xs">
                        <div><strong>Nomor Laporan:</strong> SPK-RPT-{{ str_pad($history->id, 5, '0', STR_PAD_LEFT) }}</div>
                        <div><strong>Waktu Kalkulasi:</strong> {{ $history->created_at->translatedFormat('d F Y, H:i') }} WITA</div>
                        <div><strong>Kode Grup Pengadaan:</strong> <span class="font-mono font-bold">{{ $history->matchingGroup?->group_code ?? '-' }}</span></div>
                        <div><strong>Komoditas Produk:</strong> {{ $history->matchingGroup?->product?->name ?? 'Multi Komoditas' }}</div>
                    </div>

                    <h3 class="text-sm font-extrabold text-ink border-l-4 border-forest pl-2 mt-6 mb-3">1. Pembobotan Kriteria (Analytical Hierarchy Process / AHP)</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse mb-5 text-[13px]">
                            <thead>
                                <tr class="[&>th]:border [&>th]:border-line [&>th]:bg-cream [&>th]:px-3 [&>th]:py-2 [&>th]:text-left [&>th]:text-xs [&>th]:font-bold">
                                    <th>Kode</th>
                                    <th>Nama Kriteria</th>
                                    <th>Sifat</th>
                                    <th class="text-right">Bobot Hasil AHP (w<sub>j</sub>)</th>
                                    <th class="text-right">Persentase</th>
                                </tr>
                            </thead>
                            <tbody class="[&>tr>td]:border [&>tr>td]:border-line [&>tr>td]:px-3 [&>tr>td]:py-2">
                                @php
                                    $weights = $history->weights_used ?? [0.4245, 0.2728, 0.1772, 0.0836, 0.0419];
                                @endphp
                                @foreach ($criteria as $i => $crit)
                                    <tr>
                                        <td class="font-mono text-center">{{ $crit->code }}</td>
                                        <td>{{ $crit->name }}</td>
                                        <td class="text-center">{{ strtoupper($crit->type) }}</td>
                                        <td class="text-right font-mono">{{ number_format($weights[$i] ?? 0, 4) }}</td>
                                        <td class="text-right font-mono">{{ number_format(($weights[$i] ?? 0) * 100, 2) }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <h3 class="text-sm font-extrabold text-ink border-l-4 border-forest pl-2 mt-6 mb-3">2. Matriks Keputusan Alternatif Distributor (X)</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse mb-5 text-[13px]">
                            <thead>
                                <tr class="[&>th]:border [&>th]:border-line [&>th]:bg-cream [&>th]:px-3 [&>th]:py-2 [&>th]:text-left [&>th]:text-xs [&>th]:font-bold">
                                    <th>Alternatif</th>
                                    <th class="text-right">Harga (C<sub>1</sub>)</th>
                                    <th class="text-center">Rating Kualitas (C<sub>2</sub>)</th>
                                    <th class="text-center">Waktu Kirim (C<sub>3</sub>)</th>
                                    <th class="text-right">Jarak Haversine (C<sub>4</sub>)</th>
                                    <th class="text-right">MOQ (C<sub>5</sub>)</th>
                                </tr>
                            </thead>
                            <tbody class="[&>tr>td]:border [&>tr>td]:border-line [&>tr>td]:px-3 [&>tr>td]:py-2">
                                @foreach ($history->decision_matrix ?? [] as $alt)
                                    <tr>
                                        <td><strong>{{ $alt['name'] }}</strong></td>
                                        <td class="text-right font-mono">Rp {{ number_format($alt['criteria_values'][0], 0, ',', '.') }}</td>
                                        <td class="text-center font-mono">{{ number_format($alt['criteria_values'][1], 2) }} &#9733;</td>
                                        <td class="text-center font-mono">{{ 5 - $alt['criteria_values'][2] }} Hari</td>
                                        <td class="text-right font-mono">{{ number_format($alt['criteria_values'][3], 2) }} km</td>
                                        <td class="text-right font-mono">{{ number_format($alt['criteria_values'][4], 0) }} Unit</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <h3 class="text-sm font-extrabold text-ink border-l-4 border-forest pl-2 mt-6 mb-3">3. Hasil Pemeringkatan TOPSIS (Rekomendasi Utama)</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse mb-5 text-[13px]">
                            <thead>
                                <tr class="[&>th]:border [&>th]:border-line [&>th]:bg-cream [&>th]:px-3 [&>th]:py-2 [&>th]:text-left [&>th]:text-xs [&>th]:font-bold">
                                    <th class="text-center">Peringkat</th>
                                    <th>Nama Distributor</th>
                                    <th class="text-right">Jarak Ideal Positif (D<sub>i</sub><sup>+</sup>)</th>
                                    <th class="text-right">Jarak Ideal Negatif (D<sub>i</sub><sup>-</sup>)</th>
                                    <th class="text-right">Preferensi Relatif (V<sub>i</sub>)</th>
                                    <th class="text-center">Status Keputusan</th>
                                </tr>
                            </thead>
                            <tbody class="[&>tr>td]:border [&>tr>td]:border-line [&>tr>td]:px-3 [&>tr>td]:py-2">
                                @foreach ($history->rankings['topsis'] ?? [] as $top)
                                    <tr class="{{ $top['rank'] === 1 ? 'bg-[#E8F5E9] font-bold' : '' }}">
                                        <td class="text-center font-mono font-bold">#{{ $top['rank'] }}</td>
                                        <td><strong>{{ $top['name'] }}</strong></td>
                                        <td class="text-right font-mono">{{ number_format($top['d_plus'], 4) }}</td>
                                        <td class="text-right font-mono">{{ number_format($top['d_minus'], 4) }}</td>
                                        <td class="text-right font-mono font-bold">{{ number_format($top['score'], 4) }}</td>
                                        <td class="text-center text-xs">{{ $top['rank'] === 1 ? '&#9733; TERPILIH (Rekomendasi)' : 'Alternatif' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <h3 class="text-sm font-extrabold text-ink border-l-4 border-forest pl-2 mt-6 mb-3">4. Validasi Silang (Simple Additive Weighting / SAW)</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse mb-5 text-[13px]">
                            <thead>
                                <tr class="[&>th]:border [&>th]:border-line [&>th]:bg-cream [&>th]:px-3 [&>th]:py-2 [&>th]:text-left [&>th]:text-xs [&>th]:font-bold">
                                    <th class="text-center">Peringkat SAW</th>
                                    <th>Nama Distributor</th>
                                    <th class="text-right">Nilai Preferensi Terbobot (S<sub>i</sub>)</th>
                                </tr>
                            </thead>
                            <tbody class="[&>tr>td]:border [&>tr>td]:border-line [&>tr>td]:px-3 [&>tr>td]:py-2">
                                @foreach ($history->rankings['saw'] ?? [] as $saw)
                                    <tr>
                                        <td class="text-center font-mono">#{{ $saw['rank'] }}</td>
                                        <td>{{ $saw['name'] }}</td>
                                        <td class="text-right font-mono font-bold">{{ number_format($saw['saw_score'], 4) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-between text-xs mt-8 pt-4 border-t border-line">
                        <div>
                            Dicetak secara sistematis oleh:<br>
                            <strong>Platform Kula'an Decision Support Engine</strong>
                        </div>
                        <div class="text-right">
                            Sidoarjo, {{ now()->translatedFormat('d F Y') }}<br>
                            Penanggung Jawab Pengadaan / Admin Sistem
                            <div class="mt-10 font-bold">( ____________________________ )</div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</x-layouts.admin>
