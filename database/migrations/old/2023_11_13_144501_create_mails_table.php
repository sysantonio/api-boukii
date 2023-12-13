<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('mails', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type');
            $table->string('subject');
            $table->text('body');
            $table->bigInteger('school_id')->index('school_id');
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mails');
    }
};
