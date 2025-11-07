<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    use ApiResponse;

    /**
     * Log a heartbeat from the frontend
     * This is called periodically (e.g., every 30 seconds) while user is active
     *
     * Usage: Send from frontend every 30 seconds with current page/slide info
     */
    public function heartbeat(Request $request)
    {
        try {
            $request->validate([
                'slide_id' => 'nullable|exists:slides,id',
                'quiz_id' => 'nullable|exists:quizzes,id',
                'chapter_id' => 'nullable|exists:chapters,id',
                'duration' => 'nullable|integer|min:0',
                'metadata' => 'nullable|array',
            ]);

            ActivityLog::logActivity(
                userId: Auth::id(),
                activityType: ActivityLog::TYPE_HEARTBEAT,
                slideId: $request->slide_id,
                quizId: $request->quiz_id,
                chapterId: $request->chapter_id,
                duration: $request->duration,
                metadata: $request->metadata
            );

            return $this->successResponse(null, 'Heartbeat recorded');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            \Log::error('Heartbeat error: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to record heartbeat', $e);
        }
    }

    /**
     * Log when a slide is viewed
     */
    public function logSlideViewed(Request $request)
    {
        try {
            $request->validate([
                'slide_id' => 'required|exists:slides,id',
                'chapter_id' => 'required|exists:chapters,id',
                'metadata' => 'nullable|array',
            ]);

            ActivityLog::logActivity(
                userId: Auth::id(),
                activityType: ActivityLog::TYPE_SLIDE_VIEWED,
                slideId: $request->slide_id,
                chapterId: $request->chapter_id,
                metadata: $request->metadata
            );

            return $this->successResponse(null, 'Slide view logged');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            \Log::error('Log slide viewed error: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to log slide view', $e);
        }
    }

    /**
     * Log when a slide is completed
     */
    public function logSlideCompleted(Request $request)
    {
        try {
            $request->validate([
                'slide_id' => 'required|exists:slides,id',
                'chapter_id' => 'required|exists:chapters,id',
                'duration' => 'nullable|integer|min:0',
                'metadata' => 'nullable|array',
            ]);

            ActivityLog::logActivity(
                userId: Auth::id(),
                activityType: ActivityLog::TYPE_SLIDE_COMPLETED,
                slideId: $request->slide_id,
                chapterId: $request->chapter_id,
                duration: $request->duration,
                metadata: $request->metadata
            );

            return $this->successResponse(null, 'Slide completion logged');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            \Log::error('Log slide completed error: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to log slide completion', $e);
        }
    }

    /**
     * Log when a quiz is started
     */
    public function logQuizStarted(Request $request)
    {
        try {
            $request->validate([
                'quiz_id' => 'required|exists:quizzes,id',
                'chapter_id' => 'nullable|exists:chapters,id',
                'metadata' => 'nullable|array',
            ]);

            ActivityLog::logActivity(
                userId: Auth::id(),
                activityType: ActivityLog::TYPE_QUIZ_STARTED,
                quizId: $request->quiz_id,
                chapterId: $request->chapter_id,
                metadata: $request->metadata
            );

            return $this->successResponse(null, 'Quiz start logged');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            \Log::error('Log quiz started error: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to log quiz start', $e);
        }
    }

    /**
     * Log when a quiz is completed
     */
    public function logQuizCompleted(Request $request)
    {
        try {
            $request->validate([
                'quiz_id' => 'required|exists:quizzes,id',
                'chapter_id' => 'nullable|exists:chapters,id',
                'duration' => 'nullable|integer|min:0',
                'metadata' => 'nullable|array',
            ]);

            ActivityLog::logActivity(
                userId: Auth::id(),
                activityType: ActivityLog::TYPE_QUIZ_COMPLETED,
                quizId: $request->quiz_id,
                chapterId: $request->chapter_id,
                duration: $request->duration,
                metadata: $request->metadata
            );

            return $this->successResponse(null, 'Quiz completion logged');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            \Log::error('Log quiz completed error: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to log quiz completion', $e);
        }
    }

    /**
     * Get user's activity log (for debugging or analytics)
     */
    public function getUserActivity(Request $request)
    {
        try {
            $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'activity_type' => 'nullable|string',
                'limit' => 'nullable|integer|min:1|max:100',
            ]);

            $query = ActivityLog::where('user_id', Auth::id())
                ->with(['slide', 'quiz', 'chapter']);

            if ($request->start_date) {
                $query->where('activity_timestamp', '>=', $request->start_date);
            }

            if ($request->end_date) {
                $query->where('activity_timestamp', '<=', $request->end_date);
            }

            if ($request->activity_type) {
                $query->where('activity_type', $request->activity_type);
            }

            $activities = $query->orderBy('activity_timestamp', 'desc')
                ->limit($request->limit ?? 50)
                ->get();

            return $this->successResponse([
                'activities' => $activities,
                'total' => $activities->count(),
            ], 'Activity log retrieved successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            \Log::error('Get user activity error: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to get activity log', $e);
        }
    }
}
