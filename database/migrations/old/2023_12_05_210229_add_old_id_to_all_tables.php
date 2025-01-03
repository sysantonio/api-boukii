<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Obtener todas las tablas de la base de datos
        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();

        foreach ($tables as $table) {
            // Verificar si la tabla ya tiene la columna 'old_id'
            if (!Schema::hasColumn($table, 'old_id') && Schema::hasColumn($table,'deleted_at')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->integer('old_id')->nullable()->after('deleted_at');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('all_tables', function (Blueprint $table) {
            //
        });
    }
};
