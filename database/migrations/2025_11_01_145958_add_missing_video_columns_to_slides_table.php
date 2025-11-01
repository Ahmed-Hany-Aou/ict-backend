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
        Schema::table('slides', function (Blueprint $table) {
            // Check if columns don't exist and add them
            if (!Schema::hasColumn('slides', 'video_type')) {
                $table->enum('video_type', ['none', 'scheduled', 'recorded'])->default('none')->after('content');
            }
            if (!Schema::hasColumn('slides', 'meeting_datetime')) {
                $table->dateTime('meeting_datetime')->nullable()->after('video_type');
            }
            if (!Schema::hasColumn('slides', 'meeting_link')) {
                $table->string('meeting_link')->nullable()->after('meeting_datetime');
            }
            if (!Schema::hasColumn('slides', 'video_url')) {
                $table->string('video_url')->nullable()->after('meeting_link');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slides', function (Blueprint $table) {
            $table->dropColumn(['video_type', 'meeting_datetime', 'meeting_link', 'video_url']);
        });
    }
};
