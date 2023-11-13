<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchoolUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('school_users', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->bigInteger('school_id');
            $table->bigInteger('user_id');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('school_id', 'school_users_ibfk_1')->references('id')->on('schools');
            $table->foreign('user_id', 'school_users_ibfk_2')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('school_users');
    }
}
