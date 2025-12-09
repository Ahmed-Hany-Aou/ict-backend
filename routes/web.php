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

Route::get('/test-n8n', function () {
    try {
        // 1. Create dummy image (valid 1x1 pixel JPEG)
        $fileName = 'test_screenshot_' . time() . '.jpg';
        // 1x1 red pixel JPEG hex dump
        $hex = 'FFD8FFE000104A46494600010101004800480000FFDB004300FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFDB004301FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFC00011080001000103012200021101031101FFC4001F0000010501010101010100000000000000000102030405060708090A0BFFC400B5100002010303020403050504040000017D01020300041105122131410613516107227114328191A1082342B1C11552D1F02433627282090A161718191A25262728292A3435363738393A434445464748494A535455565758595A636465666768696A737475767778797A838485868788898A92939495969798999AA2A3A4A5A6A7A8A9AAB2B3B4B5B6B7B8B9BAC2C3C4C5C6C7C8C9CAD2D3D4D5D6D7D8D9DAE1E2E3E4E5E6E7E8E9EAF1F2F3F4F5F6F7F8F9FAFFC4001F0100030101010101010101010000000000000102030405060708090A0BFFC400B51100020102040403040705040400010277000102031104052131061241510761711322328108144291A1B1C109233352F0156272D10A162434E125F11718191A262728292A35363738393A434445464748494A535455565758595A636465666768696A737475767778797A82838485868788898A92939495969798999AA2A3A4A5A6A7A8A9AAB2B3B4B5B6B7B8B9BAC2C3C4C5C6C7C8C9CAD2D3D4D5D6D7D8D9DAE2E3E4E5E6E7E8E9EAF2F3F4F5F6F7F8F9FAFFDA000C03010002110311003F00F58A28A2803FFFD9';
        $contents = pack('H*', $hex);
        
        \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $contents);
        
        // 2. Create dummy payment
        $user = \App\Models\User::first();
        if (!$user) {
            // Create a dummy user if none exists
            $user = \App\Models\User::create([
                'name' => 'Test User',
                'email' => 'test' . time() . '@example.com',
                'password' => bcrypt('password'),
            ]);
        }
        
        $payment = \App\Models\Payment::create([
            'user_id' => $user->id,
            'payment_reference' => 'TEST-N8N-' . time(),
            'screenshot_path' => $fileName,
            'amount' => 50.00,
            'status' => 'pending',
        ]);
        
        // 3. Dispatch event
        \App\Events\PaymentSubmitted::dispatch($payment);
        
        return response()->json([
            'success' => true,
            'message' => 'PaymentSubmitted event dispatched!',
            'payment_id' => $payment->id,
            'screenshot_path' => $fileName,
            'n8n_webhook_url' => config('services.n8n.webhook_url'),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});
