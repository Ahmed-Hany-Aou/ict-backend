<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    /**
     * Submit a payment for approval
     */
    public function submitPayment(Request $request)
    {
        // Enhanced debug logging to catch PHP upload errors
        $rawFiles = $_FILES['screenshot'] ?? null;
        \Log::info('Payment submission attempt (start)', [
            'has_file' => $request->hasFile('screenshot'),
            'content_length' => $request->server('CONTENT_LENGTH'),
            'raw_files_error' => $rawFiles['error'] ?? 'no file in $_FILES',
            'raw_files_size' => $rawFiles['size'] ?? 0,
            'raw_files_name' => $rawFiles['name'] ?? null,
            'php_limits' => [
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
                'max_file_uploads' => ini_get('max_file_uploads'),
            ],
            'headers' => [
                'content-type' => $request->header('content-type'),
            ],
        ]);

        // Check for PHP upload errors even if Laravel doesn't detect the file
        if ($rawFiles && isset($rawFiles['error']) && $rawFiles['error'] !== UPLOAD_ERR_OK) {
            $code = $rawFiles['error'];
            \Log::error('PHP upload error detected', [
                'error_code' => $code,
                'file_size_sent' => $rawFiles['size'] ?? 0,
            ]);

            $messages = [
                UPLOAD_ERR_INI_SIZE => 'File size exceeds upload_max_filesize (' . ini_get('upload_max_filesize') . '). Please increase PHP limits.',
                UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive.',
                UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded. Please try again.',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder on server.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
            ];

            return response()->json([
                'success' => false,
                'message' => 'File upload failed',
                'errors' => ['screenshot' => [$messages[$code] ?? "Upload error code: {$code}"]],
                'debug' => [
                    'error_code' => $code,
                    'upload_max_filesize' => ini_get('upload_max_filesize'),
                    'post_max_size' => ini_get('post_max_size'),
                    'file_size_received' => $rawFiles['size'] ?? 0,
                ]
            ], 422);
        }

        // If file present, make sure PHP reports it as a valid uploaded file
        if ($request->hasFile('screenshot')) {
            $file = $request->file('screenshot');

            if (! $file->isValid()) {
                $code = $file->getError();
                \Log::error('Screenshot invalid upload', ['code' => $code, 'php_ini_upload_max_filesize' => ini_get('upload_max_filesize'), 'post_max_size' => ini_get('post_max_size')]);

                $messages = [
                    UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize.',
                    UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
                    UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
                    UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder on server.',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                    UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
                ];

                return response()->json([
                    'success' => false,
                    'message' => 'Screenshot upload failed',
                    'errors' => ['screenshot' => [$messages[$code] ?? "Upload error code: {$code}"]],
                ], 422);
            }
        }

        // Use 'sometimes|file' so validation only runs when a file is present and surfaces upload problems
        $validator = Validator::make($request->all(), [
            'payment_reference' => 'required|string|max:255',
           // 'amount' => 'required|numeric|min:0',
            'screenshot' => 'required|nullable|file|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ], [
            'screenshot.image' => 'Screenshot must be an image file',
            'screenshot.max' => 'Screenshot size must not exceed 10MB',
        ]);

        if ($validator->fails()) {
            \Log::error('Payment validation failed', [
                'errors' => $validator->errors()->toArray(),
                'has_file' => $request->hasFile('screenshot'),
                'files' => $request->allFiles(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $screenshotPath = null;
            if ($request->hasFile('screenshot')) {
                $screenshotPath = $request->file('screenshot')->store('payment_screenshots', 'public');
                \Log::info('Screenshot stored successfully', ['path' => $screenshotPath]);
            }

            // Create payment record
            $payment = Payment::create([
                'user_id' => auth()->id(),
                'payment_reference' => $request->payment_reference,
                'amount' => $request->amount,
                'screenshot_path' => $screenshotPath,
                'status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment submitted successfully. Waiting for admin approval.',
                'payment' => $payment
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Payment submission error', ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's premium status
     */
    public function getPremiumStatus(Request $request)
    {
        $user = auth()->user();

        return response()->json([
            'success' => true,
            'is_premium' => $user->isPremiumActive(),
            'premium_expires_at' => $user->premium_expires_at?->toDateTimeString(),
            'days_remaining' => $user->premium_expires_at ?
                now()->diffInDays($user->premium_expires_at, false) : null
        ]);
    }

    /**
     * Get user's payment history
     */
    public function getPaymentHistory(Request $request)
    {
        $payments = auth()->user()->payments()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'payments' => $payments
        ]);
    }

    /**
     * Get pending payment status
     */
    public function getPendingPayment(Request $request)
    {
        $pendingPayment = auth()->user()->payments()
            ->where('status', 'pending')
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'has_pending' => $pendingPayment !== null,
            'pending_payment' => $pendingPayment
        ]);
    }
}
