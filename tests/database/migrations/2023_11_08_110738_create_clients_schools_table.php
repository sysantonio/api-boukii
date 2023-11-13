<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsSchoolsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients_schools', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->bigInteger('client_id');
            $table->bigInteger('school_id');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamp('status_updated_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            
            $table->foreign('client_id', 'clients_schools_ibfk_1')->references('id')->on('clients');
            $table->foreign('school_id', 'clients_schools_ibfk_2')->references('id')->on('schools');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clients_schools');
    }
}
