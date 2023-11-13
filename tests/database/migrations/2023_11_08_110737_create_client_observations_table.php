<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientObservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_observations', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('general', 5000)->default('');
            $table->string('notes', 5000)->default('');
            $table->string('historical', 5000)->default('');
            $table->bigInteger('client_id');
            $table->bigInteger('school_id');
            $table->timestamps();
            
            $table->foreign('client_id', 'client_observations_ibfk_1')->references('id')->on('clients');
            $table->foreign('school_id', 'client_observations_ibfk_2')->references('id')->on('schools');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_observations');
    }
}
