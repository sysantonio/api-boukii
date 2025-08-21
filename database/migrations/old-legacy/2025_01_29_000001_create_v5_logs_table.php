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
        Schema::create('v5_logs', function (Blueprint $table) {
            $table->id();
            $table->string('correlation_id')->nullable()->index();
            $table->string('level', 20)->index();
            $table->string('category', 50)->nullable()->index();
            $table->string('operation')->nullable();
            $table->text('message');
            $table->json('context')->nullable();
            $table->json('extra')->nullable();
            
            // Request information
            $table->string('request_method', 10)->nullable();
            $table->text('request_url')->nullable();
            $table->ipAddress('user_ip')->nullable();
            $table->text('user_agent')->nullable();
            
            // User and system context
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('season_id')->nullable()->index();
            $table->unsignedBigInteger('school_id')->nullable()->index();
            
            // Performance metrics
            $table->float('memory_usage_mb')->nullable();
            $table->float('memory_peak_mb')->nullable();
            $table->float('response_time_ms')->nullable();
            
            // System information
            $table->string('server_name')->nullable();
            $table->string('environment', 20)->nullable();
            $table->string('application_version', 20)->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['level', 'created_at']);
            $table->index(['category', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['correlation_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('v5_logs');
    }
};