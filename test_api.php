<?php

// Test script to check API response
require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use App\Models\Chapter;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get or create a test user
$user = User::first();
if (!$user) {
    echo "No users found in database. Please create a user first.\n";
    exit(1);
}

echo "Testing with user: {$user->email}\n";
echo "User premium status: " . ($user->isPremiumActive() ? 'Yes' : 'No') . "\n";
echo "---\n\n";

// Simulate the ChapterController index method
$userId = $user->id;
$isPremium = $user->isPremiumActive();

$chapters = Chapter::where('is_published', true)
    ->orderBy('chapter_number')
    ->with(['slides:id,chapter_id'])
    ->get();

echo "Found " . $chapters->count() . " published chapters:\n\n";

foreach ($chapters as $chapter) {
    $isScheduled = $chapter->isScheduled();
    $totalSlides = $chapter->slides->count();
    $isLocked = $chapter->is_premium && !$isPremium;

    echo "Chapter {$chapter->chapter_number}: {$chapter->title}\n";
    echo "  - ID: {$chapter->id}\n";
    echo "  - Is Published: " . ($chapter->is_published ? 'Yes' : 'No') . "\n";
    echo "  - Is Premium: " . ($chapter->is_premium ? 'Yes' : 'No') . "\n";
    echo "  - Is Locked: " . ($isLocked ? 'Yes' : 'No') . "\n";
    echo "  - Is Scheduled: " . ($isScheduled ? 'Yes' : 'No') . "\n";
    echo "  - Publish At: " . ($chapter->publish_at ? $chapter->publish_at->format('Y-m-d H:i:s') : 'Immediate') . "\n";
    echo "  - Total Slides: {$totalSlides}\n";
    echo "  - Video Type: {$chapter->video_type}\n";
    echo "  - Video URL: " . ($chapter->video_url ?? 'None') . "\n";
    echo "\n";
}

echo "---\n";
echo "API would return this data for /api/chapters endpoint\n";
