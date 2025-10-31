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
        Schema::table('quiz_results', function (Blueprint $table) {
            
            if (!Schema::hasColumn('quiz_results', 'quiz_id')) {
                $table->foreignId('quiz_id')->after('user_id')->constrained()->onDelete('cascade');
            }

            if (!Schema::hasColumn('quiz_results', 'answers')) {
                $table->json('answers')->after('quiz_id');
            }

            if (!Schema::hasColumn('quiz_results', 'score')) {
                $table->integer('score')->after('answers');
            }

            if (!Schema::hasColumn('quiz_results', 'total_questions')) {
                $table->integer('total_questions')->after('score');
            }

            if (!Schema::hasColumn('quiz_results', 'percentage')) {
                $table->float('percentage')->after('total_questions');
            }

            if (!Schema::hasColumn('quiz_results', 'passed')) {
                $table->boolean('passed')->after('percentage');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_results', function (Blueprint $table) {
            // This is also wrapped in checks to be safe
            if (Schema::hasColumn('quiz_results', 'quiz_id')) {
                $table->dropForeign(['quiz_id']);
                $table->dropColumn(['quiz_id', 'answers', 'score', 'total_questions', 'percentage', 'passed']);
            }
        });
    }
};