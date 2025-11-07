<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PsgcGithubSeeder extends Seeder
{
    /**
     * Simple CSV reader that returns an array of associative rows using the first line as headers.
     */
    protected function readCsv(string $path): array
    {
        if (!is_file($path)) {
            $this->command->warn("Missing file: {$path}");
            return [];
        }

        $rows = [];
        if (($h = fopen($path, 'r')) !== false) {
            $headers = fgetcsv($h);               // read header row
            if (!$headers) { fclose($h); return []; }

            // normalize headers (strip BOM, trim)
            $headers = array_map(function ($x) {
                $x = preg_replace('/^\xEF\xBB\xBF/', '', $x ?? ''); // strip UTF8 BOM
                return trim((string) $x);
            }, $headers);

            while (($data = fgetcsv($h)) !== false) {
                if (count($data) !== count($headers)) {
                    // sometimes last empty column — pad it
                    $data = array_pad($data, count($headers), null);
                }
                $row = [];
                foreach ($headers as $i => $k) {
                    $row[$k] = isset($data[$i]) ? trim((string) $data[$i]) : null;
                }
                $rows[] = $row;
            }
            fclose($h);
        }
        return $rows;
    }

    public function run(): void
    {
        // Paths to the 4 CSVs you already downloaded
        $base = storage_path('app/psgc');
        $regionCsv    = "{$base}/refregion.csv";    // headers: id, psgcCode, regDesc, regCode
        $provinceCsv  = "{$base}/refprovince.csv";  // headers: id, psgcCode, provDesc, regDesc, provCode, regCode
        $citymunCsv   = "{$base}/refcitymun.csv";   // headers: id, psgcCode, citymunDesc, regDesc, provCode, citymunCode, regCode
        $barangayCsv  = "{$base}/refbrgy.csv";      // headers: id, brgyCode, brgyDesc, regCode, provCode, citymunCode, ...

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('psgc_barangays')->truncate();
        DB::table('psgc_cities')->truncate();
        DB::table('psgc_provinces')->truncate();
        DB::table('psgc_regions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // -------- Regions --------
        $regionsRaw = $this->readCsv($regionCsv);
        $regions = [];
        foreach ($regionsRaw as $r) {
            // GitHub columns we care about:
            // - regCode (e.g. "01", "13")
            // - regDesc (human-readable region name)
            $code = $r['regCode'] ?? null;
            $name = $r['regDesc'] ?? null;
            if (!$code || !$name) continue;

            $regions[] = [
                'code' => (string) $code,
                'name' => $name,
            ];
        }
        if ($regions) {
            DB::table('psgc_regions')->insert($regions);
            $this->command->info('Inserted regions: '.count($regions));
        }

        // -------- Provinces --------
        $provincesRaw = $this->readCsv($provinceCsv);
        $provinces = [];
        foreach ($provincesRaw as $p) {
            // Columns:
            // - provCode, provDesc, regCode
            $code = $p['provCode'] ?? null;
            $name = $p['provDesc'] ?? null;
            $reg  = $p['regCode'] ?? null;
            if (!$code || !$name || !$reg) continue;

            $provinces[] = [
                'code'        => (string) $code,
                'name'        => $name,
                'region_code' => (string) $reg,
            ];
        }
        if ($provinces) {
            // use upsert to be idempotent if your migration has a unique index on "code"
            DB::table('psgc_provinces')->upsert($provinces, ['code'], ['name','region_code']);
            $this->command->info('Upserted provinces: '.count($provinces));
        }

        // -------- Cities / Municipalities --------
        $citiesRaw = $this->readCsv($citymunCsv);
        $cities = [];
        foreach ($citiesRaw as $c) {
            // Columns:
            // - citymunCode, citymunDesc, provCode, regCode
            $code = $c['citymunCode'] ?? null;
            $name = $c['citymunDesc'] ?? null;
            $prov = $c['provCode'] ?? null;     // can be empty for NCR cities
            $reg  = $c['regCode'] ?? null;
            if (!$code || !$name || !$reg) continue; // reg is required in your schema

            $cities[] = [
                'code'          => (string) $code,
                'name'          => $name,
                'province_code' => $prov ? (string) $prov : null,
                'region_code'   => (string) $reg,
            ];
        }
        if ($cities) {
            DB::table('psgc_cities')->upsert($cities, ['code'], ['name','province_code','region_code']);
            $this->command->info('Upserted cities: '.count($cities));
        }

        // -------- Barangays --------
        $barangaysRaw = $this->readCsv($barangayCsv);
        $barangays = [];
        foreach ($barangaysRaw as $b) {
            // Columns:
            // - brgyCode, brgyDesc, citymunCode
            $code = $b['brgyCode'] ?? null;
            $name = $b['brgyDesc'] ?? null;
            $city = $b['citymunCode'] ?? null;
            if (!$code || !$name || !$city) continue;

            $barangays[] = [
                'code'     => (string) $code,
                'name'     => $name,
                'city_code'=> (string) $city,
            ];
        }

        // Chunk barangays so it doesn't blow memory
        $chunk = 2000;
        $total = 0;
        foreach (array_chunk($barangays, $chunk) as $part) {
            DB::table('psgc_barangays')->upsert($part, ['code'], ['name','city_code']);
            $total += count($part);
        }
        $this->command->info('Upserted barangays: '.$total);
    }
}
