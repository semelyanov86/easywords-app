<?php

declare(strict_types=1);

namespace Tests\Feature\Actions;

use App\Actions\StartStudySession;
use App\Models\User;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class StartStudySessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_starts_new_study_session(): void
    {
        $user = User::factory()->create();
        $user->settings()->apply([
            'paginate' => 20,
            'fresh_first' => true,
            'show_starred' => true,
            'latest_first' => false,
            'known_enabled' => false,
            'main_language' => 'RU',
            'show_imported' => true,
            'languages_list' => ['EN', 'DE'],
            'starred_enabled' => false,
            'default_language' => 'EN',
        ]);

        Word::factory()->for($user)->count(5)->create([
            'language' => 'EN',
            'done_at' => null,
        ]);

        $action = resolve(StartStudySession::class);

        $result = $action->handle(userId: $user->id, language: 'EN', limit: 3, reverse: false);

        $this->assertArrayHasKey('word', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('next_id', $result);
        $this->assertArrayHasKey('prev_id', $result);
        $this->assertArrayHasKey('current_index', $result);
        $this->assertEquals(3, $result['total']);
        $this->assertEquals(1, $result['current_index']);
        $this->assertNotNull($result['next_id']);
        $this->assertNull($result['prev_id']);

        $this->assertIsArray(Cache::get("words.start.EN.{$user->id}"));
        $this->assertCount(3, Cache::get("words.start.EN.{$user->id}"));
        $this->assertIsInt(Cache::get("words.current.EN.{$user->id}"));
        $this->assertIsInt(Cache::get("words.next.EN.{$user->id}"));
        $this->assertNull(Cache::get("words.prev.EN.{$user->id}"));
    }

    public function test_returns_existing_session_if_already_started(): void
    {
        $user = User::factory()->create();
        $user->settings()->apply([
            'paginate' => 20,
            'fresh_first' => true,
            'show_starred' => true,
            'latest_first' => false,
            'known_enabled' => false,
            'main_language' => 'RU',
            'show_imported' => true,
            'languages_list' => ['EN', 'DE'],
            'starred_enabled' => false,
            'default_language' => 'EN',
        ]);

        $words = Word::factory()->for($user)->count(5)->create([
            'language' => 'EN',
            'done_at' => null,
        ]);

        $wordIds = $words->take(3)->pluck('id')->toArray();
        Cache::put("words.start.EN.{$user->id}", $wordIds);
        Cache::put("words.current.EN.{$user->id}", $wordIds[0]);
        Cache::put("words.next.EN.{$user->id}", $wordIds[1]);
        Cache::put("words.prev.EN.{$user->id}", null);

        $action = resolve(StartStudySession::class);

        $result = $action->handle(userId: $user->id, language: 'EN', limit: 3, reverse: false);

        $this->assertEquals($wordIds[0], $result['word']->id);
        $this->assertEquals(3, $result['total']);
        $this->assertEquals(1, $result['current_index']);
        $this->assertEquals($wordIds[1], $result['next_id']);
        $this->assertNull($result['prev_id']);
    }

    public function test_throws_exception_when_no_words_available(): void
    {
        $user = User::factory()->create();
        $user->settings()->apply([
            'paginate' => 20,
            'fresh_first' => true,
            'show_starred' => true,
            'latest_first' => false,
            'known_enabled' => false,
            'main_language' => 'RU',
            'show_imported' => true,
            'languages_list' => ['EN', 'DE'],
            'starred_enabled' => false,
            'default_language' => 'EN',
        ]);

        $action = resolve(StartStudySession::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No words available for study session');

        $action->handle(userId: $user->id, language: 'EN', limit: 5, reverse: false);
    }

    public function test_reverse_swaps_original_and_translated(): void
    {
        $user = User::factory()->create();
        $user->settings()->apply([
            'paginate' => 20,
            'fresh_first' => true,
            'show_starred' => true,
            'latest_first' => false,
            'known_enabled' => false,
            'main_language' => 'RU',
            'show_imported' => true,
            'languages_list' => ['EN', 'DE'],
            'starred_enabled' => false,
            'default_language' => 'EN',
        ]);

        $word = Word::factory()->create([
            'user_id' => $user->id,
            'language' => 'EN',
            'original' => 'hello',
            'translated' => 'привет',
            'done_at' => null,
        ]);

        $action = resolve(StartStudySession::class);

        $result = $action->handle(userId: $user->id, language: 'EN', limit: 1, reverse: true);

        // Проверяем, что original и translated поменялись местами
        $this->assertEquals('привет', $result['word']->original);
        $this->assertEquals('hello', $result['word']->translated);
    }

    public function test_reverse_false_keeps_original_and_translated(): void
    {
        $user = User::factory()->create();
        $user->settings()->apply([
            'paginate' => 20,
            'fresh_first' => true,
            'show_starred' => true,
            'latest_first' => false,
            'known_enabled' => false,
            'main_language' => 'RU',
            'show_imported' => true,
            'languages_list' => ['EN', 'DE'],
            'starred_enabled' => false,
            'default_language' => 'EN',
        ]);

        $word = Word::factory()->create([
            'user_id' => $user->id,
            'language' => 'EN',
            'original' => 'hello',
            'translated' => 'привет',
            'done_at' => null,
        ]);

        $action = resolve(StartStudySession::class);

        $result = $action->handle(userId: $user->id, language: 'EN', limit: 1, reverse: false);

        // Проверяем, что original и translated на своих местах
        $this->assertEquals('hello', $result['word']->original);
        $this->assertEquals('привет', $result['word']->translated);
    }

    public function test_reverse_works_with_existing_session(): void
    {
        $user = User::factory()->create();
        $user->settings()->apply([
            'paginate' => 20,
            'fresh_first' => true,
            'show_starred' => true,
            'latest_first' => false,
            'known_enabled' => false,
            'main_language' => 'RU',
            'show_imported' => true,
            'languages_list' => ['EN', 'DE'],
            'starred_enabled' => false,
            'default_language' => 'EN',
        ]);

        $word = Word::factory()->create([
            'user_id' => $user->id,
            'language' => 'EN',
            'original' => 'hello',
            'translated' => 'привет',
            'done_at' => null,
        ]);

        // Создаем сессию
        $wordIds = [$word->id];
        Cache::put("words.start.EN.{$user->id}", $wordIds);
        Cache::put("words.current.EN.{$user->id}", $wordIds[0]);
        Cache::put("words.next.EN.{$user->id}", null);
        Cache::put("words.prev.EN.{$user->id}", null);

        $action = resolve(StartStudySession::class);

        $result = $action->handle(userId: $user->id, language: 'EN', limit: 1, reverse: true);

        // Проверяем, что reverse работает даже с существующей сессией
        $this->assertEquals('привет', $result['word']->original);
        $this->assertEquals('hello', $result['word']->translated);
    }
}
