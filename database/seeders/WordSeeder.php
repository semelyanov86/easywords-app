<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Word;
use Illuminate\Database\Seeder;

final class WordSeeder extends Seeder
{
    public function run(): void
    {
        Word::factory()
            ->count(20)
            ->create();
    }
}
