#!/usr/bin/env php
<?php

/**
 * Test Payment Upload API
 * This tests the actual HTTP endpoint to verify it works correctly
 */

// Configuration
$baseUrl = 'http://127.0.0.1:8000';
$token = null; // Will be fetched by logging in

echo "🧪 Payment Upload API Test\n";
echo str_repeat("=", 60) . "\n\n";

// Step 1: Login to get token
echo "1️⃣  Logging in...\n";

$loginData = [
    'email' => 'admin@admin.com', // Change if needed
    'password' => 'admin'
];

$ch = curl_init("$baseUrl/api/login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo "❌ Login failed! HTTP $httpCode\n";
    echo "Response: $response\n";
    exit(1);
}

$loginResult = json_decode($response, true);
if (!isset($loginResult['token'])) {
    echo "❌ No token in response!\n";
    echo "Response: $response\n";
    exit(1);
}

$token = $loginResult['token'];
echo "✅ Login successful! Token: " . substr($token, 0, 20) . "...\n\n";

// Step 2: Create a test image file
echo "2️⃣  Creating test image...\n";

// Create a small PNG image (1x1 pixel red dot)
$imageData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DwHwAFBQIAX8jx0gAAAABJRU5ErkJggg==');
$tempFile = sys_get_temp_dir() . '/test_payment_' . time() . '.png';
file_put_contents($tempFile, $imageData);

echo "✅ Test image created: $tempFile\n";
echo "   Size: " . filesize($tempFile) . " bytes\n\n";

// Step 3: Submit payment with screenshot
echo "3️⃣  Submitting payment...\n";

$ch = curl_init("$baseUrl/api/payments/submit");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);

// IMPORTANT: Using multipart/form-data with CURLFile
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'payment_reference' => 'TEST_' . time(),
    'amount' => '199.00',
    'screenshot' => new CURLFile($tempFile, 'image/png', 'test_screenshot.png')
]);

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
    'Accept: application/json'
]);

echo "   📤 Sending request...\n";
echo "   Field name: 'screenshot'\n";
echo "   Content-Type: multipart/form-data (automatic)\n\n";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Step 4: Check response
echo "4️⃣  Response:\n";
echo "   HTTP Status: $httpCode\n";
echo "   Body: " . json_encode(json_decode($response, true), JSON_PRETTY_PRINT) . "\n\n";

// Clean up
unlink($tempFile);

if ($httpCode === 201) {
    echo "✅ SUCCESS! Payment uploaded successfully!\n\n";
    echo "👉 Check admin panel: $baseUrl/admin/payments\n";
    exit(0);
} else {
    echo "❌ FAILED! HTTP $httpCode\n\n";

    $error = json_decode($response, true);
    if (isset($error['errors'])) {
        echo "Validation errors:\n";
        foreach ($error['errors'] as $field => $messages) {
            echo "  - $field: " . implode(', ', $messages) . "\n";
        }
    }

    exit(1);
}
