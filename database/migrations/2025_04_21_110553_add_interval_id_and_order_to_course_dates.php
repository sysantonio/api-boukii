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
        Schema::table('course_dates', function (Blueprint $table) {
            $table->unsignedBigInteger('interval_id')->nullable()->after('hour_end');
            $table->integer('order')->nullable()->after('interval_id');
        });
    }

    public function down()
    {
        Schema::table('course_dates', function (Blueprint $table) {
            $table->dropColumn(['interval_id', 'order']);
        });
    }
};
