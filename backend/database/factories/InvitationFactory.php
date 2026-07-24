<?php

namespace Database\Factories;

use App\Models\Firm;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InvitationFactory extends Factory
{
    protected $model = Invitation::class;

    public function definition(): array
    {
        return [
            'firm_id' => Firm::factory(),
            'invited_by' => User::factory()->firmOwner(),
            'email' => $this->faker->unique()->safeEmail(),
            'role' => 'lawyer',
            'token' => Str::random(64),
            'expires_at' => now()->addDays(7),
        ];
    }
}
