<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\Firm;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        return [
            'firm_id' => Firm::factory(),
            'category' => 'contract',
            'name' => $this->faker->words(3, true).'.pdf',
            'original_filename' => $this->faker->words(3, true).'.pdf',
            'disk' => 'local',
            'path' => 'firms/test/documents/'.$this->faker->uuid().'.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => $this->faker->numberBetween(1000, 500000),
            'version' => 1,
            'is_latest_version' => true,
        ];
    }
}
