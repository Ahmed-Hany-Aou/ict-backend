<?php

// Quick test script to create a test payment record
// Run: php test_payment_api.php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

echo "Creating test payment...\n";

// Get first user
$user = User::first();

if (!$user) {
    echo "❌ No users found! Create a user first.\n";
    exit(1);
}

echo "✓ Using user: {$user->name} ({$user->email})\n";

// Create a fake screenshot file
$testImageContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
$filename = 'test_payment_' . time() . '.png';
Storage::disk('public')->put('payment_screenshots/' . $filename, $testImageContent);

echo "✓ Created test screenshot: {$filename}\n";

// Create payment record
$payment = Payment::create([
    'user_id' => $user->id,
    'payment_reference' => 'TEST' . time(),
    'amount' => 199.00,
    'screenshot_path' => 'payment_screenshots/' . $filename,
    'status' => 'pending',
]);

echo "✓ Payment created! ID: {$payment->id}\n";
echo "\n";
echo "✅ SUCCESS! Now check Filament admin:\n";
echo "   http://127.0.0.1:8000/admin/payments\n";
echo "\n";
echo "You should see the test payment with status 'pending'\n";
echo "You can approve/reject it from there!\n";
