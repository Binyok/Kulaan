<x-layouts.app>
    <x-slot name="title">Kriteria SPK & AHP</x-slot>

    <div class="space-y-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <span class="eyebrow">Decision Support Engine</span>
                <h1 class="font-display font-semibold text-[1.75rem] sm:text-4xl text-brand-dark tracking-tight text-balance mt-2">Sistem Pendukung Keputusan (AHP + TOPSIS + SAW)</h1>
                <p class="text-[0.8125rem] text-brand-slate mt-1">Kelola kriteria dan kalkulasi bobot AHP (Analytical Hierarchy Process) untuk penentuan distributor terbaik.</p>
            </div>
            <a href="{{ route('admin.spk.sensitivity') }}" class="btn-primary text-xs py-2.5 px-4 font-bold w-full sm:w-auto justify-center">
                Analisis Sensitivitas
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Active Weights -->
            <div class="card-shell">
                <div class="card-core p-6 sm:p-7 space-y-5">
                    <div class="flex flex-wrap justify-between items-center gap-2">
                        <h2 class="text-base font-bold text-brand-dark tracking-tight">Bobot Kriteria SPK Aktif</h2>
                        <span class="badge bg-brand-mint text-brand-emerald">Konsisten (CR &le; 0.1)</span>
                    </div>

                    <!-- Desktop: table -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="table-premium">
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
                                        <td class="font-mono text-xs font-bold text-brand-primary">{{ $c->code }}</td>
                                        <td class="font-bold text-brand-dark">{{ $c->name }}</td>
                                        <td>
                                            <span class="badge {{ $c->type === 'cost' ? 'bg-brand-ochre-soft text-brand-brass' : 'bg-brand-mint text-brand-emerald' }}">
                                                {{ strtoupper($c->type) }}
                                            </span>
                                        </td>
                                        <td class="text-right font-extrabold text-brand-dark">
                                            {{ number_format($c->default_weight * 100, 2) }}%
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile: list -->
                    <div class="sm:hidden space-y-2.5">
                        @foreach ($criteria as $c)
                            <div class="list-card flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-bold text-brand-dark text-sm">{{ $c->name }}</p>
                                    <p class="font-mono text-[0.6875rem] text-brand-slate mt-0.5">
                                        {{ $c->code }} &bull; {{ strtoupper($c->type) }}
                                    </p>
                                </div>
                                <span class="font-extrabold text-brand-dark text-sm shrink-0">{{ number_format($c->default_weight * 100, 2) }}%</span>
                            </div>
                        @endforeach
                    </div>

                    @if ($latestWeight)
                        <div class="p-4 bg-brand-mint/60 rounded-2xl text-[0.75rem] text-brand-primary border border-brand-primary/15 space-y-1">
                            <p class="font-bold">Status Konsistensi AHP Terakhir:</p>
                            <p class="text-brand-slate font-mono text-[0.6875rem] break-words">
                                &lambda;<sub>max</sub> = {{ $latestWeight->lambda_max }} &bull; CI = {{ $latestWeight->ci }} &bull; CR = <strong class="text-brand-dark">{{ $latestWeight->cr }}</strong> ({{ $latestWeight->is_consistent ? 'KONSISTEN' : 'TIDAK KONSISTEN' }})
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Pairwise Matrix Form -->
            <div class="card-shell">
                <div class="card-core p-6 sm:p-7 space-y-5">
                    <div>
                        <h2 class="text-base font-bold text-brand-dark tracking-tight">Perbandingan Berpasangan (AHP Pairwise)</h2>
                        <p class="text-[0.75rem] text-brand-slate mt-0.5">Tentukan nilai kepentingan relatif antarkriteria (Skala Saaty 1&ndash;9).</p>
                    </div>

                    <form method="POST" action="{{ route('admin.spk.calculate') }}" class="space-y-4">
                        @csrf
                        <div class="space-y-3 max-h-72 overflow-y-auto pr-2">
                            @php $n = count($criteria); @endphp
                            @for ($i = 0; $i < $n; $i++)
                                @for ($j = $i + 1; $j < $n; $j++)
                                    <div class="flex flex-col sm:flex-row sm:items-center gap-1.5 sm:gap-3 text-xs border-b border-black/[0.04] pb-2.5">
                                        <span class="sm:w-1/3 font-semibold text-brand-dark">{{ $criteria[$i]->name }}</span>
                                        <select name="matrix_{{ $i }}_{{ $j }}" class="input-field py-1.5 text-xs bg-white sm:w-40 shrink-0" aria-label="Perbandingan {{ $criteria[$i]->name }} terhadap {{ $criteria[$j]->name }}">
                                            <option value="1">1 - Sama Penting</option>
                                            <option value="2">2 - Nilai Antara 1 &amp; 3</option>
                                            <option value="3" selected>3 - Sedikit Lebih Penting</option>
                                            <option value="5">5 - Lebih Penting</option>
                                            <option value="7">7 - Sangat Penting</option>
                                            <option value="9">9 - Mutlak Penting</option>
                                        </select>
                                        <span class="sm:w-1/3 sm:text-right font-medium text-brand-slate">{{ $criteria[$j]->name }}</span>
                                    </div>
                                @endfor
                            @endfor
                        </div>

                        @if ($errors->any())
                            <div class="p-3.5 rounded-xl bg-red-50 border border-brand-brick/20 text-[0.75rem] text-brand-brick space-y-1" role="alert">
                                @foreach ($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        @endif

                        <button type="submit" class="btn-primary w-full justify-center py-3 text-[0.8125rem] font-bold">
                            Hitung Bobot AHP &amp; Uji Konsistensi (CR)
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- History & PDF Export -->
        <div class="card-shell">
            <div class="card-core p-6 sm:p-7 space-y-5">
                <div>
                    <h2 class="text-base font-bold text-brand-dark tracking-tight">Riwayat Kalkulasi Rekomendasi SPK</h2>
                    <p class="text-[0.75rem] text-brand-slate mt-0.5">Daftar keputusan multi-kriteria untuk grup pengadaan kolektif dan unduh berkas laporan akademik.</p>
                </div>

                @if ($histories->isNotEmpty())
                    <!-- Desktop: table -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="table-premium">
                            <thead>
                                <tr>
                                    <th>ID / Waktu</th>
                                    <th>Grup Pengadaan</th>
                                    <th>Komoditas Bahan</th>
                                    <th>Distributor Terpilih (TOPSIS)</th>
                                    <th class="text-center">Skor V<sub>i</sub></th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($histories as $hist)
                                    @php $topTopsis = $hist->rankings['topsis'][0] ?? null; @endphp
                                    <tr>
                                        <td>
                                            <div class="font-mono text-xs font-bold text-brand-primary">#SPK-{{ $hist->id }}</div>
                                            <div class="text-[11px] text-brand-slate">{{ $hist->created_at->format('d M Y, H:i') }} WITA</div>
                                        </td>
                                        <td>
                                            <span class="font-mono font-bold text-brand-dark text-xs">
                                                {{ $hist->matchingGroup?->group_code ?? 'Manual/Direct' }}
                                            </span>
                                        </td>
                                        <td class="font-bold text-brand-dark">
                                            {{ $hist->matchingGroup?->product?->name ?? 'Pengadaan Multi-Bahan' }}
                                        </td>
                                        <td>
                                            <div class="font-bold text-brand-dark text-xs">{{ $topTopsis['name'] ?? 'N/A' }}</div>
                                            <div class="text-[10px] text-brand-slate">Peringkat 1 dari {{ count($hist->decision_matrix ?? []) }} alternatif</div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-brand-mint text-brand-emerald font-mono">
                                                {{ isset($topTopsis['score']) ? number_format($topTopsis['score'], 4) : '-' }}
                                            </span>
                                        </td>
                                        <td class="text-right whitespace-nowrap space-x-2">
                                            <a href="{{ route('admin.spk.sensitivity', ['history_id' => $hist->id]) }}" class="btn-ghost text-xs py-1.5 px-3 font-semibold text-brand-slate hover:text-brand-dark border border-black/10">
                                                Uji Sensitivitas
                                            </a>
                                            <a href="{{ route('admin.spk.export', $hist) }}" target="_blank" class="btn-primary text-xs py-1.5 px-3.5 font-bold shadow-sm">
                                                Laporan PDF
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile: cards -->
                    <div class="sm:hidden space-y-3">
                        @foreach ($histories as $hist)
                            @php $topTopsis = $hist->rankings['topsis'][0] ?? null; @endphp
                            <div class="list-card space-y-3">
                                <div class="flex justify-between items-start gap-3">
                                    <div class="min-w-0">
                                        <p class="font-mono text-xs font-bold text-brand-primary">#SPK-{{ $hist->id }}</p>
                                        <p class="text-[0.6875rem] text-brand-slate mt-0.5">{{ $hist->created_at->format('d M Y, H:i') }} WITA</p>
                                    </div>
                                    <span class="badge bg-brand-mint text-brand-emerald font-mono shrink-0">
                                        {{ isset($topTopsis['score']) ? number_format($topTopsis['score'], 4) : '-' }}
                                    </span>
                                </div>
                                <div class="text-xs space-y-1 pt-2 border-t border-black/[0.04]">
                                    <p class="text-brand-slate">
                                        Grup: <span class="font-mono font-bold text-brand-dark">{{ $hist->matchingGroup?->group_code ?? 'Manual/Direct' }}</span>
                                    </p>
                                    <p class="font-bold text-brand-dark">{{ $hist->matchingGroup?->product?->name ?? 'Pengadaan Multi-Bahan' }}</p>
                                    <p class="text-brand-slate">
                                        Terpilih: <span class="font-bold text-brand-dark">{{ $topTopsis['name'] ?? 'N/A' }}</span>
                                    </p>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <a href="{{ route('admin.spk.sensitivity', ['history_id' => $hist->id]) }}" class="btn-ghost text-xs font-semibold w-full">
                                        Uji Sensitivitas
                                    </a>
                                    <a href="{{ route('admin.spk.export', $hist) }}" target="_blank" class="btn-primary text-xs font-bold w-full">
                                        Laporan PDF
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-10 text-center border-t border-black/[0.04]">
                        <p class="font-bold text-brand-dark text-sm">Belum ada riwayat kalkulasi SPK yang tersimpan.</p>
                        <p class="text-[0.8125rem] text-brand-slate mt-1.5 max-w-md mx-auto">Riwayat muncul setelah Anda menghitung bobot AHP atau memproses auto-matching distributor.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
