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
            $table->enum('video_type', ['none', 'scheduled', 'recorded'])->default('none')->after('content');
            $table->dateTime('meeting_datetime')->nullable()->after('video_type');
            $table->string('meeting_link')->nullable()->after('meeting_datetime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slides', function (Blueprint $table) {
            $table->dropColumn(['video_type', 'meeting_datetime', 'meeting_link']);
        });
    }
};
