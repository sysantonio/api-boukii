<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('school_users') && ! Schema::hasTable('school_user')) {
            Schema::rename('school_users', 'school_user');
        }

        if (! Schema::hasTable('school_user')) {
            Schema::create('school_user', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('school_id');
                $table->timestamps();
                $table->primary(['user_id', 'school_id']);
            });

            return;
        }

        // Drop legacy id column if present
        if (Schema::hasColumn('school_user', 'id')) {
            Schema::table('school_user', function (Blueprint $table) {
                $table->dropColumn('id');
            });
        }

        Schema::table('school_user', function (Blueprint $table) {
            if (! Schema::hasColumn('school_user', 'user_id')) {
                $table->unsignedBigInteger('user_id');
            }
            if (! Schema::hasColumn('school_user', 'school_id')) {
                $table->unsignedBigInteger('school_id');
            }
            if (! Schema::hasColumn('school_user', 'created_at') && ! Schema::hasColumn('school_user', 'updated_at')) {
                $table->timestamps();
            }
        });

        $sm = Schema::getConnection()->getDoctrineSchemaManager();
        $details = $sm->listTableDetails('school_user');
        if (! $details->hasPrimaryKey()) {
            Schema::table('school_user', function (Blueprint $table) {
                $table->primary(['user_id', 'school_id']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('school_user')) {
            Schema::drop('school_user');
        }
    }
};
