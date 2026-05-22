<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::updateOrCreate(
            ['user_limit' => 1],
            [
                'name' => 'Free',
                'price_usd' => 0,
                'description' => '1 medical representative — no payment required.',
                'is_active' => true,
            ]
        );

        $pricePerUser = (float) config('pharma.price_per_user_usd', 3);

        foreach ([10, 20, 30, 40, 50, 60, 70, 80, 90, 100] as $limit) {
            $price = round($limit * $pricePerUser, 2);

            Plan::updateOrCreate(
                ['user_limit' => $limit],
                [
                    'name' => "Up to {$limit} Users",
                    'price_usd' => $price,
                    'description' => "Manage up to {$limit} medical representatives with AI-powered tracking.",
                    'is_active' => true,
                ]
            );
        }
    }
}
