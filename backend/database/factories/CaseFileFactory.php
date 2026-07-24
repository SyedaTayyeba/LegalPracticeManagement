<?php

namespace Database\Factories;

use App\Models\CaseFile;
use App\Models\Client;
use App\Models\Firm;
use Illuminate\Database\Eloquent\Factories\Factory;

class CaseFileFactory extends Factory
{
    protected $model = CaseFile::class;

    private static array $sequenceByFirm = [];

    public function definition(): array
    {
        $firm = Firm::factory();

        return [
            'firm_id' => $firm,
            'case_number' => now()->year.'-'.str_pad((string) $this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'title' => $this->faker->sentence(4),
            'case_type' => $this->faker->randomElement(['Litigation', 'Family Law', 'Estate Planning', 'Corporate', 'Criminal Defense']),
            'client_id' => Client::factory(),
            'opposing_party' => $this->faker->company(),
            'status' => 'new',
            'opened_on' => now()->toDateString(),
        ];
    }

    public function forFirm(Firm $firm): static
    {
        return $this->state(fn () => ['firm_id' => $firm->id]);
    }

    public function status(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
