<?php

namespace Database\Factories;

use App\Models\Firm;
use Illuminate\Database\Eloquent\Factories\Factory;

class FirmFactory extends Factory
{
    protected $model = Firm::class;

    public function definition(): array
    {
        $plan = $this->faker->randomElement(['solo', 'professional', 'enterprise']);
        $limits = ['solo' => 1, 'professional' => 15, 'enterprise' => 250];

        return [
            'name' => $this->faker->company().' Law Firm',
            'email' => $this->faker->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address_line_1' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->stateAbbr(),
            'postal_code' => $this->faker->postcode(),
            'country' => 'US',
            'plan' => $plan,
            'seat_limit' => $limits[$plan],
            'storage_limit_mb' => 2048,
            'status' => 'active',
            'trial_ends_at' => now()->addDays(14),
        ];
    }
}
