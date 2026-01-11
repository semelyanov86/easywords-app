<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\ToggleWordStarred;
use App\Models\User;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ToggleWordStarredTest extends TestCase
{
    use RefreshDatabase;

    public function test_sets_starred_to_true(): void
    {
        $user = User::factory()->create();
        $word = Word::factory()->create([
            'user_id' => $user->id,
            'starred' => false,
        ]);

        $action = new ToggleWordStarred();

        $wordData = $action->handle(
            wordId: $word->id,
            userId: $user->id,
            starred: true
        );

        $this->assertTrue($wordData->starred);
        $this->assertDatabaseHas(Word::class, [
            'id' => $word->id,
            'starred' => true,
        ]);
    }

    public function test_sets_starred_to_false(): void
    {
        $user = User::factory()->create();
        $word = Word::factory()->create([
            'user_id' => $user->id,
            'starred' => true,
        ]);

        $action = new ToggleWordStarred();

        $wordData = $action->handle(
            wordId: $word->id,
            userId: $user->id,
            starred: false
        );

        $this->assertFalse($wordData->starred);
        $this->assertDatabaseHas(Word::class, [
            'id' => $word->id,
            'starred' => false,
        ]);
    }

    public function test_throws_exception_if_word_not_found(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $user = User::factory()->create();
        $action = new ToggleWordStarred();

        $action->handle(
            wordId: 999,
            userId: $user->id,
            starred: true
        );
    }

    public function test_throws_exception_if_word_belongs_to_different_user(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $word = Word::factory()->create([
            'user_id' => $user1->id,
            'starred' => false,
        ]);

        $action = new ToggleWordStarred();

        $action->handle(
            wordId: $word->id,
            userId: $user2->id,
            starred: true
        );
    }
}
