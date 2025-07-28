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
        Schema::create('season_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained();
            $table->string('snapshot_type');
            $table->longText('snapshot_data')->nullable();
            $table->timestamp('snapshot_date')->nullable();
            $table->boolean('is_immutable')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->text('description')->nullable();
            $table->string('checksum', 64);
            $table->timestamps();

            $table->index(['season_id', 'snapshot_type']);
            $table->index(['is_immutable', 'snapshot_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('season_snapshots');
    }
};
