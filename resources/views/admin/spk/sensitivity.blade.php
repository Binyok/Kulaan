@php
    $user = auth()->user();
    $pendingProposals = \App\Models\ProductProposal::where('status', \App\Enums\ProposalStatus::PENDING)->count();
@endphp

<x-layouts.admin title="Analisis Sensitivitas SPK">
    <!-- ============================ MOBILE ============================ -->
    <div class="lg:hidden pb-24">
        <!-- greeting -->
        <div class="px-4 pt-2 flex items-center justify-between">
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
                            <span class="text-xs text-ink"><b>{{ $pendingProposals }}</b> usulan produk menunggu review</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="px-4 mt-4">
            <!-- header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <div>
                    <span class="eyebrow">Academic &amp; Robustness Testing</span>
                    <h1 class="text-[1.75rem] sm:text-3xl font-extrabold text-ink tracking-tight mt-2">Uji Sensitivitas SPK</h1>
                    <p class="text-[0.8125rem] text-muted mt-1">Uji stabilitas pemeringkatan terhadap variasi bobot kriteria.</p>
                </div>
                <a href="{{ route('admin.spk.index') }}" class="btn-ghost w-full justify-center !text-xs">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    Kembali ke Kriteria
                </a>
            </div>

            <!-- scenario presets -->
            <div class="card p-5 mb-6">
                <p class="text-xs font-bold text-ink mb-3">Pilih Skenario Sensitivitas:</p>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.spk.sensitivity', ['history_id' => $history?->id, 'scenario' => 'custom']) }}"
                        class="min-h-[44px] inline-flex items-center px-3.5 rounded-xl border text-xs {{ $preset === 'custom' ? 'bg-forest text-white font-bold border-forest' : 'bg-white text-ink border-black/10 font-semibold' }}">
                        Bobot Baseline (AHP Default)
                    </a>
                    <a href="{{ route('admin.spk.sensitivity', ['history_id' => $history?->id, 'scenario' => 'cost_priority']) }}"
                        class="min-h-[44px] inline-flex items-center px-3.5 rounded-xl border text-xs {{ $preset === 'cost_priority' ? 'bg-forest text-white font-bold border-forest' : 'bg-white text-ink border-black/10 font-semibold' }}">
                        Fokus Biaya Murah (Harga 60%)
                    </a>
                    <a href="{{ route('admin.spk.sensitivity', ['history_id' => $history?->id, 'scenario' => 'quality_priority']) }}"
                        class="min-h-[44px] inline-flex items-center px-3.5 rounded-xl border text-xs {{ $preset === 'quality_priority' ? 'bg-forest text-white font-bold border-forest' : 'bg-white text-ink border-black/10 font-semibold' }}">
                        Fokus Kualitas &amp; Kecepatan (40%/30%)
                    </a>
                    <a href="{{ route('admin.spk.sensitivity', ['history_id' => $history?->id, 'scenario' => 'distance_priority']) }}"
                        class="min-h-[44px] inline-flex items-center px-3.5 rounded-xl border text-xs {{ $preset === 'distance_priority' ? 'bg-forest text-white font-bold border-forest' : 'bg-white text-ink border-black/10 font-semibold' }}">
                        Fokus Jarak Terdekat (Jarak 35%)
                    </a>
                    <a href="{{ route('admin.spk.sensitivity', ['history_id' => $history?->id, 'scenario' => 'equal_weights']) }}"
                        class="min-h-[44px] inline-flex items-center px-3.5 rounded-xl border text-xs {{ $preset === 'equal_weights' ? 'bg-forest text-white font-bold border-forest' : 'bg-white text-ink border-black/10 font-semibold' }}">
                        Bobot Rata (20% Masing-masing)
                    </a>
                </div>
            </div>

            <!-- scenario parameter form -->
            <div class="card p-6">
                <h2 class="text-base font-bold text-ink tracking-tight mb-1">Parameter Bobot Skenario</h2>
                <div class="mb-4">
                    @foreach ($criteria as $idx => $crit)
                        <div class="mb-2">
                            <div class="flex justify-between text-[10px] font-semibold text-muted mb-1">
                                <span>{{ $crit->name }} ({{ $crit->code }})</span>
                                <span>{{ number_format(($scenarioWeights[$idx] ?? 0) * 100, 1) }}%</span>
                            </div>
                            <div class="h-2 rounded-full bg-black/[0.06] overflow-hidden">
                                <div class="h-full rounded-full bg-forest" style="width: {{ number_format(($scenarioWeights[$idx] ?? 0) * 100, 1) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <form method="GET" action="{{ route('admin.spk.sensitivity') }}" class="space-y-3">
                    <input type="hidden" name="history_id" value="{{ $history?->id }}">
                    <input type="hidden" name="scenario" value="custom">
                    @foreach ($criteria as $idx => $crit)
                        <div>
                            <label for="spk_weight_{{ $idx }}" class="flex justify-between text-xs mb-1">
                                <span class="font-bold text-ink">{{ $crit->name }} ({{ $crit->code }})</span>
                                <span class="font-mono text-forest font-bold">{{ number_format(($scenarioWeights[$idx] ?? 0) * 100, 1) }}%</span>
                            </label>
                            <input id="spk_weight_{{ $idx }}" type="number" step="0.01" min="0" max="1" name="w{{ $idx }}"
                                value="{{ round($scenarioWeights[$idx] ?? 0, 4) }}" class="input text-xs w-full">
                        </div>
                    @endforeach
                    <button type="submit" class="btn-primary w-full justify-center !py-2.5 text-xs mt-4">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                        Kalkulasi Ulang Skenario
                    </button>
                </form>
                <div class="pt-4 mt-4 border-t border-black/[0.06] text-xs space-y-2">
                    <p class="font-bold text-ink">Kesimpulan Stabilitas:</p>
                    @if ($isRankingStable)
                        <div class="p-3 bg-[#DFF0E4] text-success rounded-xl border border-forest/20 font-medium">
                            <strong>Model Stabil (Robust):</strong> Pergeseran bobot tidak mengubah pemenang peringkat 1.
                        </div>
                    @else
                        <div class="p-3 bg-brass-soft text-brass rounded-xl border border-brass/30 font-medium">
                            <strong>Peka terhadap Bobot (Sensitive):</strong> Terjadi pergeseran peringkat pada alternatif distributor.
                        </div>
                    @endif
                </div>
            </div>

            <!-- rank comparison -->
            <div class="mt-4">
                <div class="card p-6">
                    <div class="flex flex-wrap justify-between items-center gap-2 mb-4">
                        <h2 class="text-base font-bold text-ink tracking-tight">Perbandingan Peringkat (Baseline vs Skenario)</h2>
                        <span class="badge {{ $isRankingStable ? 'bg-[#DFF0E4] text-success' : 'bg-brass-soft text-brass' }}">
                            {{ $isRankingStable ? 'Peringkat Konsisten' : 'Peringkat Bergeser' }}
                        </span>
                    </div>
                    @if (count($comparison) > 0)
                        <div class="hidden sm:block overflow-x-auto">
                            <table class="tbl w-full">
                                <thead>
                                    <tr>
                                        <th>Alternatif Distributor</th>
                                        <th class="text-center">Rank Awal (AHP Baseline)</th>
                                        <th class="text-center">Skor Awal</th>
                                        <th class="text-center">Rank Baru (Skenario)</th>
                                        <th class="text-center">Skor Baru</th>
                                        <th class="text-center">Perubahan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($comparison as $row)
                                        <tr>
                                            <td class="font-bold text-ink text-xs">{{ $row['name'] }}</td>
                                            <td class="text-center font-mono font-bold">#{{ $row['base_rank'] ?? '-' }}</td>
                                            <td class="text-center font-mono text-xs text-muted">{{ number_format($row['base_score'], 4) }}</td>
                                            <td class="text-center font-mono font-extrabold text-forest">#{{ $row['new_rank'] }}</td>
                                            <td class="text-center font-mono text-xs font-bold text-ink">{{ number_format($row['new_score'], 4) }}</td>
                                            <td class="text-center">
                                                @if ($row['rank_diff'] > 0)
                                                    <span class="badge bg-[#DFF0E4] text-success !text-[10px] font-bold">Naik {{ $row['rank_diff'] }}</span>
                                                @elseif ($row['rank_diff'] < 0)
                                                    <span class="badge bg-danger/10 text-danger !text-[10px] font-bold">Turun {{ abs($row['rank_diff']) }}</span>
                                                @else
                                                    <span class="badge bg-cream text-muted !text-[10px]">Tetap (Rank {{ $row['new_rank'] }})</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="sm:hidden">
                            @foreach ($comparison as $row)
                                <div class="card !rounded-2xl p-3.5 mb-2.5">
                                    <div class="flex justify-between items-start gap-3">
                                        <p class="font-bold text-ink text-sm min-w-0">{{ $row['name'] }}</p>
                                        @if ($row['rank_diff'] > 0)
                                            <span class="badge bg-[#DFF0E4] text-success !text-[10px] font-bold shrink-0">Naik {{ $row['rank_diff'] }}</span>
                                        @elseif ($row['rank_diff'] < 0)
                                            <span class="badge bg-danger/10 text-danger !text-[10px] font-bold shrink-0">Turun {{ abs($row['rank_diff']) }}</span>
                                        @else
                                            <span class="badge bg-cream text-muted !text-[10px] shrink-0">Tetap</span>
                                        @endif
                                    </div>
                                    <div class="grid grid-cols-2 gap-x-3 gap-y-1.5 text-xs pt-2 mt-2 border-t border-black/[0.04]">
                                        <span class="text-muted">Rank awal</span>
                                        <span class="text-right font-mono font-bold text-ink">#{{ $row['base_rank'] ?? '-' }} &bull; {{ number_format($row['base_score'], 4) }}</span>
                                        <span class="text-muted">Rank baru</span>
                                        <span class="text-right font-mono font-extrabold text-forest">#{{ $row['new_rank'] }} &bull; {{ number_format($row['new_score'], 4) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="pt-4 mt-4 border-t border-black/[0.06] space-y-2">
                            <h3 class="text-xs font-bold text-ink">Validasi Silang dengan Skor SAW (Skenario Saat Ini):</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                @foreach ($scenarioSaw as $saw)
                                    <div class="p-3 bg-white rounded-xl border border-black/5 text-xs">
                                        <div class="text-[11px] text-muted font-medium">Rank #{{ $saw['rank'] }} (SAW)</div>
                                        <div class="font-bold text-ink truncate mt-0.5">{{ $saw['name'] }}</div>
                                        <div class="font-mono text-forest font-bold mt-1">Nilai: {{ number_format($saw['saw_score'], 4) }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="py-10 text-center border-t border-black/[0.04]">
                            <p class="font-bold text-ink text-sm">Tidak ada data alternatif yang diuji.</p>
                            <p class="text-[0.8125rem] text-muted mt-1.5 max-w-md mx-auto">Jalankan kalkulasi SPK terlebih dahulu agar tersedia data peringkat untuk diuji sensitivitasnya.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- bottom tab -->
    <div class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-white border-t border-line px-2 pt-2 pb-5 flex items-start justify-around">
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 w-14 text-muted">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m2.25 12 8.95-8.95a1.13 1.13 0 0 1 1.6 0l8.95 8.95M4.5 9.75V19.5a.75.75 0 0 0 .75.75h13.5a.75.75 0 0 0 .75-.75V9.75"/></svg>
            <span class="text-[10px] font-semibold">Beranda</span>
        </a>
        <a href="{{ route('admin.proposals.index') }}" class="flex flex-col items-center gap-1 w-14 text-muted relative">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5v14m-7-7h14"/></svg>
            <span class="text-[10px] font-semibold">Usulan</span>
            @if ($pendingProposals > 0)
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
                    @if ($pendingProposals > 0)
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
            <header class="bg-white/70 backdrop-blur border-b border-line sticky top-0 z-10">
                <div class="px-8 h-[72px] flex items-center gap-4">
                    <div>
                        <h1 class="text-lg font-extrabold leading-tight">Uji Analisis Sensitivitas SPK</h1>
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
                                        <span class="text-xs text-ink"><b>{{ $pendingProposals }}</b> usulan produk menunggu review</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="px-8 py-6 max-w-6xl">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div>
                        <span class="eyebrow">Academic &amp; Robustness Testing</span>
                        <h1 class="text-[1.75rem] sm:text-3xl font-extrabold text-ink tracking-tight mt-2">Uji Analisis Sensitivitas SPK</h1>
                        <p class="text-[0.8125rem] text-muted mt-1">Uji stabilitas dan ketahanan pemeringkatan TOPSIS &amp; SAW terhadap variasi pergeseran bobot kriteria.</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2.5 w-full sm:w-auto">
                        <a href="{{ route('admin.spk.index') }}" class="btn-ghost justify-center">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                            Kembali ke Kriteria
                        </a>
                        @if ($history)
                            <a href="{{ route('admin.spk.export', $history) }}" target="_blank" class="btn-primary !text-xs !py-2.5 justify-center">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                Cetak Laporan PDF
                            </a>
                        @endif
                    </div>
                </div>

                <div class="card p-5 mb-6">
                    <p class="text-xs font-bold text-ink mb-3">Pilih Skenario Sensitivitas:</p>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.spk.sensitivity', ['history_id' => $history?->id, 'scenario' => 'custom']) }}"
                            class="min-h-[44px] inline-flex items-center px-3.5 rounded-xl border text-xs {{ $preset === 'custom' ? 'bg-forest text-white font-bold border-forest' : 'bg-white text-ink border-black/10 font-semibold' }}">
                            Bobot Baseline (AHP Default)
                        </a>
                        <a href="{{ route('admin.spk.sensitivity', ['history_id' => $history?->id, 'scenario' => 'cost_priority']) }}"
                            class="min-h-[44px] inline-flex items-center px-3.5 rounded-xl border text-xs {{ $preset === 'cost_priority' ? 'bg-forest text-white font-bold border-forest' : 'bg-white text-ink border-black/10 font-semibold' }}">
                            Fokus Biaya Murah (Harga 60%)
                        </a>
                        <a href="{{ route('admin.spk.sensitivity', ['history_id' => $history?->id, 'scenario' => 'quality_priority']) }}"
                            class="min-h-[44px] inline-flex items-center px-3.5 rounded-xl border text-xs {{ $preset === 'quality_priority' ? 'bg-forest text-white font-bold border-forest' : 'bg-white text-ink border-black/10 font-semibold' }}">
                            Fokus Kualitas &amp; Kecepatan (40%/30%)
                        </a>
                        <a href="{{ route('admin.spk.sensitivity', ['history_id' => $history?->id, 'scenario' => 'distance_priority']) }}"
                            class="min-h-[44px] inline-flex items-center px-3.5 rounded-xl border text-xs {{ $preset === 'distance_priority' ? 'bg-forest text-white font-bold border-forest' : 'bg-white text-ink border-black/10 font-semibold' }}">
                            Fokus Jarak Terdekat (Jarak 35%)
                        </a>
                        <a href="{{ route('admin.spk.sensitivity', ['history_id' => $history?->id, 'scenario' => 'equal_weights']) }}"
                            class="min-h-[44px] inline-flex items-center px-3.5 rounded-xl border text-xs {{ $preset === 'equal_weights' ? 'bg-forest text-white font-bold border-forest' : 'bg-white text-ink border-black/10 font-semibold' }}">
                            Bobot Rata (20% Masing-masing)
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- scenario parameter form -->
                    <div class="lg:col-span-1">
                        <div class="card p-6">
                            <h2 class="text-base font-bold text-ink tracking-tight mb-1">Parameter Bobot Skenario</h2>
                            <div class="mb-4">
                                @foreach ($criteria as $idx => $crit)
                                    <div class="mb-2">
                                        <div class="flex justify-between text-[10px] font-semibold text-muted mb-1">
                                            <span>{{ $crit->name }} ({{ $crit->code }})</span>
                                            <span>{{ number_format(($scenarioWeights[$idx] ?? 0) * 100, 1) }}%</span>
                                        </div>
                                        <div class="h-2 rounded-full bg-black/[0.06] overflow-hidden">
                                            <div class="h-full rounded-full bg-forest" style="width: {{ number_format(($scenarioWeights[$idx] ?? 0) * 100, 1) }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <form method="GET" action="{{ route('admin.spk.sensitivity') }}" class="space-y-3">
                                <input type="hidden" name="history_id" value="{{ $history?->id }}">
                                <input type="hidden" name="scenario" value="custom">
                                @foreach ($criteria as $idx => $crit)
                                    <div>
                                        <label for="spk_weight_{{ $idx }}" class="flex justify-between text-xs mb-1">
                                            <span class="font-bold text-ink">{{ $crit->name }} ({{ $crit->code }})</span>
                                            <span class="font-mono text-forest font-bold">{{ number_format(($scenarioWeights[$idx] ?? 0) * 100, 1) }}%</span>
                                        </label>
                                        <input id="spk_weight_{{ $idx }}" type="number" step="0.01" min="0" max="1" name="w{{ $idx }}"
                                            value="{{ round($scenarioWeights[$idx] ?? 0, 4) }}" class="input text-xs w-full">
                                    </div>
                                @endforeach
                                <button type="submit" class="btn-primary w-full justify-center !py-2.5 text-xs mt-4">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                                    Kalkulasi Ulang Skenario
                                </button>
                            </form>
                            <div class="pt-4 mt-4 border-t border-black/[0.06] text-xs space-y-2">
                                <p class="font-bold text-ink">Kesimpulan Stabilitas:</p>
                                @if ($isRankingStable)
                                    <div class="p-3 bg-[#DFF0E4] text-success rounded-xl border border-forest/20 font-medium">
                                        <strong>Model Stabil (Robust):</strong> Pergeseran bobot tidak mengubah pemenang peringkat 1.
                                    </div>
                                @else
                                    <div class="p-3 bg-brass-soft text-brass rounded-xl border border-brass/30 font-medium">
                                        <strong>Peka terhadap Bobot (Sensitive):</strong> Terjadi pergeseran peringkat pada alternatif distributor.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- rank comparison -->
                    <div class="lg:col-span-2">
                        <div class="card p-6">
                            <div class="flex flex-wrap justify-between items-center gap-2 mb-4">
                                <h2 class="text-base font-bold text-ink tracking-tight">Perbandingan Peringkat (Baseline vs Skenario)</h2>
                                <span class="badge {{ $isRankingStable ? 'bg-[#DFF0E4] text-success' : 'bg-brass-soft text-brass' }}">
                                    {{ $isRankingStable ? 'Peringkat Konsisten' : 'Peringkat Bergeser' }}
                                </span>
                            </div>
                            @if (count($comparison) > 0)
                                <div class="hidden sm:block overflow-x-auto">
                                    <table class="tbl w-full">
                                        <thead>
                                            <tr>
                                                <th>Alternatif Distributor</th>
                                                <th class="text-center">Rank Awal (AHP Baseline)</th>
                                                <th class="text-center">Skor Awal</th>
                                                <th class="text-center">Rank Baru (Skenario)</th>
                                                <th class="text-center">Skor Baru</th>
                                                <th class="text-center">Perubahan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($comparison as $row)
                                                <tr>
                                                    <td class="font-bold text-ink text-xs">{{ $row['name'] }}</td>
                                                    <td class="text-center font-mono font-bold">#{{ $row['base_rank'] ?? '-' }}</td>
                                                    <td class="text-center font-mono text-xs text-muted">{{ number_format($row['base_score'], 4) }}</td>
                                                    <td class="text-center font-mono font-extrabold text-forest">#{{ $row['new_rank'] }}</td>
                                                    <td class="text-center font-mono text-xs font-bold text-ink">{{ number_format($row['new_score'], 4) }}</td>
                                                    <td class="text-center">
                                                        @if ($row['rank_diff'] > 0)
                                                            <span class="badge bg-[#DFF0E4] text-success !text-[10px] font-bold">Naik {{ $row['rank_diff'] }}</span>
                                                        @elseif ($row['rank_diff'] < 0)
                                                            <span class="badge bg-danger/10 text-danger !text-[10px] font-bold">Turun {{ abs($row['rank_diff']) }}</span>
                                                        @else
                                                            <span class="badge bg-cream text-muted !text-[10px]">Tetap (Rank {{ $row['new_rank'] }})</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="sm:hidden">
                                    @foreach ($comparison as $row)
                                        <div class="card !rounded-2xl p-3.5 mb-2.5">
                                            <div class="flex justify-between items-start gap-3">
                                                <p class="font-bold text-ink text-sm min-w-0">{{ $row['name'] }}</p>
                                                @if ($row['rank_diff'] > 0)
                                                    <span class="badge bg-[#DFF0E4] text-success !text-[10px] font-bold shrink-0">Naik {{ $row['rank_diff'] }}</span>
                                                @elseif ($row['rank_diff'] < 0)
                                                    <span class="badge bg-danger/10 text-danger !text-[10px] font-bold shrink-0">Turun {{ abs($row['rank_diff']) }}</span>
                                                @else
                                                    <span class="badge bg-cream text-muted !text-[10px] shrink-0">Tetap</span>
                                                @endif
                                            </div>
                                            <div class="grid grid-cols-2 gap-x-3 gap-y-1.5 text-xs pt-2 mt-2 border-t border-black/[0.04]">
                                                <span class="text-muted">Rank awal</span>
                                                <span class="text-right font-mono font-bold text-ink">#{{ $row['base_rank'] ?? '-' }} &bull; {{ number_format($row['base_score'], 4) }}</span>
                                                <span class="text-muted">Rank baru</span>
                                                <span class="text-right font-mono font-extrabold text-forest">#{{ $row['new_rank'] }} &bull; {{ number_format($row['new_score'], 4) }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="pt-4 mt-4 border-t border-black/[0.06] space-y-2">
                                    <h3 class="text-xs font-bold text-ink">Validasi Silang dengan Skor SAW (Skenario Saat Ini):</h3>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        @foreach ($scenarioSaw as $saw)
                                            <div class="p-3 bg-white rounded-xl border border-black/5 text-xs">
                                                <div class="text-[11px] text-muted font-medium">Rank #{{ $saw['rank'] }} (SAW)</div>
                                                <div class="font-bold text-ink truncate mt-0.5">{{ $saw['name'] }}</div>
                                                <div class="font-mono text-forest font-bold mt-1">Nilai: {{ number_format($saw['saw_score'], 4) }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="py-10 text-center border-t border-black/[0.04]">
                                    <p class="font-bold text-ink text-sm">Tidak ada data alternatif yang diuji.</p>
                                    <p class="text-[0.8125rem] text-muted mt-1.5 max-w-md mx-auto">Jalankan kalkulasi SPK terlebih dahulu agar tersedia data peringkat untuk diuji sensitivitasnya.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</x-layouts.admin>
