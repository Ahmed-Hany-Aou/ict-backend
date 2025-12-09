<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 public function up()
{
    Schema::table('payments', function (Blueprint $table) {
        $table->string('ai_recommendation', 20)
              ->nullable()
              ->after('ai_analysis_result');
    });
}

public function down()
{
    Schema::table('payments', function (Blueprint $table) {
        $table->dropColumn('ai_recommendation');
    });
}
};
