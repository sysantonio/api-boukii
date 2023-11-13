<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluationFulfilledGoalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('evaluation_fulfilled_goals', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->bigInteger('evaluation_id');
            $table->bigInteger('degrees_school_sport_goals_id')->index('degrees_school_sport_goals_id');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('evaluation_id', 'evaluation_fulfilled_goals_ibfk_1')->references('id')->on('evaluations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('evaluation_fulfilled_goals');
    }
}
