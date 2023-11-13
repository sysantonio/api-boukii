<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMonitorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('monitors', function (Blueprint $table) {
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
            $table->string('avs')->default('');
            $table->string('work_license')->default('');
            $table->string('bank_details')->default('');
            $table->boolean('children')->default(0);
            $table->boolean('civil_status')->default(0);
            $table->boolean('family_allowance')->default(0);
            $table->string('partner_work_license')->default('');
            $table->boolean('partner_works')->default(0);
            $table->integer('partner_percentaje')->default(0);
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('active_school');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();

            $table->foreign('language1_id', 'monitors_ibfk_1')->references('id')->on('languages');
            $table->foreign('language2_id', 'monitors_ibfk_2')->references('id')->on('languages');
            $table->foreign('language3_id', 'monitors_ibfk_3')->references('id')->on('languages');
            $table->foreign('language3_id', 'monitors_ibfk_4')->references('id')->on('languages');
            $table->foreign('language3_id', 'monitors_ibfk_5')->references('id')->on('languages');
            $table->foreign('language3_id', 'monitors_ibfk_6')->references('id')->on('languages');
            $table->foreign('user_id', 'monitors_ibfk_7')->references('id')->on('users');
            $table->foreign('active_school', 'monitors_ibfk_8')->references('id')->on('schools');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('monitors');
    }
}
