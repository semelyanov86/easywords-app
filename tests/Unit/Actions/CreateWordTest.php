<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\CreateWord;
use App\Models\User;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CreateWordTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_word_for_user(): void
    {
        $user = User::factory()->create();
        $action = new CreateWord();

        $wordData = $action->handle(
            userId: $user->id,
            original: 'hello',
            translated: 'привет',
            language: 'en'
        );

        $this->assertDatabaseHas(Word::class, [
            'user_id' => $user->id,
            'original' => 'hello',
            'translated' => 'привет',
            'language' => 'EN',
            'views' => 0,
            'starred' => false,
            'from_sample' => false,
        ]);

        $this->assertEquals('hello', $wordData->original);
        $this->assertEquals('привет', $wordData->translated);
        $this->assertEquals('EN', $wordData->language);
    }
}
