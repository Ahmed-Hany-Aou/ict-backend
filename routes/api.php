<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\ChapterController;

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
    

    // Chapter endpoints
    Route::get('/chapters', [ChapterController::class, 'index']);
    Route::get('/chapters/{id}', [ChapterController::class, 'getChapter']);
    Route::get('/progress', [ChapterController::class, 'getUserProgress']);

    });
});

// Test route
Route::get('/test', function () {
    return response()->json(['message' => 'Backend connection successful!']);
});



