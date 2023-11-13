<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMonitorNwdTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('monitor_nwd', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->bigInteger('monitor_id');
            $table->bigInteger('school_id')->nullable();
            $table->bigInteger('station_id')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->tinyInteger('full_day');
            $table->text('description')->nullable();
            $table->string('color', 45)->nullable();
            $table->boolean('user_nwd_subtype_id')->default(1)->index('fk_user_nwd_subtype');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('monitor_id', 'monitor_nwd_ibfk_1')->references('id')->on('monitors');
            $table->foreign('school_id', 'monitor_nwd_ibfk_2')->references('id')->on('schools');
            $table->foreign('station_id', 'monitor_nwd_ibfk_3')->references('id')->on('stations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('monitor_nwd');
    }
}
