<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDegreesSchoolSportGoalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('degrees_school_sport_goals', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->bigInteger('degree_id');
            $table->text('name');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('degree_id', 'degrees_school_sport_goals_ibfk_1')->references('id')->on('degrees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('degrees_school_sport_goals');
    }
}
