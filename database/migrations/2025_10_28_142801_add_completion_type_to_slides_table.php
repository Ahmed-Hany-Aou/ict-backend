<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For MySQL, we need to use raw SQL to modify ENUM
        DB::statement("ALTER TABLE slides MODIFY COLUMN type ENUM('title', 'content', 'quiz', 'scenario', 'review', 'answers', 'completion') NOT NULL DEFAULT 'content'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE slides MODIFY COLUMN type ENUM('title', 'content', 'quiz', 'scenario', 'review', 'answers') NOT NULL DEFAULT 'content'");
    }
};
