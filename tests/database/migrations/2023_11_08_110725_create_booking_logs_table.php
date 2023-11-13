<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booking_logs', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->bigInteger('booking_id');
            $table->string('action', 100)->default('updated');
            $table->text('description')->nullable();
            $table->bigInteger('user_id');
            $table->json('before_change')->nullable();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('booking_id', 'booking_logs_ibfk_1')->references('id')->on('bookings');
            $table->foreign('user_id', 'booking_logs_ibfk_2')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('booking_logs');
    }
}
