<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\ShareWord;
use App\Models\User;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ShareWordTest extends TestCase
{
    use RefreshDatabase;

    public function test_shares_word_with_another_user(): void
    {
        $author = User::factory()->create();
        $targetUser = User::factory()->create();
        $action = new ShareWord();

        $originalWord = Word::factory()->create([
            'user_id' => $author->id,
            'original' => 'test',
            'translated' => 'тест',
            'language' => 'EN',
            'starred' => true,
            'views' => 5,
            'from_sample' => true,
            'shared_by' => null,
        ]);

        $sharedWord = $action->handle(
            word: $originalWord,
            targetUser: $targetUser,
            author: $author
        );

        $this->assertDatabaseHas(Word::class, [
            'user_id' => $targetUser->id,
            'original' => 'test',
            'translated' => 'тест',
            'language' => 'EN',
            'starred' => false,
            'views' => 0,
            'from_sample' => false,
            'shared_by' => $author->id,
        ]);

        $this->assertEquals('test', $sharedWord->original);
        $this->assertEquals('тест', $sharedWord->translated);
        $this->assertEquals('EN', $sharedWord->language);
        $this->assertEquals($targetUser->id, $sharedWord->user_id);
        $this->assertEquals($author->id, $sharedWord->shared_by);
        $this->assertFalse($sharedWord->starred);
        $this->assertEquals(0, $sharedWord->views);
        $this->assertFalse($sharedWord->from_sample);
    }

    public function test_preserves_original_word(): void
    {
        $author = User::factory()->create();
        $targetUser = User::factory()->create();
        $action = new ShareWord();

        $originalWord = Word::factory()->create([
            'user_id' => $author->id,
            'original' => 'original',
            'translated' => 'оригинал',
            'language' => 'EN',
        ]);

        $action->handle(
            word: $originalWord,
            targetUser: $targetUser,
            author: $author
        );

        // Оригинальное слово не должно измениться
        $originalWord->refresh();
        $this->assertEquals($author->id, $originalWord->user_id);
        $this->assertEquals('original', $originalWord->original);
        $this->assertEquals('оригинал', $originalWord->translated);
        $this->assertEquals('EN', $originalWord->language);
        $this->assertNull($originalWord->shared_by);
    }
}
