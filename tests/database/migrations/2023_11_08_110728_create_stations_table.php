<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stations', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('name');
            $table->text('cp')->nullable();
            $table->text('city')->nullable();
            $table->text('country');
            $table->text('province');
            $table->string('address', 100);
            $table->string('image', 500);
            $table->string('map', 500)->default('');
            $table->string('latitude', 100);
            $table->string('longitude', 100);
            $table->integer('num_hanger')->default(0);
            $table->integer('num_chairlift')->default(0);
            $table->integer('num_cabin')->default(0);
            $table->integer('num_cabin_large')->default(0);
            $table->integer('num_fonicular')->default(0);
            $table->boolean('show_details')->default(0);
            $table->boolean('active')->default(0);
            $table->text('accuweather')->nullable();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stations');
    }
}
