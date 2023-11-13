<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('email', 100)->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('birth_date');
            $table->string('phone')->nullable();
            $table->string('telephone')->default('');
            $table->string('address')->nullable();
            $table->string('cp', 100)->nullable();
            $table->text('city')->nullable();
            $table->string('province', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->bigInteger('language1_id')->nullable();
            $table->bigInteger('language2_id')->nullable();
            $table->bigInteger('language3_id')->nullable();
            $table->bigInteger('language4_id')->nullable();
            $table->bigInteger('language5_id')->nullable();
            $table->bigInteger('language6_id')->nullable();
            $table->string('image')->default('')->index('image');
            $table->bigInteger('user_id')->nullable();
            $table->timestamps();
            $table->integer('deleted_at')->nullable();

            $table->foreign('language1_id', 'clients_ibfk_1')->references('id')->on('languages');
            $table->foreign('language2_id', 'clients_ibfk_2')->references('id')->on('languages');
            $table->foreign('language3_id', 'clients_ibfk_3')->references('id')->on('languages');
            $table->foreign('language4_id', 'clients_ibfk_4')->references('id')->on('languages');
            $table->foreign('language5_id', 'clients_ibfk_5')->references('id')->on('languages');
            $table->foreign('language6_id', 'clients_ibfk_6')->references('id')->on('languages');
            $table->foreign('user_id', 'clients_ibfk_7')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clients');
    }
}
