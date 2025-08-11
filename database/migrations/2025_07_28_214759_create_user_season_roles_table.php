<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_season_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id'); // Match existing users table
            $table->unsignedBigInteger('season_id');
            $table->string('role');
            $table->timestamps();
            
            // Foreign keys without cascade to avoid issues
            $table->index('user_id');
            $table->index('season_id');
            
            // Unique constraint
            $table->unique(['user_id', 'season_id'], 'uniq_user_season');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_season_roles');
    }
};
