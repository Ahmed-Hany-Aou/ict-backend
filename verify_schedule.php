<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Waiting for you to save Chapter 7 with scheduled date...\n";
echo "Press Enter after you've saved the chapter in the admin panel...\n";
fgets(STDIN);

echo "\n=== CHECKING CHAPTER 7 ===\n\n";

$chapter = App\Models\Chapter::find(7);

if ($chapter) {
    echo "ID: {$chapter->id}\n";
    echo "Title: {$chapter->title}\n";
    echo "Published: " . ($chapter->is_published ? 'Yes' : 'No') . "\n";
    echo "Publish At: " . ($chapter->publish_at ? $chapter->publish_at->format('Y-m-d H:i:s') : 'NULL') . "\n";
    echo "Is Scheduled: " . ($chapter->isScheduled() ? 'YES ✓' : 'NO ✗') . "\n";
    echo "Is Live: " . ($chapter->isLive() ? 'YES' : 'NO') . "\n";

    if ($chapter->isScheduled()) {
        echo "\n✅ SUCCESS! Chapter is scheduled and will show as 'Coming Soon' on frontend!\n";
    } else {
        echo "\n❌ Chapter is NOT scheduled. Please check:\n";
        echo "   1. 'Published' toggle is ON\n";
        echo "   2. 'Scheduled Publish Date' is set to a FUTURE date\n";
        echo "   3. You clicked 'Save' button\n";
    }
} else {
    echo "Chapter 7 not found!\n";
}

echo "\n=== CURRENT SERVER TIME ===\n";
echo now()->format('Y-m-d H:i:s') . " (must be BEFORE publish_at for scheduling)\n";
