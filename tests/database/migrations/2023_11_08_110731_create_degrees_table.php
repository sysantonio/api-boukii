<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDegreesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('degrees', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('league');
            $table->string('level');
            $table->string('name', 100);
            $table->text('annotation')->nullable()->comment("null for unused at this school");
            $table->integer('degree_order');
            $table->integer('progress');
            $table->string('color', 10);
            $table->bigInteger('school_id')->nullable()->comment("null for default list");
            $table->bigInteger('sport_id');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('school_id', 'degrees_ibfk_1')->references('id')->on('schools');
            $table->foreign('sport_id', 'degrees_ibfk_2')->references('id')->on('sports');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('degrees');
    }
}
