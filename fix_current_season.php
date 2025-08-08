<?php

// This script will create a truly current season for testing

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Season;
use Carbon\Carbon;

try {
    echo "=== Fixing current season for testing ===" . PHP_EOL;
    
    $now = Carbon::now();
    echo "Current date: " . $now->toDateString() . PHP_EOL;
    
    // Update old season to not be current
    Season::where('school_id', 2)->update(['is_current' => 0]);
    
    // Create or update a season that covers today's date
    $currentSeason = Season::updateOrCreate(
        [
            'school_id' => 2,
            'name' => 'Temporada Test 2025'
        ],
        [
            'start_date' => $now->copy()->startOfYear(), // Start of 2025
            'end_date' => $now->copy()->endOfYear(),     // End of 2025  
            'is_active' => 1,
            'is_current' => 1,
            'is_historical' => 0,
            'hour_start' => '08:00:00',
            'hour_end' => '18:00:00'
        ]
    );
    
    echo "✅ Current season updated: " . $currentSeason->name . PHP_EOL;
    echo "   Start: " . $currentSeason->start_date . PHP_EOL;
    echo "   End: " . $currentSeason->end_date . PHP_EOL;
    echo "   ID: " . $currentSeason->id . PHP_EOL;
    
    echo PHP_EOL . "Now testing login should auto-complete!" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
}