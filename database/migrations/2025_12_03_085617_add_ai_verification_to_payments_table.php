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
        Schema::table('payments', function (Blueprint $table) {
            $table->boolean('is_ai_verified')->default(false);
            $table->integer('ai_confidence_score')->nullable(); // 0-100
            $table->json('ai_analysis_result')->nullable(); // Full JSON from AI
            $table->string('transaction_id_extracted')->nullable(); // Extracted from OCR
            $table->timestamp('ai_reviewed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'is_ai_verified',
                'ai_confidence_score',
                'ai_analysis_result',
                'transaction_id_extracted',
                'ai_reviewed_at'
            ]);
        });
    }
};
