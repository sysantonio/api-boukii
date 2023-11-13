<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_groups', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->bigInteger('course_id');
            $table->bigInteger('course_date_id');
            $table->bigInteger('degree_id');
            $table->integer('age_min')->default(1);
            $table->integer('age_max')->default(99);
            $table->integer('recommended_age')->default(1);
            $table->integer('teachers_min');
            $table->integer('teachers_max');
            $table->text('observations')->nullable();
            $table->bigInteger('teacher_min_degree')->nullable();
            $table->boolean('auto')->default(1);
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('course_id', 'course_groups_ibfk_1')->references('id')->on('courses');
            $table->foreign('course_date_id', 'course_groups_ibfk_2')->references('id')->on('course_dates');
            $table->foreign('degree_id', 'course_groups_ibfk_3')->references('id')->on('degrees');
            $table->foreign('teacher_min_degree', 'course_groups_ibfk_4')->references('id')->on('degrees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('course_groups');
    }
}
