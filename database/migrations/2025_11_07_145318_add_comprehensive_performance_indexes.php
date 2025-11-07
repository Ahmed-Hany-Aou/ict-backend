<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * These indexes dramatically improve query performance for:
     * - User lookups and authentication
     * - Chapter/slide access patterns
     * - Quiz result queries
     * - Progress tracking
     */
    public function up(): void
    {
        // Users table - optimize authentication and queries
        Schema::table('users', function (Blueprint $table) {
            $table->index(['email', 'is_active'], 'users_email_active_idx');
            $table->index(['role', 'is_premium'], 'users_role_premium_idx');
            $table->index(['created_at'], 'users_created_idx');
            $table->index(['is_premium', 'premium_expires_at'], 'users_premium_status_idx');
        });

        // Chapters table - optimize listing and filtering
        Schema::table('chapters', function (Blueprint $table) {
            $table->index(['is_published', 'chapter_number'], 'chapters_published_order_idx');
            $table->index(['is_premium', 'is_published'], 'chapters_premium_published_idx');
        });

        // Slides table - optimize chapter slide queries
        Schema::table('slides', function (Blueprint $table) {
            $table->index(['chapter_id', 'slide_number'], 'slides_chapter_order_idx');
            $table->index(['type'], 'slides_type_idx');
        });

        // Quizzes table - optimize quiz listing and access
        Schema::table('quizzes', function (Blueprint $table) {
            $table->index(['category', 'is_active'], 'quizzes_category_active_idx');
            $table->index(['is_premium', 'is_active'], 'quizzes_premium_active_idx');
        });

        // User Progress table - optimize progress queries
        Schema::table('user_progress', function (Blueprint $table) {
            $table->index(['chapter_id', 'status'], 'user_progress_chapter_status_idx');
            $table->index(['completed_at'], 'user_progress_completed_idx');
            $table->index(['started_at'], 'user_progress_started_idx');
        });

        // Slide Progress table - optimize engagement tracking
        Schema::table('slide_progress', function (Blueprint $table) {
            $table->index(['completed', 'last_viewed_at'], 'slide_progress_completed_viewed_idx');
            $table->index(['time_spent'], 'slide_progress_time_idx');
            $table->index(['view_count'], 'slide_progress_views_idx');
        });

        // Quiz Results table - optimize performance queries
        Schema::table('quiz_results', function (Blueprint $table) {
            $table->index(['created_at'], 'quiz_results_created_idx');
            $table->index(['percentage', 'passed'], 'quiz_results_score_idx');
            $table->index(['time_taken'], 'quiz_results_time_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_email_active_idx');
            $table->dropIndex('users_role_premium_idx');
            $table->dropIndex('users_created_idx');
            $table->dropIndex('users_premium_status_idx');
        });

        Schema::table('chapters', function (Blueprint $table) {
            $table->dropIndex('chapters_published_order_idx');
            $table->dropIndex('chapters_premium_published_idx');
        });

        Schema::table('slides', function (Blueprint $table) {
            $table->dropIndex('slides_chapter_order_idx');
            $table->dropIndex('slides_type_idx');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropIndex('quizzes_category_active_idx');
            $table->dropIndex('quizzes_premium_active_idx');
        });

        Schema::table('user_progress', function (Blueprint $table) {
            $table->dropIndex('user_progress_chapter_status_idx');
            $table->dropIndex('user_progress_completed_idx');
            $table->dropIndex('user_progress_started_idx');
        });

        Schema::table('slide_progress', function (Blueprint $table) {
            $table->dropIndex('slide_progress_completed_viewed_idx');
            $table->dropIndex('slide_progress_time_idx');
            $table->dropIndex('slide_progress_views_idx');
        });

        Schema::table('quiz_results', function (Blueprint $table) {
            $table->dropIndex('quiz_results_created_idx');
            $table->dropIndex('quiz_results_score_idx');
            $table->dropIndex('quiz_results_time_idx');
        });
    }
};
