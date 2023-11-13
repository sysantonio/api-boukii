<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsUtilizersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients_utilizers', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->bigInteger('main_id');
            $table->bigInteger('client_id');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('main_id', 'clients_utilizers_ibfk_1')->references('id')->on('clients');
            $table->foreign('client_id', 'clients_utilizers_ibfk_2')->references('id')->on('clients');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clients_utilizers');
    }
}
