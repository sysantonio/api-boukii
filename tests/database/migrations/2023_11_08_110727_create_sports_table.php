<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sports', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('name');
            $table->string('icon_selected', 500);
            $table->string('icon_unselected', 500);
            $table->bigInteger('sport_type');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('sport_type', 'sports_ibfk_1')->references('id')->on('sport_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sports');
    }
}
