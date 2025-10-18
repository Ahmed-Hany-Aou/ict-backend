<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Slides table
        Schema::create('slides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained()->onDelete('cascade');
            $table->integer('slide_number');
            $table->enum('type', ['title', 'content', 'quiz', 'scenario', 'review', 'answers'])->default('content');
            $table->json('content');
            $table->timestamps();
            
            $table->index(['chapter_id', 'slide_number']);
        });

        // Slide progress table
        Schema::create('slide_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('slide_id')->constrained()->onDelete('cascade');
            $table->foreignId('chapter_id')->constrained()->onDelete('cascade');
            $table->boolean('completed')->default(false);
            $table->timestamp('last_viewed_at')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'slide_id']);
            $table->index('user_id');
            $table->index('chapter_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slide_progress');
        Schema::dropIfExists('slides');
    }
};