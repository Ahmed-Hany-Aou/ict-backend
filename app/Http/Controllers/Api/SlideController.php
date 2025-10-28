<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Models\Slide;
use App\Models\SlideProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

                // --- THIS IS THE CRITICAL FIX ---
                // Decode the JSON string from the database into a PHP array/object
                'content' => json_decode($slide->content),

                'video_url' => $slide->video_url, // Include video_url if it might exist on slides in the future

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

        $progress = SlideProgress::updateOrCreate(
            [
                'user_id' => $userId,
                'slide_id' => $id,
            ],
            [
                'chapter_id' => $slide->chapter_id,
                'last_viewed_at' => now(),
            ]
        );

        return $this->successResponse(null, 'Slide marked as viewed');
    }

    public function markCompleted(Request $request, $id)
    {
        $slide = Slide::findOrFail($id);
        $userId = Auth::id();

        $progress = SlideProgress::updateOrCreate(
            [
                'user_id' => $userId,
                'slide_id' => $id,
            ],
            [
                'chapter_id' => $slide->chapter_id,
                'completed' => true,
                'last_viewed_at' => now(),
            ]
        );

        return $this->successResponse(null, 'Slide marked as completed');
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
}