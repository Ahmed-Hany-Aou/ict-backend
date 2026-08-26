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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['system', 'personal', 'broadcast'])->default('system');
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // For future flexibility
            $table->enum('sent_to', ['all', 'selected'])->default('all');
            $table->json('sent_to_users')->nullable(); // Array of user_ids
            $table->foreignId('created_by')->constrained('users'); // Admin who created
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'created_at']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
