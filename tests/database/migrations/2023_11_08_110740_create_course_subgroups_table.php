<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseSubgroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_subgroups', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->bigInteger('course_id');
            $table->bigInteger('course_date_id');
            $table->bigInteger('degree_id');
            $table->bigInteger('course_group_id');
            $table->bigInteger('monitor_id')->nullable();
            $table->integer('max_participants')->nullable();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('course_id', 'course_subgroups_ibfk_1')->references('id')->on('courses');
            $table->foreign('course_date_id', 'course_subgroups_ibfk_2')->references('id')->on('course_dates');
            $table->foreign('degree_id', 'course_subgroups_ibfk_3')->references('id')->on('degrees');
            $table->foreign('course_group_id', 'course_subgroups_ibfk_4')->references('id')->on('course_groups');
            $table->foreign('monitor_id', 'course_subgroups_ibfk_5')->references('id')->on('monitors');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('course_subgroups');
    }
}
