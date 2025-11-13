<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Models\Chapter;
use App\Models\UserProgress;
use App\Models\SlideProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ChapterController extends Controller
{
    use ApiResponse;
    /**
     * Get all chapters with user progress (including scheduled chapters)
     * Cached for 5 minutes per user
     */
    public function index()
    {
        $userId = Auth::id();
        $user = Auth::user();
        $cacheKey = "chapters_list_user_{$userId}_premium_{$user->isPremiumActive()}";

        // Cache the result for 5 minutes (300 seconds)
        $chapters = Cache::remember($cacheKey, 300, function () use ($userId, $user) {
            // Cache chapter base data separately (rarely changes)
            // NOW INCLUDING scheduled chapters (is_published = true, regardless of publish_at)
            $chaptersBase = Cache::remember('chapters_published_with_scheduled', 3600, function () {
                return Chapter::where('is_published', true)
                    ->orderBy('chapter_number')
                    ->with(['slides:id,chapter_id']) // Only load IDs for counting
                    ->get();
            });

            return $chaptersBase->map(function ($chapter) use ($userId, $user) {
                // Check if chapter is scheduled (published but future date)
                $isScheduled = $chapter->isScheduled();

                // Get total slides count from relationship
                $totalSlides = $chapter->slides->count();

                // Count completed slides with optimized query
                $completedSlides = SlideProgress::where('chapter_id', $chapter->id)
                    ->where('user_id', $userId)
                    ->where('completed', true)
                    ->count();

                // Calculate progress percentage
                $progressPercentage = $totalSlides > 0
                    ? round(($completedSlides / $totalSlides) * 100)
                    : 0;

                // Check user_progress table for chapter completion status
                $userProgress = UserProgress::where('user_id', $userId)
                    ->where('chapter_id', $chapter->id)
                    ->first();

                // Determine status
                $status = 'not_started';
                if ($userProgress) {
                    $status = $userProgress->status;
                } elseif ($progressPercentage > 0 && $progressPercentage < 100) {
                    $status = 'in_progress';
                } elseif ($progressPercentage === 100) {
                    $status = 'completed';
                }

                // Check if content is locked
                $isLocked = $chapter->is_premium && !$user->isPremiumActive();

                return [
                    'id' => $chapter->id,
                    'title' => $chapter->title,
                    'description' => $chapter->description,
                    'chapter_number' => $chapter->chapter_number,
                    'is_published' => $chapter->is_published,
                    'is_premium' => $chapter->is_premium,
                    'is_locked' => $isLocked,
                    'is_scheduled' => $isScheduled, // NEW: Frontend uses this for "Coming Soon" UI
                    'publish_at' => $chapter->publish_at ? $chapter->publish_at->format('Y-m-d H:i:s') : null, // NEW
                    'total_slides' => $totalSlides,
                    'slides_count' => $totalSlides, // Alias for compatibility
                    'completed_slides' => $completedSlides,
                    'progress_percentage' => $progressPercentage,
                    'status' => $status,
                    'started_at' => $userProgress ? $userProgress->started_at : null,
                    'completed_at' => $userProgress ? $userProgress->completed_at : null,
                    'video_url' => $chapter->video_url ?? null,
                    'video_type' => $chapter->video_type ?? 'none',
                    'meeting_datetime' => $chapter->meeting_datetime ? $chapter->meeting_datetime->format('Y-m-d H:i:s') : null,
                    'is_upcoming' => ($chapter->video_type === 'scheduled' && $chapter->meeting_datetime) ? (now() < $chapter->meeting_datetime) : null,
                    'meeting_link' => $chapter->meeting_link ?? null,
                ];
            });
        });

        return $this->successResponse([
            'chapters' => $chapters
        ], 'Chapters retrieved successfully');
    }

    /**
     * Get single chapter with slides
     * Cached for 10 minutes per user
     */
    public function show($id)
    {
        $userId = Auth::check() ? Auth::id() : null;
        $cacheKey = $userId ? "chapter_{$id}_user_{$userId}" : "chapter_{$id}_guest";

        // Cache chapter data for 10 minutes
        $chapterData = Cache::remember($cacheKey, 600, function () use ($id, $userId) {
            // Cache chapter base data (rarely changes) for 1 hour
            $chapter = Cache::remember("chapter_{$id}_base", 3600, function () use ($id) {
                return Chapter::with('slides')->findOrFail($id);
            });

            // Check if chapter is scheduled (not yet published)
            if (!$chapter->isLive()) {
                return ['error' => 'This chapter is not yet available.', 'status' => 403];
            }

            // Check if content is locked for premium users
            if ($chapter->is_premium && Auth::check() && !Auth::user()->isPremiumActive()) {
                return ['error' => 'This chapter is premium content. Please upgrade to access.', 'status' => 403];
            }

            if (Auth::check()) {
                // Mark chapter as started if not already
                $userProgress = UserProgress::firstOrCreate(
                    [
                        'user_id' => $userId,
                        'chapter_id' => $id,
                    ],
                    [
                        'status' => 'in_progress',
                        'started_at' => now(),
                    ]
                );

                // If created, clear user's chapter list cache
                if ($userProgress->wasRecentlyCreated) {
                    $this->clearUserCache($userId);
                }

                // Get all slide progress for this chapter in one query (optimized)
                $slideProgressMap = SlideProgress::where('user_id', $userId)
                    ->where('chapter_id', $id)
                    ->pluck('completed', 'slide_id')
                    ->toArray();

                // Add progress to each slide
                $chapter->slides->each(function ($slide) use ($slideProgressMap) {
                    $slide->is_completed = $slideProgressMap[$slide->id] ?? false;
                });
            }

            // Add is_upcoming for scheduled meetings
            $data = $chapter->toArray();
            if ($chapter->video_type === 'scheduled' && $chapter->meeting_datetime) {
                $data['is_upcoming'] = now() < $chapter->meeting_datetime;
            }

            return $data;
        });

        // Handle premium error from cache
        if (isset($chapterData['error'])) {
            return $this->errorResponse($chapterData['error'], $chapterData['status']);
        }

        return $this->successResponse([
            'chapter' => $chapterData
        ], 'Chapter retrieved successfully');
    }

    /**
     * Mark chapter as completed
     */
    public function markComplete($id)
    {
        $userId = Auth::id();
        $chapter = Chapter::findOrFail($id);

        // Replace all the old logic with this one block
        UserProgress::query()->updateOrInsert(
            [
                // Columns to MATCH against
                'user_id' => $userId,
                'chapter_id' => $chapter->id,
            ],
            [
                // Columns to UPDATE or INSERT
                'status' => 'completed',
                'completed_at' => now(),
                // Your original logic will work here because updateOrInsert
                // does not use Eloquent model casting.
                'started_at' => DB::raw('COALESCE(started_at, NOW())'),
            ]
        );

        // Clear cache after marking complete
        $this->clearUserCache($userId);
        Cache::forget("chapter_{$id}_user_{$userId}");

        return $this->successResponse(null, 'Chapter marked as completed');
    }


    /**
     * Get user's overall progress
     * Cached for 2 minutes per user
     */
    public function getUserProgress()
    {
        $userId = Auth::id();
        $cacheKey = "user_progress_{$userId}";

        // Cache user progress for 2 minutes
        $statistics = Cache::remember($cacheKey, 120, function () use ($userId) {
            // Cache platform statistics (rarely changes) for 10 minutes
            // NOTE: For stats, we only count LIVE chapters (not scheduled)
            $platformStats = Cache::remember('platform_stats', 600, function () {
                return [
                    'total_chapters' => Chapter::where('is_published', true)
                        ->where(function ($query) {
                            $query->whereNull('publish_at')
                                ->orWhere('publish_at', '<=', now());
                        })
                        ->count(),
                    'total_slides' => DB::table('slides')
                        ->join('chapters', 'slides.chapter_id', '=', 'chapters.id')
                        ->where('chapters.is_published', true)
                        ->where(function ($query) {
                            $query->whereNull('chapters.publish_at')
                                ->orWhere('chapters.publish_at', '<=', now());
                        })
                        ->count(),
                    'total_quizzes' => DB::table('quizzes')
                        ->where('is_active', true)
                        ->where(function ($query) {
                            $query->whereNull('publish_at')
                                ->orWhere('publish_at', '<=', now());
                        })
                        ->count(),
                ];
            });

            // Count completed chapters from user_progress table
            $completedChapters = UserProgress::where('user_id', $userId)
                ->where('status', 'completed')
                ->count();

            // Count completed slides (only from LIVE chapters)
            $completedSlides = DB::table('slide_progress')
                ->join('slides', 'slide_progress.slide_id', '=', 'slides.id')
                ->join('chapters', 'slides.chapter_id', '=', 'chapters.id')
                ->where('slide_progress.user_id', $userId)
                ->where('slide_progress.completed', true)
                ->where('chapters.is_published', true)
                ->where(function ($query) {
                    $query->whereNull('chapters.publish_at')
                        ->orWhere('chapters.publish_at', '<=', now());
                })
                ->count();

            // Count passed quizzes (best attempt only)
            $passedQuizzes = DB::table('quiz_results')
                ->select('quiz_id')
                ->where('user_id', $userId)
                ->where('passed', true)
                ->groupBy('quiz_id')
                ->get()
                ->count();

            // Calculate actual percentages (0-100)
            $slideProgressPercentage = $platformStats['total_slides'] > 0
                ? ($completedSlides / $platformStats['total_slides']) * 100
                : 0;
            $quizProgressPercentage = $platformStats['total_quizzes'] > 0
                ? ($passedQuizzes / $platformStats['total_quizzes']) * 100
                : 0;

            // Calculate weighted overall progress
            // Slides: 60%, Quizzes: 40%
            $slideWeightedProgress = $slideProgressPercentage * 0.6;
            $quizWeightedProgress = $quizProgressPercentage * 0.4;
            $overallProgress = round($slideWeightedProgress + $quizWeightedProgress);

            return [
                'total_chapters' => $platformStats['total_chapters'],
                'completed_chapters' => $completedChapters,
                'total_slides' => $platformStats['total_slides'],
                'completed_slides' => $completedSlides,
                'total_quizzes' => $platformStats['total_quizzes'],
                'passed_quizzes' => $passedQuizzes,
                'slide_progress' => round($slideProgressPercentage),
                'quiz_progress' => round($quizProgressPercentage),
                'overall_progress' => $overallProgress
            ];
        });

        return $this->successResponse([
            'statistics' => $statistics
        ], 'User progress retrieved successfully');
    }

    /**
     * Start a chapter
     */
    public function start($id)
    {
        $userId = Auth::id();
        $chapter = Chapter::findOrFail($id);

        // Mark chapter as started if not already started
        $userProgress = UserProgress::firstOrCreate(
            [
                'user_id' => $userId,
                'chapter_id' => $chapter->id,
            ],
            [
                'status' => 'in_progress',
                'started_at' => now(),
            ]
        );

        // Clear cache if newly created
        if ($userProgress->wasRecentlyCreated) {
            $this->clearUserCache($userId);
        }

        return $this->successResponse(null, 'Chapter started');
    }

    /**
     * Clear user-specific caches
     * Call this whenever user progress changes
     */
    private function clearUserCache($userId)
    {
        $user = Auth::user();
        $premiumStatus = $user ? $user->isPremiumActive() : false;

        // Clear all user-specific caches
        Cache::forget("chapters_list_user_{$userId}_premium_{$premiumStatus}");
        Cache::forget("chapters_published_with_scheduled"); // NEW: Clear the base cache too
        Cache::forget("user_progress_{$userId}");

        // Clear all chapter-specific caches for this user
        $chapterIds = Chapter::pluck('id');
        foreach ($chapterIds as $chapterId) {
            Cache::forget("chapter_{$chapterId}_user_{$userId}");
        }
    }
}