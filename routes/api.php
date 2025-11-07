<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\ChapterController;
use App\Http\Controllers\Api\SlideController;
use App\Http\Controllers\Api\PricingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Api\AdminStudentPerformanceController;
use App\Http\Controllers\Api\ActivityLogController;


/*
Route::get('/chapters', [ChapterController::class, 'index']);
Route::get('/chapters/{id}', [ChapterController::class, 'show']);
Route::get('/chapters/{id}/slides', [SlideController::class, 'getChapterSlides']);
*/
Route::middleware('api')->group(function () {
    // Auth routes (no middleware needed)
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

    // Pricing (public route)
    Route::get('/pricing', [PricingController::class, 'index']);



    Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);

    // Quiz routes
    Route::get('/quizzes', [QuizController::class, 'getAllQuizzes']);
    Route::get('/quizzes/category/{category}', [QuizController::class, 'getQuizzesByCategory']);
    Route::get('/quizzes/{quizId}', [QuizController::class, 'getQuiz']);
    Route::get('/quizzes/{quizId}/attempts', [QuizController::class, 'getQuizAttempts']);
    Route::get('/chapters/{chapterId}/quiz', [QuizController::class, 'getQuizByChapter']);
    Route::post('/quizzes/{quizId}/submit', [QuizController::class, 'submitQuiz']);
    Route::get('/quiz/results', [QuizController::class, 'getResults']);
    Route::get('/quiz/results/{resultId}', [QuizController::class, 'getResult']);
    Route::get('/quiz/results/{resultId}/detailed', [QuizController::class, 'getDetailedResult']);


    // Slides
    Route::get('/slides/{id}', [SlideController::class, 'show']);
    Route::post('/slides/{id}/view', [SlideController::class, 'markViewed']);
    Route::post('/slides/{id}/complete', [SlideController::class, 'markCompleted']);
    Route::post('/slides/{id}/time', [SlideController::class, 'updateTimeSpent']);
    Route::get('/slides/{id}/next', [SlideController::class, 'getNext']);
    Route::get('/slides/{id}/previous', [SlideController::class, 'getPrevious']);

    // User progress
    Route::get('/user/progress', [ChapterController::class, 'getUserProgress']);
    Route::post('/chapters/{id}/complete', [ChapterController::class, 'markComplete']);

    // Activity Logging (Heartbeat & Engagement Tracking)
    Route::post('/activity/heartbeat', [ActivityLogController::class, 'heartbeat']);
    Route::post('/activity/slide/viewed', [ActivityLogController::class, 'logSlideViewed']);
    Route::post('/activity/slide/completed', [ActivityLogController::class, 'logSlideCompleted']);
    Route::post('/activity/quiz/started', [ActivityLogController::class, 'logQuizStarted']);
    Route::post('/activity/quiz/completed', [ActivityLogController::class, 'logQuizCompleted']);
    Route::get('/activity/my-activity', [ActivityLogController::class, 'getUserActivity']);

    Route::get('/chapters', [ChapterController::class, 'index']);
    Route::get('/chapters/{id}', [ChapterController::class, 'show']);
    Route::get('/chapters/{id}/slides', [SlideController::class, 'getChapterSlides']);

    // Premium & Payment routes
    Route::get('/premium/status', [PaymentController::class, 'getPremiumStatus']);
    Route::post('/payments/submit', [PaymentController::class, 'submitPayment']);
    Route::get('/payments/history', [PaymentController::class, 'getPaymentHistory']);
    Route::get('/payments/pending', [PaymentController::class, 'getPendingPayment']);

    // Admin Student Performance routes
    Route::prefix('admin/performance')->group(function () {
        Route::get('/students', [AdminStudentPerformanceController::class, 'getAllStudents']);
        Route::get('/students/{userId}', [AdminStudentPerformanceController::class, 'getStudentPerformance']);
        Route::post('/students/{userId}/report', [AdminStudentPerformanceController::class, 'generateReport']);
        Route::get('/students/{userId}/analytics', [AdminStudentPerformanceController::class, 'getAnalytics']);
        Route::post('/students/{userId}/export/excel', [AdminStudentPerformanceController::class, 'exportToExcel']);
        Route::post('/students/{userId}/export/pdf', [AdminStudentPerformanceController::class, 'exportToPdf']);
        Route::post('/students/{userId}/email', [AdminStudentPerformanceController::class, 'emailReport']);
    });

    });
});

// Test route
Route::get('/test', function () {
    return response()->json(['message' => 'Backend connection successful!']);
});
