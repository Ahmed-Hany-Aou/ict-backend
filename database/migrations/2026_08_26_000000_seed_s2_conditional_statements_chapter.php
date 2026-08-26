<?php

use Illuminate\Database\Migrations\Migration;
use Database\Seeders\Chapter3Seeder;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $seeder = new Chapter3Seeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \App\Models\Chapter::where('chapter_number', 3)->delete();
    }
};
