<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'key' => 'solo', 'name' => 'Solo Lawyer', 'seat_limit' => 1, 'storage_limit_mb' => 2048,
                'price_monthly' => 49, 'features' => ['clients', 'cases', 'documents', 'tasks', 'calendar'],
            ],
            [
                'key' => 'professional', 'name' => 'Professional Firm', 'seat_limit' => 15, 'storage_limit_mb' => 20480,
                'price_monthly' => 149, 'features' => ['clients', 'cases', 'documents', 'tasks', 'calendar', 'billing', 'reporting', 'client_portal'],
            ],
            [
                'key' => 'enterprise', 'name' => 'Enterprise Firm', 'seat_limit' => 250, 'storage_limit_mb' => 512000,
                'price_monthly' => 499, 'features' => ['clients', 'cases', 'documents', 'tasks', 'calendar', 'billing', 'reporting', 'client_portal', 'priority_support', 'sso'],
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(['key' => $plan['key']], $plan);
        }
    }
}
