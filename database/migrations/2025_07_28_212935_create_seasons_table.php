<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('seasons')) {
            Schema::create('seasons', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->date('start_date');
                $table->date('end_date');
                $table->time('hour_start')->nullable();
                $table->time('hour_end')->nullable();
                $table->boolean('is_active')->default(false);
                $table->string('vacation_days')->nullable();
                $table->unsignedBigInteger('school_id');
                $table->boolean('is_closed')->default(false);
                $table->timestamp('closed_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['school_id', 'start_date', 'end_date'], 'idx_seasons_school_dates');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seasons');
    }
};
