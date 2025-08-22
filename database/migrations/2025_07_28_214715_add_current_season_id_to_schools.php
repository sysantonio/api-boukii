<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('schools') &&
            Schema::hasTable('seasons') &&
            ! Schema::hasColumn('schools', 'current_season_id')
        ) {
            Schema::table('schools', function (Blueprint $table) {
                $table->foreignId('current_season_id')->nullable()->constrained('seasons');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('schools') && Schema::hasColumn('schools', 'current_season_id')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->dropConstrainedForeignId('current_season_id');
            });
        }
    }
};
