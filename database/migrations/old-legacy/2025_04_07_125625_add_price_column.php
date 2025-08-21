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
        if (Schema::hasTable('monitors_schools')) {
            Schema::table('monitors_schools', function (Blueprint $table) {
                $table->decimal('block_price', 10, 2)->nullable()->after('accepted_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('monitors_schools')) {
            Schema::table('monitors_schools', function (Blueprint $table) {
                $table->dropColumn('block_price');
            });
        }
    }
};
