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

class ChapterController extends Controller
{
    use ApiResponse;
    /**
     * Get all chapters with user progress
     */
    public function index()
    {
        $userId = Auth::id();
        
        $chapters = Chapter::where('is_published', true)
            ->orderBy('chapter_number')
            ->get()
            ->map(function ($chapter) use ($userId) {
                // Get total slides count
                $totalSlides = $chapter->slides()->count();
                
                // Count completed slides
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
                $user = Auth::user();
                $isLocked = $chapter->is_premium && !$user->isPremiumActive();

                return [
                    'id' => $chapter->id,
                    'title' => $chapter->title,
                    'description' => $chapter->description,
                    'chapter_number' => $chapter->chapter_number,
                    'is_published' => $chapter->is_published,
                    'is_premium' => $chapter->is_premium,
                    'is_locked' => $isLocked,
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

        return $this->successResponse([
            'chapters' => $chapters
        ], 'Chapters retrieved successfully');
    }

    /**
     * Get single chapter with slides
     */
    public function show($id)
    {
        $chapter = Chapter::with('slides')->findOrFail($id);

        // Check if content is locked for premium users
        if ($chapter->is_premium && Auth::check() && !Auth::user()->isPremiumActive()) {
            return $this->errorResponse('This chapter is premium content. Please upgrade to access.', 403);
        }

        if (Auth::check()) {
            $userId = Auth::id();

            // Mark chapter as started if not already
            UserProgress::firstOrCreate(
                [
                    'user_id' => $userId,
                    'chapter_id' => $id,
                ],
                [
                    'status' => 'in_progress',
                    'started_at' => now(),
                ]
            );

            // Add progress to each slide
            $chapter->slides->each(function ($slide) use ($userId) {
                $progress = SlideProgress::where('user_id', $userId)
                    ->where('slide_id', $slide->id)
                    ->first();

                $slide->is_completed = $progress ? $progress->completed : false;
            });
        }

        // Add is_upcoming for scheduled meetings
        $chapterData = $chapter->toArray();
        if ($chapter->video_type === 'scheduled' && $chapter->meeting_datetime) {
            $chapterData['is_upcoming'] = now() < $chapter->meeting_datetime;
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
        
        return $this->successResponse(null, 'Chapter marked as completed');
    }


    /**
     * Get user's overall progress
     */
    public function getUserProgress()
    {
        $userId = Auth::id();

        // Count total published chapters
        $totalChapters = Chapter::where('is_published', true)->count();
        
        // Count completed chapters from user_progress table
        $completedChapters = UserProgress::where('user_id', $userId)
            ->where('status', 'completed')
            ->count();

        // Count total slides in published chapters
        $totalSlides = DB::table('slides')
            ->join('chapters', 'slides.chapter_id', '=', 'chapters.id')
            ->where('chapters.is_published', true)
            ->count();

        // Count completed slides
        $completedSlides = SlideProgress::where('user_id', $userId)
            ->where('completed', true)
            ->count();

        // Count total active quizzes
        $totalQuizzes = DB::table('quizzes')
            ->where('is_active', true)
            ->count();

        // Count passed quizzes (best attempt only)
        $passedQuizzes = DB::table('quiz_results')
            ->select('quiz_id')
            ->where('user_id', $userId)
            ->where('passed', true)
            ->groupBy('quiz_id')
            ->get()
            ->count();

        // Calculate weighted progress
        // Slides: 60%, Quizzes: 40%
        $slideProgress = $totalSlides > 0 ? ($completedSlides / $totalSlides) * 60 : 0;
        $quizProgress = $totalQuizzes > 0 ? ($passedQuizzes / $totalQuizzes) * 40 : 0;
        $overallProgress = round($slideProgress + $quizProgress);

        return $this->successResponse([
            'statistics' => [
                'total_chapters' => $totalChapters,
                'completed_chapters' => $completedChapters,
                'total_slides' => $totalSlides,
                'completed_slides' => $completedSlides,
                'total_quizzes' => $totalQuizzes,
                'passed_quizzes' => $passedQuizzes,
                'slide_progress' => round($slideProgress),
                'quiz_progress' => round($quizProgress),
                'overall_progress' => $overallProgress
            ]
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
        UserProgress::firstOrCreate(
            [
                'user_id' => $userId,
                'chapter_id' => $chapter->id,
            ],
            [
                'status' => 'in_progress',
                'started_at' => now(),
            ]
        );

        return $this->successResponse(null, 'Chapter started');
    }
}