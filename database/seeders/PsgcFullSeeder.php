<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PsgcFullSeeder extends Seeder
{
    public function run(): void
    {
        $dir = storage_path('app/psgc');

        $paths = [
            'regions'   => $dir . '/regions.csv',
            'provinces' => $dir . '/provinces.csv',
            'cities'    => $dir . '/cities.csv',
            'barangays' => $dir . '/barangays.csv',
        ];

        // Check all CSV files exist
        foreach ($paths as $label => $p) {
            if (!is_file($p)) {
                $this->command->error("Missing CSV: {$p}");
                return;
            }
        }

        // Optional: speed-up
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Uncomment if you want a fresh re-import
        // DB::table('psgc_barangays')->truncate();
        // DB::table('psgc_cities')->truncate();
        // DB::table('psgc_provinces')->truncate();
        // DB::table('psgc_regions')->truncate();

        $this->importCsv($paths['regions'],   'psgc_regions',   ['code','name']);
        $this->importCsv($paths['provinces'], 'psgc_provinces', ['code','name','region_code']);
        $this->importCsv($paths['cities'],    'psgc_cities',    ['code','name','province_code','region_code']);
        $this->importCsv($paths['barangays'], 'psgc_barangays', ['code','name','city_code']);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function importCsv(string $path, string $table, array $columns, int $chunk = 1000): void
    {
        $fh = fopen($path, 'r');
        if (!$fh) {
            $this->command->error("Cannot open: {$path}");
            return;
        }

        // Skip header
        fgetcsv($fh);

        $rows  = [];
        $count = 0;

        while (($data = fgetcsv($fh)) !== false) {
            $row = [];
            foreach ($columns as $i => $col) {
                $row[$col] = isset($data[$i]) ? trim((string)$data[$i]) : null;
            }
            $rows[] = $row;

            if (count($rows) >= $chunk) {
                DB::table($table)->upsert($rows, ['code'], $columns);
                $count += count($rows);
                $rows = [];
            }
        }

        if ($rows) {
            DB::table($table)->upsert($rows, ['code'], $columns);
            $count += count($rows);
        }

        fclose($fh);
        $this->command->info("Imported {$count} → {$table} (" . basename($path) . ")");
    }
}
