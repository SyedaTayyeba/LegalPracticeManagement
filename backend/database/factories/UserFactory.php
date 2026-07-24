<?php

namespace Database\Factories;

use App\Models\Firm;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'firm_id' => Firm::factory(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('Password!123'),
            'role' => 'lawyer',
            'status' => 'active',
            'remember_token' => Str::random(10),
        ];
    }

    public function firmOwner(): static
    {
        return $this->state(fn () => ['role' => 'firm_owner']);
    }

    public function lawyer(): static
    {
        return $this->state(fn () => ['role' => 'lawyer']);
    }

    public function paralegal(): static
    {
        return $this->state(fn () => ['role' => 'paralegal']);
    }

    public function client(): static
    {
        return $this->state(fn () => ['role' => 'client']);
    }

    public function platformAdmin(): static
    {
        return $this->state(fn () => ['role' => 'platform_admin', 'firm_id' => null]);
    }
}
