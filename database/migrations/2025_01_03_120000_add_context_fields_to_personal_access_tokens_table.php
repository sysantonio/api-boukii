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
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->unsignedBigInteger('school_id')->nullable()->after('tokenable_id');
            $table->unsignedBigInteger('season_id')->nullable()->after('school_id');
            $table->json('context_data')->nullable()->after('season_id');
            
            // Índices para mejorar performance
            $table->index(['school_id', 'season_id']);
            $table->index(['tokenable_id', 'school_id']);
            
            // Foreign keys (opcional, depende de tu esquema)
            // $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            // $table->foreign('season_id')->references('id')->on('seasons')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->dropIndex(['school_id', 'season_id']);
            $table->dropIndex(['tokenable_id', 'school_id']);
            
            $table->dropColumn(['school_id', 'season_id', 'context_data']);
        });
    }
};