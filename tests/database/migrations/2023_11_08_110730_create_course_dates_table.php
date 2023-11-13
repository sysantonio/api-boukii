<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseDatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_dates', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->bigInteger('course_id');
            $table->date('date');
            $table->time('hour_start');
            $table->time('hour_end');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('course_id', 'course_dates_ibfk_1')->references('id')->on('courses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('course_dates');
    }
}
