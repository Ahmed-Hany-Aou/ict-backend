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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('activity_type', 50)->comment('slide_viewed, slide_completed, quiz_started, quiz_completed, chapter_started, chapter_completed');
            $table->foreignId('slide_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('quiz_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('chapter_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('duration')->nullable()->comment('Time spent in seconds');
            $table->json('metadata')->nullable()->comment('Additional tracking data like device, location, etc');
            $table->timestamp('activity_timestamp')->useCurrent()->comment('When the activity occurred');
            $table->timestamps();

            // Performance indexes for fast querying
            $table->index(['user_id', 'activity_type', 'activity_timestamp'], 'activity_logs_user_type_time_idx');
            $table->index(['activity_type', 'activity_timestamp'], 'activity_logs_type_time_idx');
            $table->index(['slide_id', 'activity_timestamp'], 'activity_logs_slide_time_idx');
            $table->index(['quiz_id', 'activity_timestamp'], 'activity_logs_quiz_time_idx');
            $table->index(['chapter_id', 'activity_timestamp'], 'activity_logs_chapter_time_idx');
            $table->index(['user_id', 'activity_timestamp'], 'activity_logs_user_time_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
