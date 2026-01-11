<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\DeleteWord;
use App\Models\User;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;

final class DeleteWordTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_word_when_user_is_owner(): void
    {
        $user = User::factory()->create();
        $word = Word::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('words', [
            'id' => $word->id,
        ]);

        DeleteWord::make()->handle(
            wordId: $word->id,
            userId: $user->id
        );

        $this->assertDatabaseMissing('words', [
            'id' => $word->id,
        ]);
    }

    public function test_throws_exception_when_word_does_not_exist(): void
    {
        $user = User::factory()->create();

        $this->expectException(ModelNotFoundException::class);

        DeleteWord::make()->handle(
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

        DeleteWord::make()->handle(
            wordId: $word->id,
            userId: $user2->id
        );

        // Убеждаемся, что слово не было удалено
        $this->assertDatabaseHas('words', [
            'id' => $word->id,
        ]);
    }

    public function test_does_not_delete_other_users_words(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $word1 = Word::factory()->create([
            'user_id' => $user1->id,
        ]);

        $word2 = Word::factory()->create([
            'user_id' => $user2->id,
        ]);

        DeleteWord::make()->handle(
            wordId: $word1->id,
            userId: $user1->id
        );

        $this->assertDatabaseMissing('words', [
            'id' => $word1->id,
        ]);

        $this->assertDatabaseHas('words', [
            'id' => $word2->id,
        ]);
    }
}
