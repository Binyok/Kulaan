<x-layouts.app>
    <x-slot name="title">Kriteria SPK & AHP</x-slot>

    <div class="space-y-8 animate-fade-up">
        <div>
            <span class="eyebrow">Decision Support Engine</span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-brand-dark tracking-tight mt-1.5">Sistem Pendukung Keputusan (AHP + TOPSIS + SAW)</h1>
            <p class="text-[0.8125rem] text-brand-slate mt-1">Kelola kriteria dan kalkulasi bobot AHP (Analytical Hierarchy Process) untuk penentuan distributor terbaik.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Active Weights Table (Double-Bezel) -->
            <div class="card-shell">
                <div class="card-core p-6 sm:p-7 space-y-5">
                    <div class="flex justify-between items-center">
                        <h2 class="text-base font-bold text-brand-dark tracking-tight">Bobot Kriteria SPK Aktif</h2>
                        <span class="badge bg-brand-mint text-brand-emerald">Konsisten (CR &le; 0.1)</span>
                    </div>

                    <div class="overflow-x-auto -mx-6 sm:mx-0">
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
                                            <span class="badge {{ $c->type === 'cost' ? 'bg-brand-ochre-soft text-brand-accent' : 'bg-brand-mint text-brand-emerald' }}">
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

                    @if ($latestWeight)
                        <div class="p-4 bg-brand-mint/60 rounded-2xl text-[0.75rem] text-brand-primary border border-brand-primary/15 space-y-1">
                            <p class="font-bold">Status Konsistensi AHP Terakhir:</p>
                            <p class="text-brand-slate font-mono text-[0.6875rem]">
                                &lambda;<sub>max</sub> = {{ $latestWeight->lambda_max }} &bull; CI = {{ $latestWeight->ci }} &bull; CR = <strong class="text-brand-dark">{{ $latestWeight->cr }}</strong> ({{ $latestWeight->is_consistent ? 'KONSISTEN' : 'TIDAK KONSISTEN' }})
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Pairwise Matrix Form (Double-Bezel) -->
            <div class="card-shell">
                <div class="card-core p-6 sm:p-7 space-y-5">
                    <div>
                        <h2 class="text-base font-bold text-brand-dark tracking-tight">Perbandingan Berpasangan (AHP Pairwise)</h2>
                        <p class="text-[0.75rem] text-brand-slate mt-0.5">Tentukan nilai kepentingan relatif antarkriteria (Skala Saaty 1–9).</p>
                    </div>

                    <form method="POST" action="{{ route('admin.spk.calculate') }}" class="space-y-4">
                        @csrf
                        <div class="space-y-3 max-h-72 overflow-y-auto pr-2">
                            @php $n = count($criteria); @endphp
                            @for ($i = 0; $i < $n; $i++)
                                @for ($j = $i + 1; $j < $n; $j++)
                                    <div class="flex items-center justify-between text-xs border-b border-black/[0.04] pb-2.5">
                                        <span class="font-semibold text-brand-dark w-1/3">{{ $criteria[$i]->name }}</span>
                                        <select name="matrix_{{ $i }}_{{ $j }}" class="input-field py-1 text-xs bg-white w-36">
                                            <option value="1">1 - Sama Penting</option>
                                            <option value="2">2 - Nilai Antara 1 & 3</option>
                                            <option value="3" selected>3 - Sedikit Lebih Penting</option>
                                            <option value="5">5 - Lebih Penting</option>
                                            <option value="7">7 - Sangat Penting</option>
                                            <option value="9">9 - Mutlak Penting</option>
                                        </select>
                                        <span class="font-medium text-brand-slate w-1/3 text-right">{{ $criteria[$j]->name }}</span>
                                    </div>
                                @endfor
                            @endfor
                        </div>

                        <button type="submit" class="btn-primary w-full justify-center py-3 text-[0.8125rem] font-bold">
                            Hitung Bobot AHP & Uji Konsistensi (CR)
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
