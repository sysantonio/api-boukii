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
        Schema::create('season_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained();
            $table->string('key');
            $table->json('value')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['season_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('season_settings');
    }
};
