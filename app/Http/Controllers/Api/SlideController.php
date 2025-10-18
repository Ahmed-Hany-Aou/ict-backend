<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Slide;
use App\Models\SlideProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SlideController extends Controller
{
    public function getChapterSlides($chapterId)
    {
        $slides = Slide::where('chapter_id', $chapterId)
            ->orderBy('slide_number')
            ->get();

        if (Auth::check()) {
            $userId = Auth::id();
            $slides->each(function ($slide) use ($userId) {
                $progress = SlideProgress::where('user_id', $userId)
                    ->where('slide_id', $slide->id)
                    ->first();
                
                $slide->user_completed = $progress ? $progress->completed : false;
                $slide->last_viewed = $progress ? $progress->last_viewed_at : null;
            });
        }

        return response()->json([
            'success' => true,
            'slides' => $slides
        ]);
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

        return response()->json([
            'success' => true,
            'slide' => $slide
        ]);
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

        return response()->json([
            'success' => true,
            'message' => 'Slide marked as viewed'
        ]);
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

        return response()->json([
            'success' => true,
            'message' => 'Slide marked as completed'
        ]);
    }

    public function getNext($id)
    {
        $currentSlide = Slide::findOrFail($id);
        
        $nextSlide = Slide::where('chapter_id', $currentSlide->chapter_id)
            ->where('slide_number', '>', $currentSlide->slide_number)
            ->orderBy('slide_number')
            ->first();

        if (!$nextSlide) {
            return response()->json([
                'success' => false,
                'message' => 'This is the last slide'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'slide' => $nextSlide
        ]);
    }

    public function getPrevious($id)
    {
        $currentSlide = Slide::findOrFail($id);
        
        $previousSlide = Slide::where('chapter_id', $currentSlide->chapter_id)
            ->where('slide_number', '<', $currentSlide->slide_number)
            ->orderBy('slide_number', 'desc')
            ->first();

        if (!$previousSlide) {
            return response()->json([
                'success' => false,
                'message' => 'This is the first slide'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'slide' => $previousSlide
        ]);
    }
}