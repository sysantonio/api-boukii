<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchoolSalaryLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('school_salary_levels', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->bigInteger('school_id');
            $table->string('name', 100)->default('');
            $table->float('pay', 8, 2)->default(0.00);
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('school_id', 'school_salary_levels_ibfk_1')->references('id')->on('schools');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('school_salary_levels');
    }
}
