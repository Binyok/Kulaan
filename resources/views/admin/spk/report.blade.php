<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Hasil SPK - #SPK-{{ $history->id }}</title>
    <style>
        @media print {
            body { font-size: 11pt; color: #111; margin: 0; padding: 20px; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            line-height: 1.5;
            padding: 30px;
            max-width: 900px;
            margin: auto;
            background: #fff;
        }
        .header {
            border-bottom: 2px solid #2d6a4f;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .title {
            font-size: 20pt;
            font-weight: 800;
            color: #2d6a4f;
            margin: 0;
        }
        .subtitle {
            font-size: 10pt;
            color: #64748b;
            margin-top: 4px;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            background: #f8fafc;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 9.5pt;
        }
        .section-title {
            font-size: 12pt;
            font-weight: 700;
            color: #0f172a;
            border-left: 4px solid #2d6a4f;
            padding-left: 8px;
            margin: 20px 0 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
            font-size: 9pt;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 7px 10px;
            text-align: left;
        }
        th {
            background-color: #f1f5f9;
            font-weight: 700;
            color: #334155;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-mono { font-family: monospace; }
        .highlight {
            background-color: #d8f3dc;
            font-weight: bold;
        }
        .btn-print {
            background: #2d6a4f;
            color: #fff;
            padding: 8px 18px;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
        <a href="{{ route('admin.spk.index') }}" style="color: #2d6a4f; text-decoration: none; font-size: 13px; font-weight: bold;">&larr; Kembali ke Dashboard SPK</a>
        <button onclick="window.print()" class="btn-print">Cetak / Simpan sebagai PDF</button>
    </div>

    <div class="header">
        <h1 class="title">KULA'AN — UMKM SUPPLY HUB</h1>
        <div class="subtitle">Laporan Hasil Sistem Pendukung Keputusan Penentuan Distributor Terbaik (AHP + TOPSIS + SAW)</div>
    </div>

    <div class="meta-grid">
        <div><strong>Nomor Laporan:</strong> SPK-RPT-{{ str_pad($history->id, 5, '0', STR_PAD_LEFT) }}</div>
        <div><strong>Waktu Kalkulasi:</strong> {{ $history->created_at->format('d F Y, H:i') }} WITA</div>
        <div><strong>Kode Grup Pengadaan:</strong> {{ $history->matchingGroup?->group_code ?? '-' }}</div>
        <div><strong>Komoditas Produk:</strong> {{ $history->matchingGroup?->product?->name ?? 'Multi Komoditas' }}</div>
    </div>

    <div class="section-title">1. Pembobotan Kriteria (Analytical Hierarchy Process / AHP)</div>
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama Kriteria</th>
                <th>Sifat</th>
                <th class="text-right">Bobot Hasil AHP ($w_j$)</th>
                <th class="text-right">Persentase</th>
            </tr>
        </thead>
        <tbody>
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

    <div class="section-title">2. Matriks Keputusan Alternatif Distributor ($X$)</div>
    <table>
        <thead>
            <tr>
                <th>Alternatif</th>
                <th class="text-right">Harga ($C_1$)</th>
                <th class="text-center">Rating Kualitas ($C_2$)</th>
                <th class="text-center">Waktu Kirim ($C_3$)</th>
                <th class="text-right">Jarak Haversine ($C_4$)</th>
                <th class="text-right">MOQ ($C_5$)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($history->decision_matrix ?? [] as $alt)
                <tr>
                    <td><strong>{{ $alt['name'] }}</strong></td>
                    <td class="text-right font-mono">Rp {{ number_format($alt['criteria_values'][0], 0, ',', '.') }}</td>
                    <td class="text-center font-mono">{{ number_format($alt['criteria_values'][1], 2) }} ★</td>
                    <td class="text-center font-mono">{{ 5 - $alt['criteria_values'][2] }} Hari</td>
                    <td class="text-right font-mono">{{ number_format($alt['criteria_values'][3], 2) }} km</td>
                    <td class="text-right font-mono">{{ number_format($alt['criteria_values'][4], 0) }} Unit</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">3. Hasil Pemeringkatan TOPSIS (Rekomendasi Utama)</div>
    <table>
        <thead>
            <tr>
                <th class="text-center">Peringkat</th>
                <th>Nama Distributor</th>
                <th class="text-right">Jarak Ideal Positif ($D_i^+$)</th>
                <th class="text-right">Jarak Ideal Negatif ($D_i^-$)</th>
                <th class="text-right">Preferensi Relatif ($V_i$)</th>
                <th class="text-center">Status Keputusan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($history->rankings['topsis'] ?? [] as $top)
                <tr class="{{ $top['rank'] === 1 ? 'highlight' : '' }}">
                    <td class="text-center font-mono font-bold">#{{ $top['rank'] }}</td>
                    <td><strong>{{ $top['name'] }}</strong></td>
                    <td class="text-right font-mono">{{ number_format($top['d_plus'], 4) }}</td>
                    <td class="text-right font-mono">{{ number_format($top['d_minus'], 4) }}</td>
                    <td class="text-right font-mono font-bold">{{ number_format($top['score'], 4) }}</td>
                    <td class="text-center">
                        {{ $top['rank'] === 1 ? '★ TERPILIH (Rekomendasi)' : 'Alternatif' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">4. Validasi Silang (Simple Additive Weighting / SAW)</div>
    <table>
        <thead>
            <tr>
                <th class="text-center">Peringkat SAW</th>
                <th>Nama Distributor</th>
                <th class="text-right">Nilai Preferensi Terbobot ($S_i$)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($history->rankings['saw'] ?? [] as $saw)
                <tr>
                    <td class="text-center font-mono">#{{ $saw['rank'] }}</td>
                    <td>{{ $saw['name'] }}</td>
                    <td class="text-right font-mono font-bold">{{ number_format($saw['saw_score'], 4) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 40px; display: flex; justify-content: space-between; font-size: 9pt;">
        <div>
            Dicetak secara sistematis oleh:<br>
            <strong>Platform Kula'an Decision Support Engine</strong>
        </div>
        <div style="text-align: right;">
            Banjarmasin, {{ now()->format('d F Y') }}<br>
            Penanggung Jawab Pengadaan / Admin Sistem
            <div style="margin-top: 50px; font-weight: bold;">( ____________________________ )</div>
        </div>
    </div>
</body>
</html>
