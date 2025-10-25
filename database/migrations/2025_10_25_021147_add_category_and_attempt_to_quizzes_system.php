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
        // Add category to quizzes table
        Schema::table('quizzes', function (Blueprint $table) {
            $table->enum('category', ['chapter', 'midterm', 'final', 'practice'])->default('chapter')->after('chapter_id');
        });

        // Add attempt tracking to quiz_results table
        Schema::table('quiz_results', function (Blueprint $table) {
            $table->integer('attempt_number')->default(1)->after('quiz_id');
            $table->integer('time_taken')->nullable()->after('passed')->comment('Time taken in seconds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn('category');
        });

        Schema::table('quiz_results', function (Blueprint $table) {
            $table->dropColumn(['attempt_number', 'time_taken']);
        });
    }
};
