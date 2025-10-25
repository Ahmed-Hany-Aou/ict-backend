<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            // Make unnecessary fields nullable
            $table->integer('chapter_number')->nullable()->change();
            $table->text('content')->nullable()->change();
            $table->boolean('is_published')->nullable()->default(null)->change();
            $table->boolean('is_premium')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->integer('chapter_number')->nullable(false)->change();
            $table->text('content')->nullable()->change();
            $table->boolean('is_published')->default(false)->nullable(false)->change();
            $table->boolean('is_premium')->default(false)->nullable(false)->change();
        });
    }
};
