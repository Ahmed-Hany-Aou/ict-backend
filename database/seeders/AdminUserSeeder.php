<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user if it doesn't exist
        User::firstOrCreate(
            ['email' => 'admin@ict.com'],
            [
                'name' => 'ICT Admin',
                'password' => Hash::make('password123'),
            ]
        );

        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: admin@ict.com');
        $this->command->info('Password: password123');
    }
}
