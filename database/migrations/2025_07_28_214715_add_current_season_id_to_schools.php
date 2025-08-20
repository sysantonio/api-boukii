<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('schools') && Schema::hasTable('seasons')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->foreignId('current_season_id')->nullable()->constrained('seasons');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('schools')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->dropForeign(['current_season_id']);
                $table->dropColumn('current_season_id');
            });
        }
    }
};
