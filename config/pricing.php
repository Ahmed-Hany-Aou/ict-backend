<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Premium Subscription Pricing
    |--------------------------------------------------------------------------
    |
    | Configure premium subscription pricing, discounts, and duration.
    | All prices are in Egyptian Pounds (EGP).
    |
    */

    'currency' => 'EGP',
    'currency_symbol' => 'EGP',

    'premium' => [
        'original_price' => 500,
        'discounted_price' => 300,
        'discount_percentage' => 40,
        'duration_days' => 30,
        'description' => 'Get full access to all premium content for 30 days',
    ],

    /*
    |--------------------------------------------------------------------------
    | Price Validation
    |--------------------------------------------------------------------------
    |
    | Enable strict price validation for payment submissions.
    |
    */

    'strict_validation' => true,
    'allowed_variance' => 0, // Allow 0 EGP variance for exact match
];
