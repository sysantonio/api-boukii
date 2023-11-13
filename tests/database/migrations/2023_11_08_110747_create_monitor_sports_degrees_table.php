<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMonitorSportsDegreesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('monitor_sports_degrees', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->bigInteger('sport_id');
            $table->bigInteger('school_id')->nullable();
            $table->bigInteger('degree_id');
            $table->bigInteger('monitor_id');
            $table->bigInteger('salary_level')->nullable();
            $table->integer('allow_adults')->default(0);
            $table->boolean('is_default')->default(0);
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('sport_id', 'monitor_sports_degrees_ibfk_1')->references('id')->on('sports');
            $table->foreign('school_id', 'monitor_sports_degrees_ibfk_2')->references('id')->on('schools');
            $table->foreign('degree_id', 'monitor_sports_degrees_ibfk_3')->references('id')->on('degrees');
            $table->foreign('monitor_id', 'monitor_sports_degrees_ibfk_4')->references('id')->on('monitors');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('monitor_sports_degrees');
    }
}
