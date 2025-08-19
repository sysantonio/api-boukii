<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FINAL V5 USER-SCHOOL SETUP ===\n";

// Get our test users
$user1 = App\Models\User::where('email', 'user1@boukii.test')->first();
$user2 = App\Models\User::where('email', 'user2@boukii.test')->first();

if (!$user1 || !$user2) {
    echo "Error: Test users not found.\n";
    exit(1);
}

echo "Found users:\n";
echo "- User 1: {$user1->email} (ID: {$user1->id})\n";
echo "- User 2: {$user2->email} (ID: {$user2->id})\n";

// Create school_users table if it doesn't exist
echo "\n--- Creating school_users table if needed ---\n";
if (!Schema::hasTable('school_users')) {
    Schema::create('school_users', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('school_id');
        $table->boolean('active_school')->default(true);
        $table->timestamps();
        
        $table->unique(['user_id', 'school_id'], 'uniq_user_school');
    });
    echo "✓ Created school_users table\n";
} else {
    echo "✓ school_users table already exists\n";
}

// Create user-school relationships
echo "\n--- Creating user-school relationships ---\n";

// User 1 -> School 2 only
DB::table('school_users')->updateOrInsert([
    'user_id' => $user1->id,
    'school_id' => 2
], [
    'created_at' => now(),
    'updated_at' => now()
]);
echo "✓ User 1 -> School 2 (ESS Veveyse)\n";

// User 2 -> Schools 2 and 3
DB::table('school_users')->updateOrInsert([
    'user_id' => $user2->id,
    'school_id' => 2
], [
    'created_at' => now(),
    'updated_at' => now()
]);
echo "✓ User 2 -> School 2 (ESS Veveyse)\n";

DB::table('school_users')->updateOrInsert([
    'user_id' => $user2->id,
    'school_id' => 3
], [
    'created_at' => now(),
    'updated_at' => now()
]);
echo "✓ User 2 -> School 3 (ESS Les Crosets-Champoussin)\n";

echo "\n=== VERIFICATION ===\n";
$user1Schools = $user1->schools()->get(['schools.id', 'schools.name']);
$user2Schools = $user2->schools()->get(['schools.id', 'schools.name']);

echo "User 1 schools ({$user1Schools->count()}):\n";
foreach ($user1Schools as $school) {
    echo "  - {$school->name} (ID: {$school->id})\n";
}

echo "\nUser 2 schools ({$user2Schools->count()}):\n";
foreach ($user2Schools as $school) {
    echo "  - {$school->name} (ID: {$school->id})\n";
}

// Test the V5 auth endpoint
echo "\n=== TESTING V5 AUTH ===\n";
echo "Testing user1@boukii.test...\n";

// Simulate the checkUser request
$user = App\Models\User::where('email', 'user1@boukii.test')->first();
$schools = $user->schools()->select(['schools.id', 'schools.name', 'schools.slug'])->get();

echo "API would return:\n";
echo "- Schools count: {$schools->count()}\n";
echo "- Requires school selection: " . ($schools->count() > 1 ? 'Yes' : 'No') . "\n";

echo "\n🎯 SETUP COMPLETED SUCCESSFULLY!\n";
echo "✅ User 1 (user1@boukii.test): School 2 only\n";
echo "✅ User 2 (user2@boukii.test): Schools 2 & 3\n";
echo "✅ Password: password123\n";
echo "✅ V5 authentication should now work!\n";