<?php
/**
 * Test authentication endpoint
 * Run: php test_auth_endpoint.php
 */

$apiUrl = 'http://localhost:8000/api/login';

echo "Testing Login Endpoint: $apiUrl\n";
echo "================================\n\n";

// Test data
$testData = [
    'email' => 'test@example.com',
    'password' => 'wrongpassword'
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Origin: http://localhost:3000'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ CURL Error: $error\n";
    echo "\n🔧 Possible fixes:\n";
    echo "1. Make sure Laravel server is running: php artisan serve\n";
    echo "2. Check if port 8000 is not blocked\n";
    exit(1);
}

echo "HTTP Status Code: $httpCode\n";
echo "Response:\n";
echo json_encode(json_decode($response), JSON_PRETTY_PRINT);
echo "\n\n";

if ($httpCode == 200 || $httpCode == 401 || $httpCode == 422) {
    echo "✅ Backend is responding correctly!\n";
    echo "✅ CORS headers are working!\n";
    echo "\nIf you're getting CORS errors in browser:\n";
    echo "1. Restart your Laravel server: php artisan serve\n";
    echo "2. Restart your React dev server: npm start\n";
    echo "3. Clear browser cache (Ctrl+Shift+Delete)\n";
    echo "4. Try incognito/private mode\n";
} else if ($httpCode == 500) {
    echo "❌ Server Error (500) - Check Laravel logs\n";
    echo "Run: tail -20 storage/logs/laravel.log\n";
} else {
    echo "⚠️  Unexpected response code: $httpCode\n";
}
