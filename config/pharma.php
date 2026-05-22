<?php

return [
    'app_name' => env('PHARMA_APP_NAME', 'MedRep Fleet'),
    'currency' => env('PHARMA_CURRENCY', 'USD'),
    'price_per_user_usd' => (float) env('PHARMA_PRICE_PER_USER', 3),
    'subscription_days' => (int) env('PHARMA_SUBSCRIPTION_DAYS', 30),

    'leave_allowances' => [
        'casual' => (int) env('LEAVE_CASUAL_DAYS', 12),
        'sick' => (int) env('LEAVE_SICK_DAYS', 10),
        'annual' => (int) env('LEAVE_ANNUAL_DAYS', 15),
        'emergency' => (int) env('LEAVE_EMERGENCY_DAYS', 3),
    ],
];
