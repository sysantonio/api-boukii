<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('school_colors')) {
            Schema::table('school_colors', function (Blueprint $table) {
                $table->decimal('price', 10, 2)->nullable()->after('default');
            });
        }

        if (Schema::hasTable('monitor_nwd')) {
            Schema::table('monitor_nwd', function (Blueprint $table) {
                $table->decimal('price', 10, 2)->nullable()->after('user_nwd_subtype_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('school_colors')) {
            Schema::table('school_colors', function (Blueprint $table) {
                $table->dropColumn('price');
            });
        }

        if (Schema::hasTable('monitor_nwd')) {
            Schema::table('monitor_nwd', function (Blueprint $table) {
                $table->dropColumn('price');
            });
        }
    }
};
