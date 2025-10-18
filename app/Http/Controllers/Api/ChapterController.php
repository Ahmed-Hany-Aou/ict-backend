<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\UserProgress;
use App\Models\SlideProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChapterController extends Controller
{
    /**
     * Get all published chapters
     */
    public function index()
    {
        $chapters = Chapter::where('is_published', true)
            ->orderBy('chapter_number')
            ->get();

        // Add user progress if authenticated
        if (Auth::check()) {
            $userId = Auth::id();
            
            $chapters->each(function ($chapter) use ($userId) {
                // Get overall chapter progress
                $userProgress = UserProgress::where('user_id', $userId)
                    ->where('chapter_id', $chapter->id)
                    ->first();

                // Calculate slide completion percentage
                $totalSlides = $chapter->slides()->count();
                $completedSlides = SlideProgress::where('user_id', $userId)
                    ->where('chapter_id', $chapter->id)
                    ->where('completed', true)
                    ->count();

                $chapter->progress_percentage = $totalSlides > 0 
                    ? round(($completedSlides / $totalSlides) * 100) 
                    : 0;
                
                $chapter->status = $userProgress ? $userProgress->status : 'not_started';
                $chapter->slides_count = $totalSlides;
                $chapter->completed_slides = $completedSlides;
            });
        }

        return response()->json([
            'success' => true,
            'chapters' => $chapters
        ]);
    }

    /**
     * Get single chapter with slides
     */
    public function show($id)
    {
        $chapter = Chapter::with('slides')->findOrFail($id);

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

                $slide->completed = $progress ? $progress->completed : false;
            });
        }

        return response()->json([
            'success' => true,
            'chapter' => $chapter
        ]);
    }

    /**
     * Get user's progress for all chapters
     */
    public function getUserProgress()
    {
        $userId = Auth::id();

        $progress = UserProgress::where('user_id', $userId)
            ->with('chapter:id,title,chapter_number')
            ->get();

        // Calculate overall statistics
        $totalChapters = Chapter::where('is_published', true)->count();
        $completedChapters = UserProgress::where('user_id', $userId)
            ->where('status', 'completed')
            ->count();

        $totalSlides = Chapter::where('is_published', true)
            ->withCount('slides')
            ->get()
            ->sum('slides_count');

        $completedSlides = SlideProgress::where('user_id', $userId)
            ->where('completed', true)
            ->count();

        return response()->json([
            'success' => true,
            'statistics' => [
                'total_chapters' => $totalChapters,
                'completed_chapters' => $completedChapters,
                'total_slides' => $totalSlides,
                'completed_slides' => $completedSlides,
                'overall_progress' => $totalSlides > 0 
                    ? round(($completedSlides / $totalSlides) * 100) 
                    : 0
            ],
            'chapter_progress' => $progress
        ]);
    }
}