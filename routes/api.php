<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\ChapterController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


// Test route
Route::get('/test', function () {
    return response()->json(['message' => 'Backend connection successful!']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/quiz/submit', [QuizController::class, 'submitQuiz']);
    Route::get('/quiz/results', [QuizController::class, 'getResults']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::get('/chapters', [ChapterController::class, 'index']);
});
