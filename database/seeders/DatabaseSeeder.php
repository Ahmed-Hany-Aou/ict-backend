<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate(
            ['email' => 'amany.hany@gmail.com'],
            [
                'name' => 'Amany Hany',
                'password' => bcrypt('amany@1975'),
                'is_premium' => true,
                'premium_expires_at' => now()->addYears(2),
                'role' => 'student',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => bcrypt('password'), 'is_premium' => true]
        );

        $this->call([
            Chapter1Seeder::class,
            Chapter2Seeder::class,
            Chapter3Seeder::class,
        ]);
    }
}
