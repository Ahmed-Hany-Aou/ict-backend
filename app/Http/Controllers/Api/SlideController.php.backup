<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Models\Slide;
use App\Models\SlideProgress;
use App\Models\Chapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class SlideController extends Controller
{
    use ApiResponse;
   public function getChapterSlides($chapterId)
    {
        $slides = Slide::where('chapter_id', $chapterId)
            ->orderBy('slide_number')
            ->get();

        $userId = Auth::id();

        // Map slides to the format the frontend expects
        $slidesData = $slides->map(function ($slide) use ($userId) {

            $isCompleted = false;

            if ($userId) {
                $progress = SlideProgress::where('user_id', $userId)
                    ->where('slide_id', $slide->id)
                    ->first();
                $isCompleted = $progress ? (bool)$progress->completed : false;
            }

            return [
                'id' => $slide->id,
                'chapter_id' => $slide->chapter_id,
                'slide_number' => $slide->slide_number,
                'type' => $slide->type,

                // Content is already cast to array by Eloquent model
                'content' => $slide->content,

                'video_url' => $slide->video_url,

                'is_completed' => $isCompleted,
            ];
        });

        return $this->successResponse([
            'slides' => $slidesData
        ], 'Slides retrieved successfully');
    } 

    public function show($id)
    {
        $slide = Slide::findOrFail($id);

        if (Auth::check()) {
            $progress = SlideProgress::where('user_id', Auth::id())
                ->where('slide_id', $id)
                ->first();

            $slide->user_completed = $progress ? $progress->completed : false;
            $slide->last_viewed = $progress ? $progress->last_viewed_at : null;
        }

        return $this->successResponse([
            'slide' => $slide
        ], 'Slide retrieved successfully');
    }

    public function markViewed(Request $request, $id)
    {
        $slide = Slide::findOrFail($id);
        $userId = Auth::id();

        $progress = SlideProgress::where('user_id', $userId)
            ->where('slide_id', $id)
            ->first();

        if ($progress) {
            // Existing progress - increment view count and update last viewed
            $progress->update([
                'last_viewed_at' => now(),
                'view_count' => $progress->view_count + 1,
            ]);
        } else {
            // New progress - set started_at and initialize view_count
            $progress = SlideProgress::create([
                'user_id' => $userId,
                'slide_id' => $id,
                'chapter_id' => $slide->chapter_id,
                'last_viewed_at' => now(),
                'started_at' => now(),
                'view_count' => 1,
                'time_spent' => 0,
            ]);
        }

        return $this->successResponse(null, 'Slide marked as viewed');
    }

    public function markCompleted(Request $request, $id)
    {
        $request->validate([
            'time_spent' => 'nullable|integer|min:0'
        ]);

        $slide = Slide::findOrFail($id);
        $userId = Auth::id();

        $progress = SlideProgress::where('user_id', $userId)
            ->where('slide_id', $id)
            ->first();

        $updateData = [
            'chapter_id' => $slide->chapter_id,
            'completed' => true,
            'last_viewed_at' => now(),
        ];

        // Add time spent if provided
        if ($request->has('time_spent')) {
            if ($progress) {
                // Add to existing time
                $updateData['time_spent'] = $progress->time_spent + $request->time_spent;
            } else {
                $updateData['time_spent'] = $request->time_spent;
                $updateData['started_at'] = now();
                $updateData['view_count'] = 1;
            }
        }

        $progress = SlideProgress::updateOrCreate(
            [
                'user_id' => $userId,
                'slide_id' => $id,
            ],
            $updateData
        );

        // Clear user cache when slide is completed
        $this->clearSlideCache($userId, $slide->chapter_id);

        return $this->successResponse(null, 'Slide marked as completed');
    }

    /**
     * Update time spent on a slide
     * This can be called periodically from the frontend to track actual time
     */
    public function updateTimeSpent(Request $request, $id)
    {
        $request->validate([
            'time_spent' => 'required|integer|min:0'
        ]);

        $slide = Slide::findOrFail($id);
        $userId = Auth::id();

        $progress = SlideProgress::where('user_id', $userId)
            ->where('slide_id', $id)
            ->first();

        if ($progress) {
            // Add to existing time
            $progress->update([
                'time_spent' => $progress->time_spent + $request->time_spent,
                'last_viewed_at' => now(),
            ]);
        } else {
            // Create new progress with time
            $progress = SlideProgress::create([
                'user_id' => $userId,
                'slide_id' => $id,
                'chapter_id' => $slide->chapter_id,
                'last_viewed_at' => now(),
                'started_at' => now(),
                'view_count' => 1,
                'time_spent' => $request->time_spent,
            ]);
        }

        return $this->successResponse([
            'total_time_spent' => $progress->time_spent
        ], 'Time updated successfully');
    }

    public function getNext($id)
    {
        $currentSlide = Slide::findOrFail($id);
        
        $nextSlide = Slide::where('chapter_id', $currentSlide->chapter_id)
            ->where('slide_number', '>', $currentSlide->slide_number)
            ->orderBy('slide_number')
            ->first();

        if (!$nextSlide) {
            return $this->notFoundResponse('This is the last slide');
        }

        return $this->successResponse([
            'slide' => $nextSlide
        ], 'Next slide retrieved successfully');
    }

    public function getPrevious($id)
    {
        $currentSlide = Slide::findOrFail($id);
        
        $previousSlide = Slide::where('chapter_id', $currentSlide->chapter_id)
            ->where('slide_number', '<', $currentSlide->slide_number)
            ->orderBy('slide_number', 'desc')
            ->first();

        if (!$previousSlide) {
            return $this->notFoundResponse('This is the first slide');
        }

        return $this->successResponse([
            'slide' => $previousSlide
        ], 'Previous slide retrieved successfully');
    }

    /**
     * Clear slide-related caches when progress changes
     */
    private function clearSlideCache($userId, $chapterId)
    {
        $user = Auth::user();
        $premiumStatus = $user ? $user->isPremiumActive() : false;

        // Clear user progress and chapter list caches
        Cache::forget("chapters_list_user_{$userId}_premium_{$premiumStatus}");
        Cache::forget("user_progress_{$userId}");
        Cache::forget("chapter_{$chapterId}_user_{$userId}");
    }
}