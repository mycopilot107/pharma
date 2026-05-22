<?php

return [
    'default' => env('PHARMA_CURRENCY', 'USD'),

    'supported' => [
        'USD' => ['name' => 'US Dollar', 'symbol' => '$', 'decimals' => 2],
        'INR' => ['name' => 'Indian Rupee', 'symbol' => '₹', 'decimals' => 2],
        'EUR' => ['name' => 'Euro', 'symbol' => '€', 'decimals' => 2],
        'GBP' => ['name' => 'British Pound', 'symbol' => '£', 'decimals' => 2],
        'AED' => ['name' => 'UAE Dirham', 'symbol' => 'د.إ', 'decimals' => 2],
        'SAR' => ['name' => 'Saudi Riyal', 'symbol' => '﷼', 'decimals' => 2],
        'AUD' => ['name' => 'Australian Dollar', 'symbol' => 'A$', 'decimals' => 2],
        'CAD' => ['name' => 'Canadian Dollar', 'symbol' => 'C$', 'decimals' => 2],
        'SGD' => ['name' => 'Singapore Dollar', 'symbol' => 'S$', 'decimals' => 2],
        'MYR' => ['name' => 'Malaysian Ringgit', 'symbol' => 'RM', 'decimals' => 2],
        'BDT' => ['name' => 'Bangladeshi Taka', 'symbol' => '৳', 'decimals' => 2],
        'PKR' => ['name' => 'Pakistani Rupee', 'symbol' => '₨', 'decimals' => 2],
        'NGN' => ['name' => 'Nigerian Naira', 'symbol' => '₦', 'decimals' => 2],
        'ZAR' => ['name' => 'South African Rand', 'symbol' => 'R', 'decimals' => 2],
    ],
];
