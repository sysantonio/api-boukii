<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('school_user')) {
            Schema::create('school_user', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('school_id');
                $table->json('role_data')->nullable(); // For storing role information
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                // Primary key is composite
                $table->primary(['user_id', 'school_id']);
                
                // Foreign key constraints
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->cascadeOnDelete();
                    
                $table->foreign('school_id')
                    ->references('id')
                    ->on('schools')
                    ->cascadeOnDelete();
                    
                // Indexes for performance
                $table->index('is_active');
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('school_user');
    }
};