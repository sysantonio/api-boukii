<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUGGING SCHOOL 3 ISSUE ===\n";

$school3 = App\Models\School::find(3);
echo "School 3 exists: " . ($school3 ? 'Yes' : 'No') . "\n";
if ($school3) {
    echo "School 3 active: " . ($school3->active ? 'Yes' : 'No') . "\n";
    echo "School 3 deleted_at: " . ($school3->deleted_at ?: 'NULL') . "\n";
    echo "School 3 name: {$school3->name}\n";
}

echo "\nUser 2 school relationships in DB:\n";
$user2Relations = DB::table('school_users')->where('user_id', 20207)->get();
echo "Count: " . $user2Relations->count() . "\n";
foreach ($user2Relations as $rel) {
    echo "  - School ID: {$rel->school_id}\n";
}

echo "\nUser 2 schools via Eloquent (with constraints):\n";
$user2 = App\Models\User::find(20207);
$schools = $user2->schools()->get(['schools.id', 'schools.name', 'schools.active', 'schools.deleted_at']);
foreach ($schools as $school) {
    echo "  - School ID: {$school->id}, Name: {$school->name}, Active: " . ($school->active ? 'Yes' : 'No') . ", Deleted: " . ($school->deleted_at ?: 'NULL') . "\n";
}

echo "\nUser 2 schools WITHOUT constraints:\n";
$schoolsAll = DB::table('schools')
    ->join('school_users', 'schools.id', '=', 'school_users.school_id')
    ->where('school_users.user_id', 20207)
    ->select('schools.id', 'schools.name', 'schools.active', 'schools.deleted_at')
    ->get();
    
foreach ($schoolsAll as $school) {
    echo "  - School ID: {$school->id}, Name: {$school->name}, Active: " . ($school->active ? 'Yes' : 'No') . ", Deleted: " . ($school->deleted_at ?: 'NULL') . "\n";
}