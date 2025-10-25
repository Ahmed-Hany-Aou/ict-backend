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
            $table->foreignId('quiz_id')->after('user_id')->constrained()->onDelete('cascade');
            $table->json('answers')->after('quiz_id');
            $table->integer('score')->after('answers');
            $table->integer('total_questions')->after('score');
            $table->float('percentage')->after('total_questions');
            $table->boolean('passed')->after('percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_results', function (Blueprint $table) {
            $table->dropForeign(['quiz_id']);
            $table->dropColumn(['quiz_id', 'answers', 'score', 'total_questions', 'percentage', 'passed']);
        });
    }
};
