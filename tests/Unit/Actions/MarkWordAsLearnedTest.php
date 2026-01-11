<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\MarkWordAsLearned;
use App\Models\User;
use App\Models\Word;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MarkWordAsLearnedTest extends TestCase
{
    use RefreshDatabase;

    public function test_marks_word_as_learned_with_current_time(): void
    {
        $user = User::factory()->create();
        $word = Word::factory()->create([
            'user_id' => $user->id,
            'done_at' => null,
        ]);

        $action = new MarkWordAsLearned();

        $wordData = $action->handle(
            wordId: $word->id,
            userId: $user->id
        );

        $this->assertNotNull($wordData->done_at);
        $this->assertInstanceOf(CarbonImmutable::class, $wordData->done_at);

        $this->assertDatabaseHas(Word::class, [
            'id' => $word->id,
            'user_id' => $user->id,
        ]);
        $this->assertNotNull(Word::find($word->id)?->done_at);
    }

    public function test_marks_word_as_learned_with_custom_time(): void
    {
        $user = User::factory()->create();
        $word = Word::factory()->create([
            'user_id' => $user->id,
            'done_at' => null,
        ]);

        $customTime = CarbonImmutable::create(2026, 1, 1, 12, 0, 0);

        $action = new MarkWordAsLearned();

        $wordData = $action->handle(
            wordId: $word->id,
            userId: $user->id,
            doneAt: $customTime
        );

        $this->assertEquals($customTime, $wordData->done_at);

        $this->assertDatabaseHas(Word::class, [
            'id' => $word->id,
            'user_id' => $user->id,
        ]);
        $this->assertEquals($customTime, Word::find($word->id)?->done_at);
    }

    public function test_throws_exception_if_word_not_found(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $user = User::factory()->create();
        $action = new MarkWordAsLearned();

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
            'done_at' => null,
        ]);

        $action = new MarkWordAsLearned();

        $action->handle(
            wordId: $word->id,
            userId: $user2->id
        );
    }
}
