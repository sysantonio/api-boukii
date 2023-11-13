<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStationsSchoolsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stations_schools', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->bigInteger('station_id');
            $table->bigInteger('school_id');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('station_id', 'stations_schools_ibfk_1')->references('id')->on('stations');
            $table->foreign('school_id', 'stations_schools_ibfk_2')->references('id')->on('schools');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stations_schools');
    }
}
