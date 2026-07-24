<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Firm;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        $first = $this->faker->firstName();
        $last = $this->faker->lastName();

        return [
            'firm_id' => Firm::factory(),
            'type' => 'individual',
            'first_name' => $first,
            'last_name' => $last,
            'display_name' => "{$first} {$last}",
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'city' => $this->faker->city(),
            'state' => $this->faker->stateAbbr(),
            'status' => 'active',
        ];
    }

    public function organization(): static
    {
        $name = $this->faker->company();

        return $this->state(fn () => [
            'type' => 'organization',
            'organization_name' => $name,
            'display_name' => $name,
            'first_name' => null,
            'last_name' => null,
        ]);
    }
}
