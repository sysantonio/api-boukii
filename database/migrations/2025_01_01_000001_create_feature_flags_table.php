<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Agregar feature flags a la tabla schools
        Schema::table('schools', function (Blueprint $table) {
            $table->json('feature_flags')->nullable()->after('settings');
            $table->timestamp('feature_flags_updated_at')->nullable()->after('feature_flags');
            $table->boolean('is_test_school')->default(false)->after('is_active');
            $table->boolean('has_microgate_integration')->default(false)->after('is_test_school');
            $table->json('whatsapp_config')->nullable()->after('has_microgate_integration');
            $table->string('webhook_url')->nullable()->after('whatsapp_config');
            
            $table->index('is_test_school');
            $table->index('has_microgate_integration');
        });

        // Crear tabla para historial de feature flags
        Schema::create('feature_flag_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->json('changes'); // {'flag_name': {'from': old_value, 'to': new_value}}
            $table->text('reason');
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            $table->index(['school_id', 'created_at']);
            $table->index('user_id');
        });

        // Crear tabla para logs de migración V5
        Schema::create('v5_migration_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $table->string('migration_type'); // 'data', 'feature_flag', 'rollback'
            $table->string('status'); // 'pending', 'running', 'completed', 'failed'
            $table->json('metadata')->nullable(); // Información adicional
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('progress_percentage')->default(0);
            $table->timestamps();
            
            $table->index(['school_id', 'status']);
            $table->index(['migration_type', 'status']);
        });

        // Crear tabla para métricas de performance V4 vs V5
        Schema::create('performance_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $table->string('version'); // 'v4', 'v5'
            $table->string('module'); // 'dashboard', 'planificador', 'reservas', etc.
            $table->string('action'); // 'page_load', 'api_call', 'user_action'
            $table->integer('response_time_ms');
            $table->json('metadata')->nullable(); // User agent, device info, etc.
            $table->timestamp('measured_at');
            
            $table->index(['school_id', 'version', 'module']);
            $table->index(['version', 'action', 'measured_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_metrics');
        Schema::dropIfExists('v5_migration_logs');
        Schema::dropIfExists('feature_flag_history');
        
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn([
                'feature_flags',
                'feature_flags_updated_at',
                'is_test_school',
                'has_microgate_integration',
                'whatsapp_config',
                'webhook_url'
            ]);
        });
    }
};