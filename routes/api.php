<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\ChapterController;
use App\Http\Controllers\Api\SlideController;



Route::get('/chapters', [ChapterController::class, 'index']);
Route::get('/chapters/{id}', [ChapterController::class, 'show']);
Route::get('/chapters/{id}/slides', [SlideController::class, 'getChapterSlides']);

Route::middleware('api')->group(function () {
    // Auth routes (no middleware needed)
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);




    Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/quiz/submit', [QuizController::class, 'submitQuiz']);
    Route::get('/quiz/results', [QuizController::class, 'getResults']);
    Route::get('/profile', [AuthController::class, 'profile']);


    // Slides
    Route::get('/slides/{id}', [SlideController::class, 'show']);
    Route::post('/slides/{id}/view', [SlideController::class, 'markViewed']);
    Route::post('/slides/{id}/complete', [SlideController::class, 'markCompleted']);
    Route::get('/slides/{id}/next', [SlideController::class, 'getNext']);
    Route::get('/slides/{id}/previous', [SlideController::class, 'getPrevious']);

    // User progress
    Route::get('/user/progress', [ChapterController::class, 'getUserProgress']);
    


    });
});

// Test route
Route::get('/test', function () {
    return response()->json(['message' => 'Backend connection successful!']);
});



