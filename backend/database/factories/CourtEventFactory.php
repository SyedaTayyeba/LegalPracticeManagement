<?php

namespace Database\Factories;

use App\Models\CourtEvent;
use App\Models\Firm;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourtEventFactory extends Factory
{
    protected $model = CourtEvent::class;

    public function definition(): array
    {
        return [
            'firm_id' => Firm::factory(),
            'title' => $this->faker->sentence(3),
            'event_type' => 'hearing',
            'starts_at' => now()->addDays($this->faker->numberBetween(1, 20))->setTime(10, 0),
        ];
    }
}
