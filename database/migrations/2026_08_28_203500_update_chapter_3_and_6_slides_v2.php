<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Database\Seeders\Chapter3Seeder;
use Database\Seeders\Chapter6Seeder;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            $seeder3 = new Chapter3Seeder();
            $seeder3->run();
        } catch (\Throwable $e) {
            // Log or ignore
        }

        try {
            $seeder6 = new Chapter6Seeder();
            $seeder6->run();
        } catch (\Throwable $e) {
            // Log or ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
