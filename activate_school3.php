<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ACTIVATING SCHOOL 3 ===\n";

$school3 = App\Models\School::find(3);
$school3->active = true;
$school3->save();

echo "✓ School 3 activated: {$school3->name}\n";

// Test User 2 schools again
$user2 = App\Models\User::find(20207);
$schools = $user2->schools()->get(['schools.id', 'schools.name']);

echo "\nUser 2 schools now ({$schools->count()}):\n";
foreach ($schools as $school) {
    echo "  - {$school->name} (ID: {$school->id})\n";
}

echo "\n🎯 Ready to test V5 multi-school auth!\n";