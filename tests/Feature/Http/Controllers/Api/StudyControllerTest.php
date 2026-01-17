<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\User;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class StudyControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_start_creates_new_study_session(): void
    {
        $user = new User([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->save();
        $user->settings()->apply([
            'paginate' => 20,
            'fresh_first' => true,
            'show_starred' => true,
            'latest_first' => false,
            'known_enabled' => false, // Показывать только невыученные слова
            'main_language' => 'RU',
            'show_imported' => true,
            'languages_list' => ['DE', 'EN'],
            'starred_enabled' => false,
            'default_language' => 'DE',
        ]);

        Word::factory()->count(5)->create([
            'user_id' => $user->id,
            'language' => 'DE',
            'done_at' => null,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.v1.words.start', [
            'limit' => 3,
            'reverse' => false,
        ]));

        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/vnd.api+json')
            ->assertJsonStructure([
                'data' => [
                    'type',
                    'id',
                    'attributes' => [
                        'original',
                        'translated',
                        'language',
                        'done_at',
                        'starred',
                        'views',
                        'from_sample',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'meta' => [
                    'total',
                    'next_id',
                    'prev_id',
                    'current_index',
                ],
            ])
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.current_index', 1)
            ->assertJsonPath('meta.prev_id', null);

        $this->assertNotNull($response->json('meta.next_id'));
    }

    public function test_start_returns_existing_session_if_already_started(): void
    {
        $user = new User([
            'name' => 'Test User',
            'email' => 'test2@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->save();
        $user->settings()->apply([
            'paginate' => 20,
            'fresh_first' => true,
            'show_starred' => true,
            'latest_first' => false,
            'known_enabled' => false,
            'main_language' => 'RU',
            'show_imported' => true,
            'languages_list' => ['DE', 'EN'],
            'starred_enabled' => false,
            'default_language' => 'DE',
        ]);

        $words = Word::factory()->count(3)->create([
            'user_id' => $user->id,
            'language' => 'DE',
            'done_at' => null,
        ]);

        // Создаем сессию вручную
        /** @var array<int> $wordIds */
        $wordIds = $words->pluck('id')->toArray();
        \Illuminate\Support\Facades\Cache::put("words.start.{$user->id}", $wordIds);
        \Illuminate\Support\Facades\Cache::put("words.current.{$user->id}", $wordIds[0]);
        \Illuminate\Support\Facades\Cache::put("words.next.{$user->id}", $wordIds[1]);

        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.v1.words.start'));

        $response->assertStatus(200)
            ->assertJsonPath('data.id', (string) $wordIds[0])
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.current_index', 1)
            ->assertJsonPath('meta.next_id', $wordIds[1]);
    }

    public function test_start_fails_without_authentication(): void
    {
        $response = $this->getJson(route('api.v1.words.start'));

        $response->assertStatus(401);
    }

    public function test_start_fails_when_no_words_available(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.v1.words.start'));

        $response->assertStatus(400)
            ->assertHeader('content-type', 'application/vnd.api+json')
            ->assertJsonPath('errors.0.status', '400')
            ->assertJsonPath('errors.0.title', 'Bad Request')
            ->assertJsonPath('errors.0.detail', 'No words available for study session');
    }

    public function test_start_uses_default_limit_when_not_specified(): void
    {
        $user = new User([
            'name' => 'Test User',
            'email' => 'test3@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->save();
        $user->settings()->apply([
            'paginate' => 20,
            'fresh_first' => true,
            'show_starred' => true,
            'latest_first' => false,
            'known_enabled' => false,
            'main_language' => 'RU',
            'show_imported' => true,
            'languages_list' => ['DE', 'EN'],
            'starred_enabled' => false,
            'default_language' => 'DE',
        ]);

        Word::factory()->count(25)->create([
            'user_id' => $user->id,
            'language' => 'DE',
            'done_at' => null,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.v1.words.start'));

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 20);
    }

    public function test_start_validates_limit_parameter(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.v1.words.start', [
            'limit' => 'invalid',
        ]));

        $response->assertStatus(422)
            ->assertHeader('content-type', 'application/vnd.api+json');

        $response = $this->getJson(route('api.v1.words.start', [
            'limit' => 0,
        ]));

        $response->assertStatus(422);

        $response = $this->getJson(route('api.v1.words.start', [
            'limit' => 101,
        ]));

        $response->assertStatus(422);
    }

    public function test_start_validates_reverse_parameter(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.v1.words.start', [
            'reverse' => 'invalid',
        ]));

        $response->assertStatus(422)
            ->assertHeader('content-type', 'application/vnd.api+json');
    }

    public function test_start_respects_reverse_parameter(): void
    {
        $user = new User([
            'name' => 'Test User',
            'email' => 'test4@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->save();
        $user->settings()->apply([
            'paginate' => 20,
            'fresh_first' => true,
            'show_starred' => true,
            'latest_first' => false,
            'known_enabled' => false,
            'main_language' => 'RU',
            'show_imported' => true,
            'languages_list' => ['DE', 'EN'],
            'starred_enabled' => false,
            'default_language' => 'DE',
        ]);

        Word::factory()->count(5)->create([
            'user_id' => $user->id,
            'language' => 'DE',
            'done_at' => null,
        ]);

        Sanctum::actingAs($user);

        // Создаем сессию с reverse=true
        $response = $this->getJson(route('api.v1.words.start', [
            'limit' => 5,
            'reverse' => true,
        ]));

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 5);

        // Проверяем, что кэш содержит слова
        /** @var mixed $startCache */
        $startCache = \Illuminate\Support\Facades\Cache::get("words.start.{$user->id}");
        $this->assertIsArray($startCache);
        $this->assertCount(5, $startCache);
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
            'languages_list' => ['DE', 'EN'],
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

        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.v1.words.start', [
            'limit' => 1,
            'reverse' => true,
        ]));

        $response->assertStatus(200)
            ->assertJsonPath('data.id', (string) $word->id)
            // Проверяем, что original и translated поменялись местами
            ->assertJsonPath('data.attributes.original', 'привет')
            ->assertJsonPath('data.attributes.translated', 'hello');
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
            'languages_list' => ['DE', 'EN'],
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

        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.v1.words.start', [
            'limit' => 1,
            'reverse' => false,
        ]));

        $response->assertStatus(200)
            ->assertJsonPath('data.id', (string) $word->id)
            // Проверяем, что original и translated на своих местах
            ->assertJsonPath('data.attributes.original', 'hello')
            ->assertJsonPath('data.attributes.translated', 'привет');
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
            'languages_list' => ['DE', 'EN'],
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
        \Illuminate\Support\Facades\Cache::put("words.start.{$user->id}", $wordIds);
        \Illuminate\Support\Facades\Cache::put("words.current.{$user->id}", $wordIds[0]);
        \Illuminate\Support\Facades\Cache::put("words.next.{$user->id}", null);
        \Illuminate\Support\Facades\Cache::put("words.prev.{$user->id}", null);

        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.v1.words.start', [
            'limit' => 1,
            'reverse' => true,
        ]));

        $response->assertStatus(200)
            ->assertJsonPath('data.id', (string) $word->id)
            // Проверяем, что reverse работает даже с существующей сессией
            ->assertJsonPath('data.attributes.original', 'привет')
            ->assertJsonPath('data.attributes.translated', 'hello');
    }
}
