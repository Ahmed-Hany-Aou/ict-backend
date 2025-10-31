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
        if (!Schema::hasTable('quiz_results')) {
            Schema::create('quiz_results', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('quiz_id')->constrained()->onDelete('cascade');
                $table->json('answers'); // User answers
            $table->integer('score');
            $table->integer('total_questions');
            $table->float('percentage');
            $table->boolean('passed');
            $table->timestamps();
        
        });
    }
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_results');
    }
};
