<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// CSRF Debug Routes - REMOVE IN PRODUCTION
Route::get('/debug-csrf', function() {
    return response()->json([
        'csrf_token' => csrf_token(),
        'session_id' => session()->getId(),
        'session_status' => session()->isStarted(),
        'env' => [
            'app_url' => config('app.url'),
            'app_env' => config('app.env'),
            'session_driver' => config('session.driver'),
            'session_domain' => config('session.domain'),
            'session_secure' => config('session.secure'),
            'session_same_site' => config('session.same_site'),
        ],
        'cookies' => request()->cookies->all(),
    ]);
});

Route::post('/test-csrf', function() {
    return response()->json([
        'status' => 'success',
        'message' => 'CSRF validation passed!',
        'timestamp' => now()->toIso8601String()
    ]);
})->middleware('web');
