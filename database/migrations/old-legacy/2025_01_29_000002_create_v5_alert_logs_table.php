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
        Schema::create('v5_alert_logs', function (Blueprint $table) {
            $table->id();
            $table->string('alert_id')->unique()->index();
            $table->string('type', 100)->index();
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->index();
            $table->text('message');
            $table->json('data')->nullable();
            $table->string('correlation_id')->nullable()->index();
            
            // Resolution tracking
            $table->boolean('resolved')->default(false)->index();
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->text('resolution_notes')->nullable();
            
            // Notification tracking
            $table->boolean('email_sent')->default(false);
            $table->timestamp('email_sent_at')->nullable();
            $table->json('notification_channels')->nullable();
            
            // Context
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('season_id')->nullable()->index();
            $table->unsignedBigInteger('school_id')->nullable()->index();
            
            $table->timestamps();
            
            // Indexes for dashboard queries
            $table->index(['priority', 'resolved', 'created_at']);
            $table->index(['type', 'created_at']);
            $table->index(['correlation_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('v5_alert_logs');
    }
};