<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->tinyInteger('course_type')->index('fk_courses2_type_idx');
            $table->boolean('is_flexible');
            $table->bigInteger('sport_id');
            $table->bigInteger('school_id');
            $table->bigInteger('station_id')->nullable();
            $table->text('name');
            $table->text('short_description');
            $table->text('description');
            $table->decimal('price', 8, 2)->comment("If duration_flexible, per 15min");
            $table->string('currency', 3)->default('CHF');
            $table->integer('max_participants')->default(1);
            $table->time('duration');
            $table->boolean('duration_flexible')->default(0);
            $table->date('date_start');
            $table->date('date_end');
            $table->date('date_start_res')->nullable();
            $table->date('date_end_res')->nullable();
            $table->string('hour_min')->nullable();
            $table->string('hour_max')->nullable();
            $table->boolean('confirm_attendance')->default(0);
            $table->boolean('active')->default(1);
            $table->boolean('online')->default(0);
            $table->longText('image')->nullable();
            $table->json('translations')->nullable();
            $table->json('price_range')->nullable();
            $table->json('discounts')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('sport_id', 'courses_ibfk_1')->references('id')->on('sports');
            $table->foreign('school_id', 'courses_ibfk_2')->references('id')->on('schools');
            $table->foreign('station_id', 'courses_ibfk_3')->references('id')->on('stations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('courses');
    }
}
