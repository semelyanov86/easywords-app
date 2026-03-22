<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\ToggleWordStarred;
use App\Models\User;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class ToggleWordStarredTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Проверяет, что starred переключается на true.
     */
    public function test_toggles_starred_to_true(): void
    {
        $user = User::factory()->create();
        $word = Word::factory()->create([
            'user_id' => $user->id,
            'starred' => false,
        ]);

        $action = new ToggleWordStarred();

        $result = $action->handle($word->id, $user->id);

        $this->assertTrue($result);
        $this->assertDatabaseHas(Word::class, [
            'id' => $word->id,
            'starred' => true,
        ]);
    }

    /**
     * Проверяет, что starred переключается на false.
     */
    public function test_toggles_starred_to_false(): void
    {
        $user = User::factory()->create();
        $word = Word::factory()->create([
            'user_id' => $user->id,
            'starred' => true,
        ]);

        $action = new ToggleWordStarred();

        $result = $action->handle($word->id, $user->id);

        $this->assertFalse($result);
        $this->assertDatabaseHas(Word::class, [
            'id' => $word->id,
            'starred' => false,
        ]);
    }

    /**
     * Проверяет, что выбрасывается исключение, если слово не найдено.
     */
    public function test_throws_exception_if_word_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = User::factory()->create();
        $action = new ToggleWordStarred();

        $action->handle(999, $user->id);
    }

    /**
     * Проверяет, что выбрасывается исключение, если слово принадлежит другому пользователю.
     */
    public function test_throws_exception_if_word_belongs_to_different_user(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $word = Word::factory()->create([
            'user_id' => $user1->id,
            'starred' => false,
        ]);

        $action = new ToggleWordStarred();

        $action->handle($word->id, $user2->id);
    }
}
