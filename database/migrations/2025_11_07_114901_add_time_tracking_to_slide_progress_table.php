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
        Schema::table('slide_progress', function (Blueprint $table) {
            $table->timestamp('started_at')->nullable()->after('last_viewed_at');
            $table->integer('time_spent')->default(0)->comment('Time spent in seconds')->after('started_at');
            $table->integer('view_count')->default(0)->comment('Number of times viewed')->after('time_spent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slide_progress', function (Blueprint $table) {
            $table->dropColumn(['started_at', 'time_spent', 'view_count']);
        });
    }
};
