<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseExtrasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_extras', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->bigInteger('course_id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->decimal('price', 8, 2);
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('course_id', 'course_extras_ibfk_1')->references('id')->on('courses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('course_extras');
    }
}
