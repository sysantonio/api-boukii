<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMonitorsSchoolsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('monitors_schools', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->bigInteger('monitor_id');
            $table->bigInteger('school_id');
            $table->bigInteger('station_id')->nullable();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamp('status_updated_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            
            $table->foreign('monitor_id', 'monitors_schools_ibfk_1')->references('id')->on('monitors');
            $table->foreign('school_id', 'monitors_schools_ibfk_2')->references('id')->on('schools');
            $table->foreign('station_id', 'monitors_schools_ibfk_3')->references('id')->on('stations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('monitors_schools');
    }
}
