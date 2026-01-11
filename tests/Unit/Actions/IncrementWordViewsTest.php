<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\IncrementWordViews;
use App\Models\User;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class IncrementWordViewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_increments_word_views(): void
    {
        $user = User::factory()->create();
        $word = Word::factory()->create([
            'user_id' => $user->id,
            'views' => 5,
        ]);

        $action = new IncrementWordViews();

        $wordData = $action->handle(
            wordId: $word->id,
            userId: $user->id
        );

        $this->assertEquals(6, $wordData->views);
        $this->assertDatabaseHas(Word::class, [
            'id' => $word->id,
            'views' => 6,
        ]);
    }

    public function test_throws_exception_if_word_not_found(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $user = User::factory()->create();
        $action = new IncrementWordViews();

        $action->handle(
            wordId: 999,
            userId: $user->id
        );
    }

    public function test_throws_exception_if_word_belongs_to_different_user(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $word = Word::factory()->create([
            'user_id' => $user1->id,
            'views' => 5,
        ]);

        $action = new IncrementWordViews();

        $action->handle(
            wordId: $word->id,
            userId: $user2->id
        );
    }
}
