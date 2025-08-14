<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Season;

echo "=== Checking seasons for school_id=2 ===" . PHP_EOL;

$seasons = Season::where('school_id', 2)->get();

foreach ($seasons as $season) {
    echo "ID: {$season->id}, Name: {$season->name}, is_current: {$season->is_current}, is_active: {$season->is_active}" . PHP_EOL;
    echo "  Dates: {$season->start_date} to {$season->end_date}" . PHP_EOL;
}

echo PHP_EOL . "=== Setting season 13 as current ===" . PHP_EOL;

// Make sure season 13 is the only current one
Season::where('school_id', 2)->update(['is_current' => 0]);
Season::where('id', 13)->update(['is_current' => 1]);

echo "✅ Season 13 set as current" . PHP_EOL;

$current = Season::where('school_id', 2)->where('is_current', 1)->first();
if ($current) {
    echo "Current season: ID {$current->id}, Name: {$current->name}" . PHP_EOL;
} else {
    echo "❌ No current season found!" . PHP_EOL;
}