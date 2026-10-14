<x-layouts.app>
    <x-slot name="title">Analisis Sensitivitas SPK</x-slot>

    <div class="space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <span class="eyebrow">Academic &amp; Robustness Testing</span>
                <h1 class="font-display font-semibold text-[1.75rem] sm:text-4xl text-brand-dark tracking-tight text-balance mt-2">Uji Analisis Sensitivitas SPK</h1>
                <p class="text-[0.8125rem] text-brand-slate mt-1">Uji stabilitas dan ketahanan pemeringkatan TOPSIS &amp; SAW terhadap variasi pergeseran bobot kriteria.</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2.5 w-full sm:w-auto">
                <a href="{{ route('admin.spk.index') }}" class="btn-back w-full sm:w-auto justify-center">
                    Kembali ke Kriteria
                </a>
                @if ($history)
                    <a href="{{ route('admin.spk.export', $history) }}" target="_blank" class="btn-primary text-xs py-2.5 px-4 font-bold w-full sm:w-auto justify-center">
                        Cetak Laporan PDF
                    </a>
                @endif
            </div>
        </div>

        <!-- Scenario Presets Bar -->
        <div class="card-shell">
            <div class="card-core p-5">
                <div class="flex flex-col gap-3">
                    <span class="text-xs font-bold text-brand-dark">Pilih Skenario Sensitivitas:</span>
                    <div class="flex flex-wrap gap-2 text-xs">
                        <a href="{{ route('admin.spk.sensitivity', ['history_id' => $history?->id, 'scenario' => 'custom']) }}"
                            class="min-h-11 inline-flex items-center px-3.5 rounded-xl border {{ $preset === 'custom' ? 'bg-brand-primary text-white font-bold border-brand-primary' : 'bg-white text-brand-dark border-black/10 hover:bg-black/5' }}">
                            Bobot Baseline (AHP Default)
                        </a>
                        <a href="{{ route('admin.spk.sensitivity', ['history_id' => $history?->id, 'scenario' => 'cost_priority']) }}"
                            class="min-h-11 inline-flex items-center px-3.5 rounded-xl border {{ $preset === 'cost_priority' ? 'bg-brand-primary text-white font-bold border-brand-primary' : 'bg-white text-brand-dark border-black/10 hover:bg-black/5' }}">
                            Fokus Biaya Murah (Harga 60%)
                        </a>
                        <a href="{{ route('admin.spk.sensitivity', ['history_id' => $history?->id, 'scenario' => 'quality_priority']) }}"
                            class="min-h-11 inline-flex items-center px-3.5 rounded-xl border {{ $preset === 'quality_priority' ? 'bg-brand-primary text-white font-bold border-brand-primary' : 'bg-white text-brand-dark border-black/10 hover:bg-black/5' }}">
                            Fokus Kualitas &amp; Kecepatan (40%/30%)
                        </a>
                        <a href="{{ route('admin.spk.sensitivity', ['history_id' => $history?->id, 'scenario' => 'distance_priority']) }}"
                            class="min-h-11 inline-flex items-center px-3.5 rounded-xl border {{ $preset === 'distance_priority' ? 'bg-brand-primary text-white font-bold border-brand-primary' : 'bg-white text-brand-dark border-black/10 hover:bg-black/5' }}">
                            Fokus Jarak Terdekat (Jarak 35%)
                        </a>
                        <a href="{{ route('admin.spk.sensitivity', ['history_id' => $history?->id, 'scenario' => 'equal_weights']) }}"
                            class="min-h-11 inline-flex items-center px-3.5 rounded-xl border {{ $preset === 'equal_weights' ? 'bg-brand-primary text-white font-bold border-brand-primary' : 'bg-white text-brand-dark border-black/10 hover:bg-black/5' }}">
                            Bobot Rata (20% Masing-masing)
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Parameter Skenario Form -->
            <div class="card-shell lg:col-span-1">
                <div class="card-core p-6 space-y-5">
                    <h2 class="text-base font-bold text-brand-dark tracking-tight">Parameter Bobot Skenario</h2>
                    <form method="GET" action="{{ route('admin.spk.sensitivity') }}" class="space-y-4">
                        <input type="hidden" name="history_id" value="{{ $history?->id }}">
                        <input type="hidden" name="scenario" value="custom">

                        @foreach ($criteria as $idx => $crit)
                            <div>
                                <label for="spk_weight_{{ $idx }}" class="flex justify-between text-xs mb-1">
                                    <span class="font-bold text-brand-dark">{{ $crit->name }} ({{ $crit->code }})</span>
                                    <span class="font-mono text-brand-primary font-bold">
                                        {{ number_format(($scenarioWeights[$idx] ?? 0) * 100, 1) }}%
                                    </span>
                                </label>
                                <input id="spk_weight_{{ $idx }}" type="number" step="0.01" min="0" max="1"
                                    name="w{{ $idx }}"
                                    value="{{ round($scenarioWeights[$idx] ?? 0, 4) }}"
                                    class="input-field text-xs w-full">
                            </div>
                        @endforeach

                        <button type="submit" class="btn-primary w-full justify-center py-2.5 text-xs font-bold mt-2">
                            Kalkulasi Ulang Skenario
                        </button>
                    </form>

                    <div class="pt-4 border-t border-black/[0.06] text-xs text-brand-slate space-y-1">
                        <p class="font-bold text-brand-dark">Kesimpulan Stabilitas:</p>
                        @if ($isRankingStable)
                            <div class="p-3 bg-brand-mint text-brand-emerald rounded-xl border border-brand-primary/20 font-medium">
                                <strong>Model Stabil (Robust):</strong> Pergeseran bobot tidak mengubah pemenang peringkat 1.
                            </div>
                        @else
                            <div class="p-3 bg-brand-ochre-soft text-brand-brass rounded-xl border border-brand-accent/25 font-medium">
                                <strong>Peka terhadap Bobot (Sensitive):</strong> Terjadi pergeseran peringkat pada alternatif distributor.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Comparison Table -->
            <div class="card-shell lg:col-span-2">
                <div class="card-core p-6 space-y-5">
                    <div class="flex flex-wrap justify-between items-center gap-2">
                        <h2 class="text-base font-bold text-brand-dark tracking-tight">Perbandingan Peringkat (Baseline vs Skenario)</h2>
                        <span class="badge {{ $isRankingStable ? 'bg-brand-mint text-brand-emerald' : 'bg-brand-ochre-soft text-brand-brass' }}">
                            {{ $isRankingStable ? 'Peringkat Konsisten' : 'Peringkat Bergeser' }}
                        </span>
                    </div>

                    @if (count($comparison) > 0)
                        <!-- Desktop: table -->
                        <div class="hidden sm:block overflow-x-auto">
                            <table class="table-premium">
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
                                            <td class="font-bold text-brand-dark text-xs">
                                                {{ $row['name'] }}
                                            </td>
                                            <td class="text-center font-mono font-bold">
                                                #{{ $row['base_rank'] ?? '-' }}
                                            </td>
                                            <td class="text-center font-mono text-xs text-brand-slate">
                                                {{ number_format($row['base_score'], 4) }}
                                            </td>
                                            <td class="text-center font-mono font-extrabold text-brand-primary">
                                                #{{ $row['new_rank'] }}
                                            </td>
                                            <td class="text-center font-mono text-xs font-bold text-brand-dark">
                                                {{ number_format($row['new_score'], 4) }}
                                            </td>
                                            <td class="text-center">
                                                @if ($row['rank_diff'] > 0)
                                                    <span class="badge bg-brand-mint text-brand-emerald text-[10px] font-bold">
                                                        Naik {{ $row['rank_diff'] }}
                                                    </span>
                                                @elseif ($row['rank_diff'] < 0)
                                                    <span class="badge bg-brand-brick/10 text-brand-brick text-[10px] font-bold">
                                                        Turun {{ abs($row['rank_diff']) }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-brand-cream text-brand-slate text-[10px]">
                                                        Tetap (Rank {{ $row['new_rank'] }})
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile: cards -->
                        <div class="sm:hidden space-y-3">
                            @foreach ($comparison as $row)
                                <div class="list-card space-y-2">
                                    <div class="flex justify-between items-start gap-3">
                                        <p class="font-bold text-brand-dark text-sm min-w-0">{{ $row['name'] }}</p>
                                        @if ($row['rank_diff'] > 0)
                                            <span class="badge bg-brand-mint text-brand-emerald text-[10px] font-bold shrink-0">Naik {{ $row['rank_diff'] }}</span>
                                        @elseif ($row['rank_diff'] < 0)
                                            <span class="badge bg-brand-brick/10 text-brand-brick text-[10px] font-bold shrink-0">Turun {{ abs($row['rank_diff']) }}</span>
                                        @else
                                            <span class="badge bg-brand-cream text-brand-slate text-[10px] shrink-0">Tetap</span>
                                        @endif
                                    </div>
                                    <div class="grid grid-cols-2 gap-x-3 gap-y-1.5 text-[0.75rem] pt-2 border-t border-black/[0.04]">
                                        <span class="text-brand-slate">Rank awal</span>
                                        <span class="text-right font-mono font-bold text-brand-dark">#{{ $row['base_rank'] ?? '-' }} &bull; {{ number_format($row['base_score'], 4) }}</span>
                                        <span class="text-brand-slate">Rank baru</span>
                                        <span class="text-right font-mono font-extrabold text-brand-primary">#{{ $row['new_rank'] }} &bull; {{ number_format($row['new_score'], 4) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Cross-validation with SAW -->
                        <div class="pt-4 border-t border-black/[0.06] space-y-2">
                            <h3 class="text-xs font-bold text-brand-dark">Validasi Silang dengan Skor SAW (Skenario Saat Ini):</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                @foreach ($scenarioSaw as $saw)
                                    <div class="p-3 bg-white rounded-xl border border-black/5 text-xs">
                                        <div class="text-[11px] text-brand-slate font-medium">Rank #{{ $saw['rank'] }} (SAW)</div>
                                        <div class="font-bold text-brand-dark truncate mt-0.5">{{ $saw['name'] }}</div>
                                        <div class="font-mono text-brand-primary font-bold mt-1">Nilai: {{ number_format($saw['saw_score'], 4) }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="py-10 text-center border-t border-black/[0.04]">
                            <p class="font-bold text-brand-dark text-sm">Tidak ada data alternatif yang diuji.</p>
                            <p class="text-[0.8125rem] text-brand-slate mt-1.5 max-w-md mx-auto">Jalankan kalkulasi SPK terlebih dahulu agar tersedia data peringkat untuk diuji sensitivitasnya.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
