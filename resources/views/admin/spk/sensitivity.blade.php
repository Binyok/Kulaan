<x-layouts.app>
    <x-slot name="title">Analisis Sensitivitas SPK</x-slot>

    <div class="space-y-8 animate-fade-up">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <span class="eyebrow">Academic &amp; Robustness Testing</span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-brand-dark tracking-tight mt-1.5">Uji Analisis Sensitivitas SPK</h1>
                <p class="text-[0.8125rem] text-brand-slate mt-1">Uji stabilitas dan ketahanan pemeringkatan TOPSIS &amp; SAW terhadap variasi pergeseran bobot kriteria.</p>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="{{ route('admin.spk.index') }}" class="btn-ghost text-xs py-2 px-4 border border-black/10">
                    &larr; Kembali ke Kriteria
                </a>
                @if ($history)
                    <a href="{{ route('admin.spk.export', $history) }}" target="_blank" class="btn-primary text-xs py-2 px-4 font-bold flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <span>Cetak Laporan PDF</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Scenario Presets Bar -->
        <div class="card-shell">
            <div class="card-core p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <span class="text-xs font-bold text-brand-dark">Pilih Skenario Sensitivitas:</span>
                    <div class="flex flex-wrap gap-2 text-xs">
                        <a href="{{ route('admin.spk.sensitivity', ['history_id' => $history?->id, 'scenario' => 'custom']) }}" 
                            class="px-3 py-1.5 rounded-xl border {{ $preset === 'custom' ? 'bg-brand-primary text-white font-bold border-brand-primary' : 'bg-white text-brand-dark border-black/10 hover:bg-black/5' }}">
                            Bobot Baseline (AHP Default)
                        </a>
                        <a href="{{ route('admin.spk.sensitivity', ['history_id' => $history?->id, 'scenario' => 'cost_priority']) }}" 
                            class="px-3 py-1.5 rounded-xl border {{ $preset === 'cost_priority' ? 'bg-brand-primary text-white font-bold border-brand-primary' : 'bg-white text-brand-dark border-black/10 hover:bg-black/5' }}">
                            Fokus Biaya Murah (Harga 60%)
                        </a>
                        <a href="{{ route('admin.spk.sensitivity', ['history_id' => $history?->id, 'scenario' => 'quality_priority']) }}" 
                            class="px-3 py-1.5 rounded-xl border {{ $preset === 'quality_priority' ? 'bg-brand-primary text-white font-bold border-brand-primary' : 'bg-white text-brand-dark border-black/10 hover:bg-black/5' }}">
                            Fokus Kualitas &amp; Kecepatan (40%/30%)
                        </a>
                        <a href="{{ route('admin.spk.sensitivity', ['history_id' => $history?->id, 'scenario' => 'distance_priority']) }}" 
                            class="px-3 py-1.5 rounded-xl border {{ $preset === 'distance_priority' ? 'bg-brand-primary text-white font-bold border-brand-primary' : 'bg-white text-brand-dark border-black/10 hover:bg-black/5' }}">
                            Fokus Jarak Terdekat (Jarak 35%)
                        </a>
                        <a href="{{ route('admin.spk.sensitivity', ['history_id' => $history?->id, 'scenario' => 'equal_weights']) }}" 
                            class="px-3 py-1.5 rounded-xl border {{ $preset === 'equal_weights' ? 'bg-brand-primary text-white font-bold border-brand-primary' : 'bg-white text-brand-dark border-black/10 hover:bg-black/5' }}">
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
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="font-bold text-brand-dark">{{ $crit->name }} ({{ $crit->code }})</span>
                                    <span class="font-mono text-brand-primary font-bold">
                                        {{ number_format(($scenarioWeights[$idx] ?? 0) * 100, 1) }}%
                                    </span>
                                </div>
                                <input type="number" step="0.01" min="0" max="1" 
                                    name="w{{ $idx }}" 
                                    value="{{ round($scenarioWeights[$idx] ?? 0, 4) }}" 
                                    class="input-field text-xs py-1.5 w-full">
                            </div>
                        @endforeach

                        <button type="submit" class="btn-primary w-full justify-center py-2.5 text-xs font-bold mt-2">
                            Kalkulasi Ulang Skenario
                        </button>
                    </form>

                    <div class="pt-4 border-t border-black/[0.06] text-xs text-brand-slate space-y-1">
                        <p class="font-bold text-brand-dark">Kesimpulan Stabilitas:</p>
                        @if ($isRankingStable)
                            <div class="p-3 bg-emerald-50 text-emerald-800 rounded-xl border border-emerald-200 font-medium">
                                ✓ <strong>Model Stabil (Robust):</strong> Pergeseran bobot tidak mengubah pemenang peringkat 1.
                            </div>
                        @else
                            <div class="p-3 bg-amber-50 text-amber-800 rounded-xl border border-amber-200 font-medium">
                                ⚠ <strong>Peka terhadap Bobot (Sensitive):</strong> Terjadi pergeseran peringkat pada alternatif distributor.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Comparison Table -->
            <div class="card-shell lg:col-span-2">
                <div class="card-core p-6 space-y-5">
                    <div class="flex justify-between items-center">
                        <h2 class="text-base font-bold text-brand-dark tracking-tight">Perbandingan Peringkat (Baseline vs Skenario)</h2>
                        <span class="badge {{ $isRankingStable ? 'bg-brand-mint text-brand-emerald' : 'bg-amber-100 text-amber-800' }}">
                            {{ $isRankingStable ? 'Peringkat Konsisten' : 'Peringkat Bergeser' }}
                        </span>
                    </div>

                    <div class="overflow-x-auto -mx-6 sm:mx-0">
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
                                @forelse ($comparison as $row)
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
                                                <span class="badge bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                                                    ▲ Naik {{ $row['rank_diff'] }}
                                                </span>
                                            @elseif ($row['rank_diff'] < 0)
                                                <span class="badge bg-red-100 text-red-800 text-[10px] font-bold">
                                                    ▼ Turun {{ abs($row['rank_diff']) }}
                                                </span>
                                            @else
                                                <span class="badge bg-slate-100 text-slate-600 text-[10px]">
                                                    Tetap (Rank {{ $row['new_rank'] }})
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-brand-slate text-xs">
                                            Tidak ada data alternatif yang diuji.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Perbandingan dengan Metode SAW -->
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
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
