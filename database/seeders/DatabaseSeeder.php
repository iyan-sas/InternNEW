<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Sample test user (pwede mong tanggalin kung di kailangan)
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // ✅ Call your SuperAdmin seeder here
        $this->call([
            SuperAdminSeeder::class,
        ]);

        $this->call(CampusCollegeSeeder::class);
    }
}
