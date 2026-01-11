<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Sample;
use Illuminate\Database\Seeder;

final class SampleSeeder extends Seeder
{
    public function run(): void
    {
        $languages = config('app.supported_languages');
        foreach ($languages as $language) {
            if (file_exists('./database/seeders/samples/' . $language . '.php')) {
                $samples = require './database/seeders/samples/' . $language . '.php';
                Sample::insert($samples);
            }
        }
    }
}
