<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== COMPLETING V5 USER-SEASON SETUP ===\n";

// Get our test users
$user1 = App\Models\User::where('email', 'user1@boukii.test')->first();
$user2 = App\Models\User::where('email', 'user2@boukii.test')->first();

if (!$user1 || !$user2) {
    echo "Error: Test users not found. Run create_test_users.php first.\n";
    exit(1);
}

// Get seasons
$season2 = App\Models\Season::where('school_id', 2)->where('name', 'LIKE', 'Test Season%')->first();
$season3 = App\Models\Season::where('school_id', 3)->where('name', 'LIKE', 'Test Season%')->first();

if (!$season2 || !$season3) {
    echo "Error: Test seasons not found.\n";
    exit(1);
}

echo "Found:\n";
echo "- User 1: {$user1->email} (ID: {$user1->id})\n";
echo "- User 2: {$user2->email} (ID: {$user2->id})\n";
echo "- Season 2: {$season2->name} for school {$season2->school_id} (ID: {$season2->id})\n";
echo "- Season 3: {$season3->name} for school {$season3->school_id} (ID: {$season3->id})\n";

// Create user_season_roles table if it doesn't exist
try {
    if (!Schema::hasTable('user_season_roles')) {
        echo "\n--- Creating user_season_roles table ---\n";
        Schema::create('user_season_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('season_id');
            $table->string('role');
            $table->boolean('is_active')->default(true);
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['user_id', 'season_id'], 'uniq_user_season');
            $table->index(['user_id', 'is_active'], 'idx_user_season_active');
        });
        echo "✓ Created user_season_roles table\n";
    }

    // Create user-season relationships
    echo "\n--- Creating user-season relationships ---\n";
    
    // Check table structure first
    echo "\n--- Checking table structure ---\n";
    $columns = DB::select('DESCRIBE user_season_roles');
    $columnNames = array_column($columns, 'Field');
    echo "Available columns: " . implode(', ', $columnNames) . "\n";
    
    // Prepare data based on available columns
    $baseData = [
        'role' => 'season.admin',
        'created_at' => now(),
        'updated_at' => now()
    ];
    
    // Add optional columns if they exist
    if (in_array('is_active', $columnNames)) {
        $baseData['is_active'] = true;
    }
    if (in_array('assigned_at', $columnNames)) {
        $baseData['assigned_at'] = now();
    }

    // User 1 -> Season 2 only
    DB::table('user_season_roles')->updateOrInsert([
        'user_id' => $user1->id,
        'season_id' => $season2->id
    ], $baseData);
    echo "✓ User 1 -> Season {$season2->id} (School 2: ESS Veveyse)\n";
    
    // User 2 -> Season 2 and Season 3
    DB::table('user_season_roles')->updateOrInsert([
        'user_id' => $user2->id,
        'season_id' => $season2->id
    ], $baseData);
    echo "✓ User 2 -> Season {$season2->id} (School 2: ESS Veveyse)\n";
    
    DB::table('user_season_roles')->updateOrInsert([
        'user_id' => $user2->id,
        'season_id' => $season3->id
    ], $baseData);
    echo "✓ User 2 -> Season {$season3->id} (School 3: ESS Les Crosets-Champoussin)\n";

    echo "\n=== SETUP COMPLETED ===\n";
    echo "✅ User 1 (user1@boukii.test): Access to School 2 only\n";
    echo "✅ User 2 (user2@boukii.test): Access to Schools 2 & 3\n";
    echo "✅ Password for both users: password123\n";
    echo "✅ Role: season.admin for all assignments\n";
    
    // Verify relationships
    echo "\n--- Verification ---\n";
    $user1Relations = DB::table('user_season_roles')->where('user_id', $user1->id)->get();
    $user2Relations = DB::table('user_season_roles')->where('user_id', $user2->id)->get();
    
    echo "User 1 relationships: " . $user1Relations->count() . " (should be 1)\n";
    echo "User 2 relationships: " . $user2Relations->count() . " (should be 2)\n";
    
    echo "\n🎯 Ready for V5 authentication flow testing!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}