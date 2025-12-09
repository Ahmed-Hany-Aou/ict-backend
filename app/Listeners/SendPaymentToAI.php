<?php

namespace App\Listeners;

use App\Events\PaymentSubmitted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendPaymentToAI implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'ai_processing';
    public $tries = 3;
    public $retryAfter = 60;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PaymentSubmitted $event): void
    {
        $payment = $event->payment;
        
        // Image Handling: Send as Multipart File
        $imagePath = \Illuminate\Support\Facades\Storage::disk('public')->path($payment->screenshot_path);

        // Send to n8n Webhook
        // Send metadata as query parameters for reliable parsing
        $queryParams = http_build_query([
            'payment_id' => $payment->id,
            'expected_amount' => config('pricing.premium.discounted_price'),
           //'expected_amount' => $payment->amount,
            'user_id' => $payment->user_id,
            'timestamp' => now()->toIso8601String()
        ]);

        $webhookUrl = config('services.n8n.webhook_url') . '?' . $queryParams;

        // Send to n8n Webhook with file as multipart and metadata as query params
        \Illuminate\Support\Facades\Http::timeout(30)
            ->attach('data', file_get_contents($imagePath), basename($payment->screenshot_path))
            // ->attach('file', file_get_contents($imagePath), basename($payment->screenshot_path)) causes binary data error 
            ->post($webhookUrl);
    }

    public function failed(\Throwable $exception)
    {
         \Illuminate\Support\Facades\Log::error("AI Processing Failed for Payment ID: " . $this->payment->id ?? 'unknown', ['error' => $exception->getMessage()]);
    }
}
