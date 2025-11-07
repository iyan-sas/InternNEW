<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SchoolYearSeeder extends Seeder {
    public function run(): void {
        DB::table('school_years')->insert([
            ['label' => '2024-2025', 'is_active' => true],
            ['label' => '2025-2026', 'is_active' => false],
        ]);
    }
}
