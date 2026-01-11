<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\Word;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Word>
 */
final class WordFactory extends Factory
{
    protected $model = Word::class;

    public function definition(): array
    {
        $users = User::all();
        if ($users && $users->isNotEmpty()) {
            $userId = $users->random();
        } else {
            $userId = \App\Models\User::factory();
        }

        return [
            'original' => $this->faker->word(),
            'translated' => $this->faker->word(),
            'done_at' => Carbon::now(),
            'starred' => $this->faker->boolean(),
            'language' => $this->faker->randomElement(config('app.supported_languages')),
            'views' => $this->faker->randomNumber(),
            'example_original' => $this->faker->words(),
            'example_translated' => $this->faker->words(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'user_id' => $userId,
        ];
    }
}
