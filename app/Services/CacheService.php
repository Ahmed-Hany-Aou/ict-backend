<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Centralized Cache Management Service
 * Provides reliable cache clearing across different cache drivers
 */
class CacheService
{
    /**
     * Clear all application caches
     * Works reliably with database, redis, file drivers and cache prefixes
     */
    public static function clearAll(): void
    {
        $driver = config('cache.default');

        // For database driver, directly truncate the cache table
        // This is more reliable than Cache::flush() with prefixes
        if ($driver === 'database') {
            $table = config('cache.stores.database.table', 'cache');
            DB::table($table)->truncate();
        } else {
            // For other drivers (redis, file, etc.), use Cache::flush()
            Cache::flush();
        }

        // Also clear the application cache and config cache
        \Artisan::call('cache:clear');
        \Artisan::call('config:clear');
    }

    /**
     * Clear user-specific caches
     * Call this when user's premium status changes
     */
    public static function clearUserCache(int $userId): void
    {
        // Clear user-specific chapter caches (both premium states)
        Cache::forget("chapters_list_user_{$userId}_premium_true");
        Cache::forget("chapters_list_user_{$userId}_premium_false");
        Cache::forget("user_progress_{$userId}");

        // Clear all chapter-specific caches for this user
        self::clearChapterCaches();
    }

    /**
     * Clear chapter-related caches
     * Call this when chapters or slides are created/updated/deleted
     */
    public static function clearChapterCaches(): void
    {
        Cache::forget("chapters_published_with_scheduled");
        Cache::forget("platform_stats");

        // Clear all user chapter list caches
        self::clearPattern("chapters_list_user_*");
        self::clearPattern("chapter_*_user_*");
        self::clearPattern("user_progress_*");
    }

    /**
     * Clear quiz-related caches
     * Call this when quizzes are created/updated/deleted
     */
    public static function clearQuizCaches(): void
    {
        Cache::forget("quizzes_all_with_scheduled_premium_true");
        Cache::forget("quizzes_all_with_scheduled_premium_false");
        Cache::forget("quizzes_active_with_scheduled_ordered");

        // Clear category-specific quiz caches
        self::clearPattern("quizzes_category_*");
    }

    /**
     * Clear slide-related caches
     * Call this when slides are created/updated/deleted
     */
    public static function clearSlideCaches(): void
    {
        // Slides affect chapter data, so clear chapter caches
        self::clearChapterCaches();
    }

    /**
     * Clear caches matching a pattern (for database driver)
     * This works with the cache prefix
     */
    private static function clearPattern(string $pattern): void
    {
        $driver = config('cache.default');

        if ($driver === 'database') {
            $table = config('cache.stores.database.table', 'cache');
            $prefix = config('cache.prefix', '');

            // Convert wildcard pattern to SQL LIKE pattern
            $likePattern = $prefix . str_replace('*', '%', $pattern);

            DB::table($table)
                ->where('key', 'LIKE', $likePattern)
                ->delete();
        } else {
            // For Redis and other drivers that support patterns
            // Note: This is a simple implementation, might need refinement for production
            Cache::flush();
        }
    }
}
