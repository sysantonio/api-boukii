<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStationServiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('station_service', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->bigInteger('station_id');
            $table->bigInteger('service_type_id');
            $table->string('name', 100);
            $table->string('url', 100)->default('');
            $table->string('telephone', 100)->default('');
            $table->string('email', 100)->default('');
            $table->string('image')->default('');
            $table->boolean('active')->default(0);
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('station_id', 'station_service_ibfk_1')->references('id')->on('stations');
            $table->foreign('service_type_id', 'station_service_ibfk_2')->references('id')->on('service_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('station_service');
    }
}
