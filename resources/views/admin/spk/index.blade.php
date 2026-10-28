<x-layouts.admin title="Kriteria SPK (AHP + TOPSIS + SAW)">
    <!-- ============================ MOBILE ============================ -->
    <div class="lg:hidden pb-24">
        <!-- greeting -->
        <div class="px-4 pt-2 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="relative" x-data="{ menuOpen: false }">
                    <button type="button" @click="menuOpen = !menuOpen" class="w-11 h-11 rounded-2xl bg-forest text-white flex items-center justify-center font-extrabold text-sm cursor-pointer">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</button>
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
                    <p class="text-[15px] font-extrabold leading-tight">{{ auth()->user()->name }}</p>
                </div>
            </div>
            <x-notification-bell size="sm" align="right" />
        </div>

        <!-- body -->
        <div class="px-4 py-4">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <div>
                    <span class="eyebrow text-brass">Decision Support Engine</span>
                    <h1 class="text-[1.75rem] sm:text-3xl font-extrabold text-ink tracking-tight mt-2">SPK (AHP + TOPSIS + SAW)</h1>
                    <p class="text-[0.8125rem] text-muted mt-1">Kelola kriteria dan kalkulasi bobot AHP.</p>
                </div>
                <a href="{{ route('admin.spk.sensitivity') }}" class="btn-primary !text-xs !py-2.5 w-full justify-center">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg> Analisis Sensitivitas
                </a>
            </div>

            <div class="card p-6">
                <div class="flex flex-wrap justify-between items-center gap-2 mb-4">
                    <h2 class="text-base font-bold text-ink tracking-tight">Bobot Kriteria SPK Aktif</h2>
                    <span class="badge bg-[#DFF0E4] text-success">Konsisten (CR &le; 0.1)</span>
                </div>
                <div class="hidden sm:block overflow-x-auto">
                    <table class="tbl w-full">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Kriteria</th>
                                <th>Tipe</th>
                                <th class="text-right">Bobot (AHP)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($criteria as $c)
                                <tr>
                                    <td class="font-mono text-xs font-bold text-forest">{{ $c->code }}</td>
                                    <td class="font-bold text-ink">{{ $c->name }}</td>
                                    <td>
                                        <span class="badge {{ $c->type === 'cost' ? 'bg-brass-soft text-brass' : 'bg-[#DFF0E4] text-success' }}">
                                            {{ strtoupper($c->type) }}
                                        </span>
                                    </td>
                                    <td class="text-right font-extrabold text-ink">{{ number_format($c->default_weight * 100, 2) }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="sm:hidden">
                    @foreach ($criteria as $c)
                        <div class="card !rounded-2xl p-3.5 mb-2.5 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-bold text-ink text-sm">{{ $c->name }}</p>
                                <p class="font-mono text-[11px] text-muted mt-0.5">{{ $c->code }} &bull; {{ strtoupper($c->type) }}</p>
                            </div>
                            <span class="font-extrabold text-ink text-sm shrink-0">{{ number_format($c->default_weight * 100, 2) }}%</span>
                        </div>
                    @endforeach
                </div>
                @if ($latestWeight)
                    <div class="mt-4">
                        <div class="p-4 bg-[#DFF0E4]/70 rounded-2xl text-xs text-forest border border-forest/15 space-y-1">
                            <p class="font-bold">Status Konsistensi AHP Terakhir:</p>
                            <p class="text-muted font-mono text-[11px] break-words">
                                &lambda;<sub>max</sub> = {{ $latestWeight->lambda_max }} &bull; CI = {{ $latestWeight->ci }} &bull; CR = <strong class="text-ink">{{ $latestWeight->cr }}</strong> ({{ $latestWeight->is_consistent ? 'KONSISTEN' : 'TIDAK KONSISTEN' }})
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-4">
                <div class="card p-6">
                    <h2 class="text-base font-bold text-ink tracking-tight">Perbandingan Berpasangan (AHP Pairwise)</h2>
                    <p class="text-xs text-muted mt-0.5 mb-4">Tentukan nilai kepentingan relatif antarkriteria (Skala Saaty 1&ndash;9).</p>

                    <form method="POST" action="{{ route('admin.spk.calculate') }}" class="space-y-4">
                        @csrf
                        <div class="space-y-3 max-h-72 overflow-y-auto pr-2">
                            @php $n = count($criteria); @endphp
                            @for ($i = 0; $i < $n; $i++)
                                @for ($j = $i + 1; $j < $n; $j++)
                                    <div class="flex flex-col sm:flex-row sm:items-center gap-1.5 sm:gap-3 text-xs border-b border-black/[0.04] pb-2.5">
                                        <span class="sm:w-1/3 font-semibold text-ink">{{ $criteria[$i]->name }}</span>
                                        <select name="matrix_{{ $i }}_{{ $j }}" class="input !py-1.5 text-xs bg-white sm:w-44 shrink-0" aria-label="Perbandingan {{ $criteria[$i]->name }} terhadap {{ $criteria[$j]->name }}">
                                            <option value="1">1 - Sama Penting</option>
                                            <option value="2">2 - Nilai Antara 1 &amp; 3</option>
                                            <option value="3" selected>3 - Sedikit Lebih Penting</option>
                                            <option value="5">5 - Lebih Penting</option>
                                            <option value="7">7 - Sangat Penting</option>
                                            <option value="9">9 - Mutlak Penting</option>
                                        </select>
                                        <span class="sm:w-1/3 sm:text-right font-medium text-muted">{{ $criteria[$j]->name }}</span>
                                    </div>
                                @endfor
                            @endfor
                        </div>

                        @if ($errors->any())
                            <div class="p-3.5 rounded-xl bg-danger/10 border border-danger/20 text-xs text-danger space-y-1" role="alert">
                                @foreach ($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        @endif

                        <button type="submit" class="btn-primary w-full justify-center py-3 text-[0.8125rem]">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V13.5Zm0 2.25h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V18Zm4.5-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V18Zm4.5-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V18ZM9 3.75H6.75A2.25 2.25 0 0 0 4.5 6v13.5A2.25 2.25 0 0 0 6.75 21.75h10.5A2.25 2.25 0 0 0 19.5 19.5V6a2.25 2.25 0 0 0-2.25-2.25H15M9 3.75h6M9 3.75v1.5h6V3.75"/></svg> Hitung Bobot AHP &amp; Uji Konsistensi (CR)
                        </button>
                    </form>
                </div>
            </div>

            <div class="card p-6 mt-6">
                <h2 class="text-base font-bold text-ink tracking-tight">Riwayat Kalkulasi Rekomendasi SPK</h2>
                <p class="text-xs text-muted mt-0.5 mb-4">Daftar keputusan multi-kriteria untuk grup pengadaan kolektif dan unduh berkas laporan akademik.</p>

                @if ($histories->isNotEmpty())
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="tbl w-full">
                            <thead>
                                <tr>
                                    <th>ID / Waktu</th>
                                    <th>Grup Pengadaan</th>
                                    <th>Komoditas Bahan</th>
                                    <th>Rekomendasi #1 (TOPSIS)</th>
                                    <th class="text-center">Skor V<sub>i</sub></th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($histories as $hist)
                                    @php $topTopsis = $hist->rankings['topsis'][0] ?? null; @endphp
                                    <tr>
                                        <td>
                                            <div class="font-mono text-xs font-bold text-forest">#SPK-{{ $hist->id }}</div>
                                            <div class="text-[11px] text-muted">{{ $hist->created_at->format('d M Y, H:i') }} WITA</div>
                                        </td>
                                        <td>
                                            <span class="font-mono font-bold text-ink text-xs">{{ $hist->matchingGroup?->group_code ?? 'Manual/Direct' }}</span>
                                        </td>
                                        <td class="font-bold text-ink">{{ $hist->matchingGroup?->product?->name ?? 'Pengadaan Multi-Bahan' }}</td>
                                        <td>
                                            <div class="font-bold text-ink text-xs">{{ $topTopsis['name'] ?? 'N/A' }}</div>
                                            <div class="text-[10px] text-muted">Peringkat 1 dari {{ count($hist->decision_matrix ?? []) }} alternatif</div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-[#DFF0E4] text-success font-mono">{{ isset($topTopsis['score']) ? number_format($topTopsis['score'], 4) : '-' }}</span>
                                        </td>
                                        <td class="text-right whitespace-nowrap">
                                            <a href="{{ route('admin.spk.sensitivity', ['history_id' => $hist->id]) }}" class="btn-ghost !py-1.5 !px-3 text-xs !text-muted">Uji Sensitivitas</a>
                                            <a href="{{ route('admin.spk.export', $hist) }}" target="_blank" class="btn-primary !py-1.5 !px-3.5 text-xs">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg> Laporan PDF
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="sm:hidden">
                        @foreach ($histories as $hist)
                            @php $topTopsis = $hist->rankings['topsis'][0] ?? null; @endphp
                            <div class="card !rounded-2xl p-4 mb-3 space-y-3">
                                <div class="flex justify-between items-start gap-3">
                                    <div>
                                        <p class="font-mono text-xs font-bold text-forest">#SPK-{{ $hist->id }}</p>
                                        <p class="text-[11px] text-muted mt-0.5">{{ $hist->created_at->format('d M Y, H:i') }} WITA</p>
                                    </div>
                                    <span class="badge bg-[#DFF0E4] text-success font-mono shrink-0">{{ isset($topTopsis['score']) ? number_format($topTopsis['score'], 4) : '-' }}</span>
                                </div>
                                <div class="text-xs space-y-1 pt-2 border-t border-black/[0.04]">
                                    <p class="text-muted">Grup: <span class="font-mono font-bold text-ink">{{ $hist->matchingGroup?->group_code ?? 'Manual/Direct' }}</span></p>
                                    <p class="font-bold text-ink">{{ $hist->matchingGroup?->product?->name ?? 'Pengadaan Multi-Bahan' }}</p>
                                    <p class="text-muted">Rekomendasi #1: <span class="font-bold text-ink">{{ $topTopsis['name'] ?? 'N/A' }}</span></p>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <a href="{{ route('admin.spk.sensitivity', ['history_id' => $hist->id]) }}" class="btn-ghost text-xs justify-center">Uji Sensitivitas</a>
                                    <a href="{{ route('admin.spk.export', $hist) }}" target="_blank" class="btn-primary text-xs justify-center">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg> Laporan PDF
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4">{{ $histories->links() }}</div>
                @else
                    <div class="py-10 text-center border-t border-black/[0.04]">
                        <p class="font-bold text-ink text-sm">Belum ada riwayat kalkulasi SPK yang tersimpan.</p>
                        <p class="text-[0.8125rem] text-muted mt-1.5 max-w-md mx-auto">Riwayat muncul setelah Anda menghitung bobot AHP atau setelah grup pengadaan UMKM terbentuk.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- bottom tab -->
    <x-mobile-tab role="admin" active="spk" :badges="['proposals' => $pendingProposals]" />

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
                </a>
                <a href="{{ route('admin.spk.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-forest text-white">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 20h18M6 16v-5m6 5V8m6 8v-9"/></svg>
                    Kriteria SPK
                </a>
            </nav>
            <div class="p-4 border-t border-line">
                <a href="{{ route('profile.complete') }}" class="flex items-center gap-3 px-2">
                    <span class="w-10 h-10 rounded-2xl bg-forest text-white flex items-center justify-center font-extrabold text-xs">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                    <div class="min-w-0">
                        <p class="text-sm font-bold truncate">{{ auth()->user()->name }}</p>
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
                        <h1 class="text-lg font-extrabold leading-tight">Sistem Pendukung Keputusan</h1>
                        <p class="text-xs text-muted">{{ now()->translatedFormat('l, d F Y') }}</p>
                    </div>
                    <div class="ml-auto flex items-center gap-3">
                        <div class="hidden lg:flex items-center gap-2 bg-white border border-line rounded-2xl pl-4 pr-2 py-2 w-72">
                            <svg class="w-4 h-4 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21 21-5.2-5.2m0 0A7.5 7.5 0 1 0 5.2 5.2a7.5 7.5 0 0 0 10.6 10.6Z"/></svg>
                            <input class="flex-1 bg-transparent text-sm outline-none placeholder:text-muted/70" placeholder="Cari UMKM, distributor...">
                        </div>
                        <x-notification-bell align="right" />
                    </div>
                </div>
            </header>

            <main class="px-8 py-6 max-w-6xl">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div>
                        <span class="eyebrow text-brass">Decision Support Engine</span>
                        <h1 class="text-[1.75rem] sm:text-3xl font-extrabold text-ink tracking-tight mt-2">Sistem Pendukung Keputusan (AHP + TOPSIS + SAW)</h1>
                        <p class="text-[0.8125rem] text-muted mt-1">Kelola kriteria dan kalkulasi bobot AHP untuk rekomendasi distributor terbaik pilihan UMKM.</p>
                    </div>
                    <a href="{{ route('admin.spk.sensitivity') }}" class="btn-primary !text-xs !py-2.5 !px-4 w-full sm:w-auto justify-center">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg> Analisis Sensitivitas
                    </a>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="card p-6">
                        <div class="flex flex-wrap justify-between items-center gap-2 mb-4">
                            <h2 class="text-base font-bold text-ink tracking-tight">Bobot Kriteria SPK Aktif</h2>
                            <span class="badge bg-[#DFF0E4] text-success">Konsisten (CR &le; 0.1)</span>
                        </div>
                        <div class="hidden sm:block overflow-x-auto">
                            <table class="tbl w-full">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Kriteria</th>
                                        <th>Tipe</th>
                                        <th class="text-right">Bobot (AHP)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($criteria as $c)
                                        <tr>
                                            <td class="font-mono text-xs font-bold text-forest">{{ $c->code }}</td>
                                            <td class="font-bold text-ink">{{ $c->name }}</td>
                                            <td>
                                                <span class="badge {{ $c->type === 'cost' ? 'bg-brass-soft text-brass' : 'bg-[#DFF0E4] text-success' }}">
                                                    {{ strtoupper($c->type) }}
                                                </span>
                                            </td>
                                            <td class="text-right font-extrabold text-ink">{{ number_format($c->default_weight * 100, 2) }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="sm:hidden">
                            @foreach ($criteria as $c)
                                <div class="card !rounded-2xl p-3.5 mb-2.5 flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-bold text-ink text-sm">{{ $c->name }}</p>
                                        <p class="font-mono text-[11px] text-muted mt-0.5">{{ $c->code }} &bull; {{ strtoupper($c->type) }}</p>
                                    </div>
                                    <span class="font-extrabold text-ink text-sm shrink-0">{{ number_format($c->default_weight * 100, 2) }}%</span>
                                </div>
                            @endforeach
                        </div>
                        @if ($latestWeight)
                            <div class="mt-4">
                                <div class="p-4 bg-[#DFF0E4]/70 rounded-2xl text-xs text-forest border border-forest/15 space-y-1">
                                    <p class="font-bold">Status Konsistensi AHP Terakhir:</p>
                                    <p class="text-muted font-mono text-[11px] break-words">
                                        &lambda;<sub>max</sub> = {{ $latestWeight->lambda_max }} &bull; CI = {{ $latestWeight->ci }} &bull; CR = <strong class="text-ink">{{ $latestWeight->cr }}</strong> ({{ $latestWeight->is_consistent ? 'KONSISTEN' : 'TIDAK KONSISTEN' }})
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="card p-6">
                        <h2 class="text-base font-bold text-ink tracking-tight">Perbandingan Berpasangan (AHP Pairwise)</h2>
                        <p class="text-xs text-muted mt-0.5 mb-4">Tentukan nilai kepentingan relatif antarkriteria (Skala Saaty 1&ndash;9).</p>

                        <form method="POST" action="{{ route('admin.spk.calculate') }}" class="space-y-4">
                            @csrf
                            <div class="space-y-3 max-h-72 overflow-y-auto pr-2">
                                @php $n = count($criteria); @endphp
                                @for ($i = 0; $i < $n; $i++)
                                    @for ($j = $i + 1; $j < $n; $j++)
                                        <div class="flex flex-col sm:flex-row sm:items-center gap-1.5 sm:gap-3 text-xs border-b border-black/[0.04] pb-2.5">
                                            <span class="sm:w-1/3 font-semibold text-ink">{{ $criteria[$i]->name }}</span>
                                            <select name="matrix_{{ $i }}_{{ $j }}" class="input !py-1.5 text-xs bg-white sm:w-44 shrink-0" aria-label="Perbandingan {{ $criteria[$i]->name }} terhadap {{ $criteria[$j]->name }}">
                                                <option value="1">1 - Sama Penting</option>
                                                <option value="2">2 - Nilai Antara 1 &amp; 3</option>
                                                <option value="3" selected>3 - Sedikit Lebih Penting</option>
                                                <option value="5">5 - Lebih Penting</option>
                                                <option value="7">7 - Sangat Penting</option>
                                                <option value="9">9 - Mutlak Penting</option>
                                            </select>
                                            <span class="sm:w-1/3 sm:text-right font-medium text-muted">{{ $criteria[$j]->name }}</span>
                                        </div>
                                    @endfor
                                @endfor
                            </div>

                            @if ($errors->any())
                                <div class="p-3.5 rounded-xl bg-danger/10 border border-danger/20 text-xs text-danger space-y-1" role="alert">
                                    @foreach ($errors->all() as $error)
                                        <p>{{ $error }}</p>
                                    @endforeach
                                </div>
                            @endif

                            <button type="submit" class="btn-primary w-full justify-center py-3 text-[0.8125rem]">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V13.5Zm0 2.25h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V18Zm4.5-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V18Zm4.5-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V18ZM9 3.75H6.75A2.25 2.25 0 0 0 4.5 6v13.5A2.25 2.25 0 0 0 6.75 21.75h10.5A2.25 2.25 0 0 0 19.5 19.5V6a2.25 2.25 0 0 0-2.25-2.25H15M9 3.75h6M9 3.75v1.5h6V3.75"/></svg> Hitung Bobot AHP &amp; Uji Konsistensi (CR)
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card p-6 mt-6">
                    <h2 class="text-base font-bold text-ink tracking-tight">Riwayat Kalkulasi Rekomendasi SPK</h2>
                    <p class="text-xs text-muted mt-0.5 mb-4">Daftar keputusan multi-kriteria untuk grup pengadaan kolektif dan unduh berkas laporan akademik.</p>

                    @if ($histories->isNotEmpty())
                        <div class="hidden sm:block overflow-x-auto">
                            <table class="tbl w-full">
                                <thead>
                                    <tr>
                                        <th>ID / Waktu</th>
                                        <th>Grup Pengadaan</th>
                                        <th>Komoditas Bahan</th>
                                        <th>Rekomendasi #1 (TOPSIS)</th>
                                        <th class="text-center">Skor V<sub>i</sub></th>
                                        <th class="text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($histories as $hist)
                                        @php $topTopsis = $hist->rankings['topsis'][0] ?? null; @endphp
                                        <tr>
                                            <td>
                                                <div class="font-mono text-xs font-bold text-forest">#SPK-{{ $hist->id }}</div>
                                                <div class="text-[11px] text-muted">{{ $hist->created_at->format('d M Y, H:i') }} WITA</div>
                                            </td>
                                            <td>
                                                <span class="font-mono font-bold text-ink text-xs">{{ $hist->matchingGroup?->group_code ?? 'Manual/Direct' }}</span>
                                            </td>
                                            <td class="font-bold text-ink">{{ $hist->matchingGroup?->product?->name ?? 'Pengadaan Multi-Bahan' }}</td>
                                            <td>
                                                <div class="font-bold text-ink text-xs">{{ $topTopsis['name'] ?? 'N/A' }}</div>
                                                <div class="text-[10px] text-muted">Peringkat 1 dari {{ count($hist->decision_matrix ?? []) }} alternatif</div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-[#DFF0E4] text-success font-mono">{{ isset($topTopsis['score']) ? number_format($topTopsis['score'], 4) : '-' }}</span>
                                            </td>
                                            <td class="text-right whitespace-nowrap">
                                                <a href="{{ route('admin.spk.sensitivity', ['history_id' => $hist->id]) }}" class="btn-ghost !py-1.5 !px-3 text-xs !text-muted">Uji Sensitivitas</a>
                                                <a href="{{ route('admin.spk.export', $hist) }}" target="_blank" class="btn-primary !py-1.5 !px-3.5 text-xs">
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg> Laporan PDF
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="sm:hidden">
                            @foreach ($histories as $hist)
                                @php $topTopsis = $hist->rankings['topsis'][0] ?? null; @endphp
                                <div class="card !rounded-2xl p-4 mb-3 space-y-3">
                                    <div class="flex justify-between items-start gap-3">
                                        <div>
                                            <p class="font-mono text-xs font-bold text-forest">#SPK-{{ $hist->id }}</p>
                                            <p class="text-[11px] text-muted mt-0.5">{{ $hist->created_at->format('d M Y, H:i') }} WITA</p>
                                        </div>
                                        <span class="badge bg-[#DFF0E4] text-success font-mono shrink-0">{{ isset($topTopsis['score']) ? number_format($topTopsis['score'], 4) : '-' }}</span>
                                    </div>
                                    <div class="text-xs space-y-1 pt-2 border-t border-black/[0.04]">
                                        <p class="text-muted">Grup: <span class="font-mono font-bold text-ink">{{ $hist->matchingGroup?->group_code ?? 'Manual/Direct' }}</span></p>
                                        <p class="font-bold text-ink">{{ $hist->matchingGroup?->product?->name ?? 'Pengadaan Multi-Bahan' }}</p>
                                        <p class="text-muted">Rekomendasi #1: <span class="font-bold text-ink">{{ $topTopsis['name'] ?? 'N/A' }}</span></p>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <a href="{{ route('admin.spk.sensitivity', ['history_id' => $hist->id]) }}" class="btn-ghost text-xs justify-center">Uji Sensitivitas</a>
                                        <a href="{{ route('admin.spk.export', $hist) }}" target="_blank" class="btn-primary text-xs justify-center">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg> Laporan PDF
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4">{{ $histories->links() }}</div>
                    @else
                        <div class="py-10 text-center border-t border-black/[0.04]">
                            <p class="font-bold text-ink text-sm">Belum ada riwayat kalkulasi SPK yang tersimpan.</p>
                            <p class="text-[0.8125rem] text-muted mt-1.5 max-w-md mx-auto">Riwayat muncul setelah Anda menghitung bobot AHP atau setelah grup pengadaan UMKM terbentuk.</p>
                        </div>
                    @endif
                </div>
            </main>
        </div>
    </div>
</x-layouts.admin>
