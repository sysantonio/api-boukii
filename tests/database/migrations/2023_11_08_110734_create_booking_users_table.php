<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booking_users', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->bigInteger('booking_id');
            $table->bigInteger('client_id');
            $table->decimal('price', 8, 2);
            $table->string('currency', 3)->default('CHF');
            $table->bigInteger('course_subgroup_id')->nullable()->index('fk_bu2_subgroup_idx');
            $table->bigInteger('course_id')->nullable();
            $table->bigInteger('course_date_id');
            $table->bigInteger('degree_id')->nullable();
            $table->bigInteger('course_group_id')->nullable();
            $table->bigInteger('monitor_id')->nullable();
            $table->date('date')->nullable();
            $table->time('hour_start')->nullable();
            $table->time('hour_end')->nullable();
            $table->boolean('attended')->default(0);
            $table->string('color', 45)->nullable();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('booking_id', 'booking_users_ibfk_1')->references('id')->on('bookings');
            $table->foreign('client_id', 'booking_users_ibfk_2')->references('id')->on('clients');
            $table->foreign('course_id', 'booking_users_ibfk_3')->references('id')->on('courses');
            $table->foreign('course_date_id', 'booking_users_ibfk_4')->references('id')->on('course_dates');
            $table->foreign('degree_id', 'booking_users_ibfk_5')->references('id')->on('degrees');
            $table->foreign('course_group_id', 'booking_users_ibfk_6')->references('id')->on('course_groups');
            $table->foreign('monitor_id', 'booking_users_ibfk_7')->references('id')->on('monitors');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('booking_users');
    }
}
