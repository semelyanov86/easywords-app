<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Sample;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Sample>
 */
final class SampleFactory extends Factory
{
    protected $model = Sample::class;

    public function definition(): array
    {
        return [
            'original' => $this->faker->word(),
            'translated' => $this->faker->word(),
            'language' => $this->faker->randomElement(['EN', 'DE', 'FR', 'ES']),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
