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
        // Add composite index for quiz_results to optimize frequent queries
        Schema::table('quiz_results', function (Blueprint $table) {
            // Index for getting user's quiz results
            $table->index(['user_id', 'quiz_id']);
            // Index for filtering passed quizzes
            $table->index(['user_id', 'passed']);
            // Index for quiz-specific queries
            $table->index(['quiz_id', 'passed']);
        });

        // Add composite index for slide_progress to optimize chapter progress queries
        Schema::table('slide_progress', function (Blueprint $table) {
            // Index for getting completed slides by chapter
            $table->index(['user_id', 'chapter_id', 'completed']);
        });

        // Add index for user_progress status queries
        Schema::table('user_progress', function (Blueprint $table) {
            // Index for getting completed chapters by user
            $table->index(['user_id', 'status']);
        });

        // Add index for quizzes to optimize category and active queries
        Schema::table('quizzes', function (Blueprint $table) {
            // Index for filtering by category and active status
            $table->index(['category', 'is_active']);
            // Index for chapter-specific quizzes
            $table->index(['chapter_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_results', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'quiz_id']);
            $table->dropIndex(['user_id', 'passed']);
            $table->dropIndex(['quiz_id', 'passed']);
        });

        Schema::table('slide_progress', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'chapter_id', 'completed']);
        });

        Schema::table('user_progress', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropIndex(['category', 'is_active']);
            $table->dropIndex(['chapter_id', 'is_active']);
        });
    }
};
