<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING TABLE STRUCTURES ===\n";

// Check school_users table structure
if (Schema::hasTable('school_users')) {
    echo "\nschool_users table columns:\n";
    $columns = DB::select('DESCRIBE school_users');
    foreach ($columns as $column) {
        echo "  - {$column->Field} ({$column->Type})\n";
    }
} else {
    echo "\nschool_users table does not exist\n";
}

// Check if there are other similar tables
echo "\n=== Looking for user-school relationship tables ===\n";
$tables = DB::select("SHOW TABLES LIKE '%user%school%'");
foreach ($tables as $table) {
    $tableName = array_values((array) $table)[0];
    echo "Found table: {$tableName}\n";
    
    $columns = DB::select("DESCRIBE {$tableName}");
    foreach ($columns as $column) {
        echo "  - {$column->Field} ({$column->Type})\n";
    }
    echo "\n";
}