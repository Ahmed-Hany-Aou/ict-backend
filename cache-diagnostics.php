<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "═══════════════════════════════════════════════════════════════\n";
echo "           CACHE DIAGNOSTICS & VERIFICATION TOOL\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// 1. Check cache configuration
echo "📋 CACHE CONFIGURATION\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  Cache Driver: " . config('cache.default') . "\n";
echo "  Cache Prefix: " . config('cache.prefix') . "\n";
echo "  Cache Table:  " . config('cache.stores.database.table', 'cache') . "\n\n";

// 2. Count cache entries
echo "📊 CACHE STATISTICS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$totalEntries = DB::table('cache')->count();
echo "  Total cache entries: {$totalEntries}\n\n";

// 3. Show all cache keys
if ($totalEntries > 0) {
    echo "🔑 CACHE KEYS (All entries)\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $cacheEntries = DB::table('cache')->get();
    foreach ($cacheEntries as $index => $entry) {
        $expiresAt = date('Y-m-d H:i:s', $entry->expiration);
        $isExpired = $entry->expiration < time() ? '❌ EXPIRED' : '✅ VALID';
        echo sprintf("  %2d. %s %s\n", $index + 1, $entry->key, $isExpired);
        echo "      Expires: {$expiresAt}\n";
    }
    echo "\n";
}

// 4. Check for quiz-related caches
echo "🎯 QUIZ-RELATED CACHES\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$quizCaches = DB::table('cache')
    ->where('key', 'LIKE', '%quiz%')
    ->get();

if ($quizCaches->count() > 0) {
    foreach ($quizCaches as $cache) {
        echo "  ✓ " . $cache->key . "\n";
    }
} else {
    echo "  ✅ No quiz caches found (GOOD - means they were cleared!)\n";
}
echo "\n";

// 5. Check for chapter-related caches
echo "📚 CHAPTER-RELATED CACHES\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$chapterCaches = DB::table('cache')
    ->where('key', 'LIKE', '%chapter%')
    ->get();

if ($chapterCaches->count() > 0) {
    foreach ($chapterCaches as $cache) {
        echo "  ✓ " . $cache->key . "\n";
    }
} else {
    echo "  ✅ No chapter caches found (GOOD!)\n";
}
echo "\n";

// 6. Test cache clearing
echo "🧪 TESTING CACHE CLEARING\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

// Test Quiz Cache Clearing
echo "  Testing CacheService::clearQuizCaches()...\n";
$beforeCount = DB::table('cache')->where('key', 'LIKE', '%quiz%')->count();
\App\Services\CacheService::clearQuizCaches();
$afterCount = DB::table('cache')->where('key', 'LIKE', '%quiz%')->count();

if ($afterCount === 0) {
    echo "  ✅ Quiz caches cleared successfully! ({$beforeCount} → {$afterCount})\n";
} else {
    echo "  ⚠️ Warning: {$afterCount} quiz caches still remain\n";
}

// Test Chapter Cache Clearing
echo "  Testing CacheService::clearChapterCaches()...\n";
$beforeCount = DB::table('cache')->where('key', 'LIKE', '%chapter%')->count();
\App\Services\CacheService::clearChapterCaches();
$afterCount = DB::table('cache')->where('key', 'LIKE', '%chapter%')->count();

if ($afterCount === 0) {
    echo "  ✅ Chapter caches cleared successfully! ({$beforeCount} → {$afterCount})\n";
} else {
    echo "  ⚠️ Warning: {$afterCount} chapter caches still remain\n";
}

echo "\n";

// 7. Final recommendation
echo "💡 RECOMMENDATIONS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$remainingCaches = DB::table('cache')->count();

if ($remainingCaches === 0) {
    echo "  ✅ All caches cleared! System is using fresh data.\n";
} else {
    echo "  ⚠️ {$remainingCaches} cache entries still present.\n";
    echo "  Run: php artisan cache:clear\n";
    echo "  Or:  DB::table('cache')->truncate();\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "                    DIAGNOSTIC COMPLETE\n";
echo "═══════════════════════════════════════════════════════════════\n";
