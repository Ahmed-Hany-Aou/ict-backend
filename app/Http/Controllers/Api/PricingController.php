<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class PricingController extends Controller
{
    /**
     * Get current premium pricing information
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $pricing = config('pricing.premium');

        return response()->json([
            'success' => true,
            'data' => [
                'currency' => config('pricing.currency'),
                'currency_symbol' => config('pricing.currency_symbol'),
                'original_price' => $pricing['original_price'],
                'discounted_price' => $pricing['discounted_price'],
                'discount_percentage' => $pricing['discount_percentage'],
                'duration_days' => $pricing['duration_days'],
                'description' => $pricing['description'],
                'formatted' => [
                    'original_price' => config('pricing.currency_symbol') . ' ' . number_format($pricing['original_price'], 0),
                    'discounted_price' => config('pricing.currency_symbol') . ' ' . number_format($pricing['discounted_price'], 0),
                ]
            ]
        ]);
    }
}
