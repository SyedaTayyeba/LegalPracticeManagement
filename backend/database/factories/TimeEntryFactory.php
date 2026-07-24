<?php

namespace Database\Factories;

use App\Models\TimeEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimeEntryFactory extends Factory
{
    protected $model = TimeEntry::class;

    public function definition(): array
    {
        return [
            'description' => $this->faker->sentence(4),
            'minutes' => $this->faker->numberBetween(15, 240),
            'hourly_rate' => $this->faker->randomElement([250, 300, 350]),
            'billable' => true,
            'entry_date' => now()->toDateString(),
        ];
    }
}
