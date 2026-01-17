<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Тесты для WordActionController.
 *
 * Проверяет все действия с словами: удаление, отметка как выученное,
 * переключение избранного, шаринг.
 */
final class WordActionControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /**
     * Проверяет отметку слова как выученное.
     */
    public function test_mark_learned_sets_done_at(): void
    {
        $word = Word::factory()->for($this->user)->create(['done_at' => null]);

        $response = $this
            ->actingAs($this->user)
            ->post(route('words.mark-learned', ['id' => $word->id]));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('words', [
            'id' => $word->id,
            'done_at' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Проверяет удаление слова.
     */
    public function test_delete_removes_word(): void
    {
        $word = Word::factory()->for($this->user)->create();

        $response = $this
            ->actingAs($this->user)
            ->delete(route('words.delete', ['id' => $word->id]));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('words', ['id' => $word->id]);
    }

    /**
     * Проверяет переключение избранного (добавление).
     */
    public function test_toggle_starred_adds_to_favorites(): void
    {
        $word = Word::factory()->for($this->user)->create(['starred' => false]);

        $response = $this
            ->actingAs($this->user)
            ->post(route('words.toggle-starred', ['id' => $word->id]));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('words', [
            'id' => $word->id,
            'starred' => true,
        ]);
    }

    /**
     * Проверяет переключение избранного (удаление).
     */
    public function test_toggle_starred_removes_from_favorites(): void
    {
        $word = Word::factory()->for($this->user)->create(['starred' => true]);

        $response = $this
            ->actingAs($this->user)
            ->post(route('words.toggle-starred', ['id' => $word->id]));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('words', [
            'id' => $word->id,
            'starred' => false,
        ]);
    }

    /**
     * Проверяет шаринг слова с другим пользователем.
     */
    public function test_share_creates_copy_for_target_user(): void
    {
        $targetUser = User::factory()->create();
        $word = Word::factory()->for($this->user)->create([
            'original' => 'test',
            'translated' => 'тест',
            'language' => 'en',
        ]);

        $response = $this
            ->actingAs($this->user)
            ->post(route('words.share', ['id' => $word->id]), [
                'user_id' => $targetUser->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('words', [
            'user_id' => $targetUser->id,
            'original' => 'test',
            'translated' => 'тест',
            'language' => 'en',
            'shared_by' => $this->user->id,
        ]);
    }

    /**
     * Проверяет, что шаринг требует существующего пользователя.
     */
    public function test_share_requires_valid_user_id(): void
    {
        $word = Word::factory()->for($this->user)->create();

        $response = $this
            ->actingAs($this->user)
            ->post(route('words.share', ['id' => $word->id]), [
                'user_id' => 99999,
            ]);

        $response->assertSessionHasErrors('user_id');
    }

    /**
     * Проверяет, что пользователь не может удалять слова другого пользователя.
     */
    public function test_delete_returns_error_for_other_users_word(): void
    {
        $otherUser = User::factory()->create();
        $word = Word::factory()->for($otherUser)->create();

        $response = $this
            ->actingAs($this->user)
            ->delete(route('words.delete', ['id' => $word->id]));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('words', ['id' => $word->id]);
    }

    /**
     * Проверяет, что все действия требуют аутентификации.
     */
    public function test_all_actions_require_authentication(): void
    {
        $word = Word::factory()->for($this->user)->create();

        $this->post(route('words.mark-learned', ['id' => $word->id]))
            ->assertRedirect(route('login'));

        $this->delete(route('words.delete', ['id' => $word->id]))
            ->assertRedirect(route('login'));

        $this->post(route('words.toggle-starred', ['id' => $word->id]))
            ->assertRedirect(route('login'));
    }
}
