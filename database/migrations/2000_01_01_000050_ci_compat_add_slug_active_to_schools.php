<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('schools')) {
            Schema::table('schools', function (Blueprint $table) {
                // slug (unique) si falta
                if (! Schema::hasColumn('schools', 'slug')) {
                    $table->string('slug')->nullable()->unique()->after('name');
                }
                // active (bool) si falta
                if (! Schema::hasColumn('schools', 'active')) {
                    $table->boolean('active')->default(true)->after('slug');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('schools')) {
            Schema::table('schools', function (Blueprint $table) {
                if (Schema::hasColumn('schools', 'active')) {
                    $table->dropColumn('active');
                }
                if (Schema::hasColumn('schools', 'slug')) {
                    // Algunos SQLite no soportan dropUnique por nombre; el dropColumn es suficiente en CI
                    $table->dropColumn('slug');
                }
            });
        }
    }
};
