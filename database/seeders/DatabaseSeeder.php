<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->createOne([
            'name' => 'Sergei Emelianov',
            'email' => 'se@sergeyem.ru',
            'is_admin' => true,
            'has_premium' => true,
        ]);
        $this->call(WordSeeder::class);
        $this->call(SampleSeeder::class);
    }
}
