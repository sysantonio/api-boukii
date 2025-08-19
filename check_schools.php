<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== SCHOOLS ===\n";
$schools = App\Models\School::all(['id', 'name']);
foreach ($schools as $school) {
    echo "ID: {$school->id} - {$school->name}\n";
}

echo "\n=== SAMPLE USERS ===\n";
$users = App\Models\User::take(5)->get(['id', 'name', 'email']);
foreach ($users as $user) {
    echo "ID: {$user->id} - {$user->name} ({$user->email})\n";
}

echo "\n=== CHECKING FOR SCHOOL 2 ===\n";
$school2 = App\Models\School::find(2);
if ($school2) {
    echo "School ID 2 exists: {$school2->name}\n";
} else {
    echo "School ID 2 does not exist\n";
}