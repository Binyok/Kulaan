<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Membangun dataset wilayah lokal (database/data/wilayah/*.json) dari data
 * Kode & Data Wilayah Administrasi Pemerintahan (Kepmendagri) yang
 * dipublikasikan repositori cahyadsn/wilayah. File JSON hasil generate
 * TIDAK di-commit ke git — jalankan perintah ini sekali setelah clone:
 *
 *   php artisan wilayah:build
 */
class BuildWilayahData extends Command
{
    protected $signature = 'wilayah:build {--source= : URL atau path file wilayah.sql lain}';

    protected $description = 'Bangun dataset wilayah lokal (JSON) dari data Kemendagri (cahyadsn/wilayah)';

    private const SOURCE_URL = 'https://raw.githubusercontent.com/cahyadsn/wilayah/master/db/wilayah.sql';

    public function handle(): int
    {
        $source = $this->option('source') ?: self::SOURCE_URL;

        $sql = @file_get_contents($source);
        if ($sql === false || trim($sql) === '') {
            $this->error("Gagal membaca sumber data wilayah: {$source}");

            return self::FAILURE;
        }

        $levels = [1 => [], 2 => [], 3 => [], 4 => []];
        foreach (preg_split('/\r\n|\r|\n/', $sql) as $line) {
            if (! preg_match("#^\\('([0-9.]+)','(.*)'\\)[,;]?$#", trim($line), $m)) {
                continue;
            }
            [, $kode, $nama] = $m;
            $nama = str_replace("''", "'", $nama); // SQL escape untuk apostrof (cth. O''o''u -> O'o'u)
            $parts = explode('.', $kode);
            $row = ['id' => $kode, 'name' => $nama];
            if (count($parts) === 1) {
                $levels[1][] = $row;
            } else {
                $parent = implode('.', array_slice($parts, 0, -1));
                $levels[count($parts)][$parent][] = $row;
            }
        }

        if ($levels[1] === []) {
            $this->error('Tidak ada baris wilayah yang terbaca dari sumber — format file berubah?');

            return self::FAILURE;
        }

        $base = database_path('data/wilayah');
        File::deleteDirectory($base);
        File::ensureDirectoryExists($base);

        $this->writeJson("{$base}/provinces.json", $levels[1]);
        $counts = ['provinsi' => count($levels[1])];

        foreach ([2 => 'regencies', 3 => 'districts', 4 => 'villages'] as $depth => $dir) {
            File::ensureDirectoryExists("{$base}/{$dir}");
            $total = 0;
            foreach ($levels[$depth] as $parent => $rows) {
                $this->writeJson("{$base}/{$dir}/{$parent}.json", $rows);
                $total += count($rows);
            }
            $counts[$dir] = $total;
        }

        $this->info('Dataset wilayah berhasil dibangun di database/data/wilayah:');
        foreach ($counts as $label => $count) {
            $this->line("  - {$label}: {$count}");
        }

        return self::SUCCESS;
    }

    private function writeJson(string $path, array $rows): void
    {
        usort($rows, fn ($a, $b) => strcasecmp($a['name'], $b['name']));
        file_put_contents($path, json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
