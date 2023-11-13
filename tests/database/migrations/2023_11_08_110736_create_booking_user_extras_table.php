<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingUserExtrasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booking_user_extras', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->bigInteger('boouking_user_id');
            $table->bigInteger('course_extra_id');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('boouking_user_id', 'booking_user_extras_ibfk_1')->references('id')->on('booking_users');
            $table->foreign('course_extra_id', 'booking_user_extras_ibfk_2')->references('id')->on('course_extras');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('booking_user_extras');
    }
}
