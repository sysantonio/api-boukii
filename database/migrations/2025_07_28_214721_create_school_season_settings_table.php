<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_season_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained();
            $table->foreignId('season_id')->constrained();
            $table->string('key');
            $table->json('value')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'season_id', 'key'], 'uniq_school_season_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_season_settings');
    }
};
