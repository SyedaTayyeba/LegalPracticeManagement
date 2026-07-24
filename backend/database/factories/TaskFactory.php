<?php

namespace Database\Factories;

use App\Models\Firm;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'firm_id' => Firm::factory(),
            'title' => $this->faker->sentence(4),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high', 'urgent']),
            'status' => 'pending',
            'due_date' => now()->addDays($this->faker->numberBetween(1, 30)),
        ];
    }
}
