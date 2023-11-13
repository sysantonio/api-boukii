<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('username')->nullable();
            $table->string('email', 100)->nullable();
            $table->string('password');
            $table->string('image')->default('')->index('image');
            $table->string('type', 100);
            $table->boolean('active')->default(1)->comment("avoids login");
            $table->text('recover_token')->nullable();
            $table->timestamps();
            $table->integer('deleted_at')->nullable();
            $table->boolean('logout')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
