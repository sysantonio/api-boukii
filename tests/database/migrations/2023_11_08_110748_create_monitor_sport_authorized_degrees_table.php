<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMonitorSportAuthorizedDegreesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('monitor_sport_authorized_degrees', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->bigInteger('monitor_sport_id');
            $table->bigInteger('degree_id');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('monitor_sport_id', 'monitor_sport_authorized_degrees_ibfk_1')->references('id')->on('monitor_sports_degrees');
            $table->foreign('degree_id', 'monitor_sport_authorized_degrees_ibfk_2')->references('id')->on('degrees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('monitor_sport_authorized_degrees');
    }
}
