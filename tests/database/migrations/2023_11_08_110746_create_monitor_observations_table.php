<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMonitorObservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('monitor_observations', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('general', 5000)->default('');
            $table->string('notes', 5000)->default('');
            $table->string('historical', 5000)->default('');
            $table->bigInteger('monitor_id');
            $table->bigInteger('school_id');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('monitor_id', 'monitor_observations_ibfk_1')->references('id')->on('monitors');
            $table->foreign('school_id', 'monitor_observations_ibfk_2')->references('id')->on('schools');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('monitor_observations');
    }
}
