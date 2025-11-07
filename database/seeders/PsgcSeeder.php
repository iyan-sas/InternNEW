<?php
// database/seeders/PsgcSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PsgcSeeder extends Seeder
{
    public function run(): void
    {
        DB::disableQueryLog();
        $this->seedCsv('psgc_regions', 'regions.csv', ['code','name']);
        $this->seedCsv('psgc_provinces', 'provinces.csv', ['code','name','region_code']);
        $this->seedCsv('psgc_cities', 'cities.csv', ['code','name','province_code','region_code']);
        $this->seedCsv('psgc_barangays', 'barangays.csv', ['code','name','city_code']);
    }

    protected function seedCsv(string $table, string $file, array $columns): void
    {
        $path = "psgc/{$file}";
        if (!Storage::exists($path)) {
            $this->command->warn("Skip {$table}: storage/app/{$path} not found");
            return;
        }

        $stream = Storage::readStream($path);
        if (!$stream) return;

        $header = null;
        $batch  = [];
        $batchSize = 1000;

        while (($row = fgetcsv($stream)) !== false) {
            if ($header === null) {
                $header = array_map('trim', $row);
                continue;
            }
            $assoc = array_combine($header, $row);

            // normalize to our expected column list
            $data = [];
            foreach ($columns as $col) {
                $val = $assoc[$col] ?? null;
                $val = $val === '' ? null : $val;
                $data[$col] = $val;
            }

            $batch[] = $data;

            if (count($batch) >= $batchSize) {
                DB::table($table)->upsert($batch, ['code'], $columns);
                $batch = [];
            }
        }
        if ($batch) {
            DB::table($table)->upsert($batch, ['code'], $columns);
        }

        fclose($stream);
        $this->command->info("Seeded {$table}");
    }
}
