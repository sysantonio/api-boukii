<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchoolSportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('school_sports', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->bigInteger('school_id');
            $table->bigInteger('sport_id')->index('sport_id');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();

            $table->foreign('school_id', 'school_sports_ibfk_1')->references('id')->on('schools');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('school_sports');
    }
}
