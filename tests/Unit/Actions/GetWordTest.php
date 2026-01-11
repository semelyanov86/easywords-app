<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\GetWord;
use App\Models\User;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;

final class GetWordTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_word_when_user_is_owner(): void
    {
        $user = User::factory()->create();
        $word = Word::factory()->create([
            'user_id' => $user->id,
            'original' => 'TestWord',
        ]);

        $result = GetWord::make()->handle(
            wordId: $word->id,
            userId: $user->id
        );

        $this->assertEquals($word->id, $result->id);
        $this->assertEquals('TestWord', $result->original);
    }

    public function test_throws_exception_when_word_does_not_exist(): void
    {
        $user = User::factory()->create();

        $this->expectException(ModelNotFoundException::class);

        GetWord::make()->handle(
            wordId: 999,
            userId: $user->id
        );
    }

    public function test_throws_exception_when_word_belongs_to_other_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $word = Word::factory()->create([
            'user_id' => $user1->id,
        ]);

        $this->expectException(ModelNotFoundException::class);

        GetWord::make()->handle(
            wordId: $word->id,
            userId: $user2->id
        );
    }

    public function test_returns_word_with_correct_fields(): void
    {
        $user = User::factory()->create();
        $word = Word::factory()->create([
            'user_id' => $user->id,
            'original' => 'Hallo',
            'translated' => 'Привет',
            'language' => 'DE',
            'views' => 5,
            'starred' => true,
        ]);

        $result = GetWord::make()->handle(
            wordId: $word->id,
            userId: $user->id
        );

        $this->assertEquals($word->id, $result->id);
        $this->assertEquals('Hallo', $result->original);
        $this->assertEquals('Привет', $result->translated);
        $this->assertEquals('DE', $result->language);
        $this->assertEquals(5, $result->views);
        $this->assertTrue($result->starred);
    }
}
