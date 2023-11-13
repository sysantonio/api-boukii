<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVouchersLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vouchers_log', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->bigInteger('voucher_id');
            $table->bigInteger('booking_id');
            $table->decimal('amount', 8, 2);
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('voucher_id', 'vouchers_log_ibfk_1')->references('id')->on('vouchers');
            $table->foreign('booking_id', 'vouchers_log_ibfk_2')->references('id')->on('bookings');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vouchers_log');
    }
}
