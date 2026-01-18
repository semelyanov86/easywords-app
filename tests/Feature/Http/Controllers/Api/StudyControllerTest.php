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
            'limit' => 1,
            'reverse' => true,
            'language' => 'DE',
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
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('meta.current_index', 1)
            ->assertJsonPath('meta.prev_id', null);

        $this->assertNull($response->json('meta.next_id'));
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
        \Illuminate\Support\Facades\Cache::put("words.start.DE.{$user->id}", $wordIds);
        \Illuminate\Support\Facades\Cache::put("words.current.DE.{$user->id}", $wordIds[0]);
        \Illuminate\Support\Facades\Cache::put("words.next.DE.{$user->id}", $wordIds[1]);

        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.v1.words.start', [
            'language' => 'DE',
        ]));

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

        $response = $this->getJson(route('api.v1.words.start', [
            'language' => 'EN',
        ]));

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

        $response = $this->getJson(route('api.v1.words.start', [
            'language' => 'DE',
        ]));

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 20);
    }

    public function test_start_validates_limit_parameter(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.v1.words.start', [
            'limit' => 'invalid',
            'language' => 'EN',
        ]));

        $response->assertStatus(422)
            ->assertHeader('content-type', 'application/vnd.api+json');

        $response = $this->getJson(route('api.v1.words.start', [
            'limit' => 0,
            'language' => 'EN',
        ]));

        $response->assertStatus(422);

        $response = $this->getJson(route('api.v1.words.start', [
            'limit' => 101,
            'language' => 'EN',
        ]));

        $response->assertStatus(422);
    }

    public function test_start_validates_reverse_parameter(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.v1.words.start', [
            'reverse' => 'invalid',
            'language' => 'EN',
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
            'reverse' => true,
            'language' => 'DE',
        ]));

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 5);

        // Проверяем, что кэш содержит слова
        /** @var mixed $startCache */
        $startCache = \Illuminate\Support\Facades\Cache::get("words.start.DE.{$user->id}");
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
            'language' => 'EN',
        ]));

        $this->assertInstanceOf(Word::class, $word);
        $response->assertStatus(200)
            ->assertJsonPath('data.id', (string) $word->id)
            // Проверяем, что original и translated поменялись местами при reverse=true
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
            'language' => 'EN',
        ]));

        $this->assertInstanceOf(Word::class, $word);
        $response->assertStatus(200)
            ->assertJsonPath('data.id', (string) $word->id)
            // Проверяем, что original и translated на своих местах при reverse=false
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
        \Illuminate\Support\Facades\Cache::put("words.start.EN.{$user->id}", $wordIds);
        \Illuminate\Support\Facades\Cache::put("words.current.EN.{$user->id}", $wordIds[0]);
        \Illuminate\Support\Facades\Cache::put("words.next.EN.{$user->id}", null);
        \Illuminate\Support\Facades\Cache::put("words.prev.EN.{$user->id}", null);

        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.v1.words.start', [
            'limit' => 1,
            'reverse' => true,
            'language' => 'EN',
        ]));

        $this->assertInstanceOf(Word::class, $word);
        $response->assertStatus(200)
            ->assertJsonPath('data.id', (string) $word->id)
            // Проверяем, что reverse работает даже с существующей сессией
            ->assertJsonPath('data.attributes.original', 'привет')
            ->assertJsonPath('data.attributes.translated', 'hello');
    }

    public function test_next_goes_to_next_word(): void
    {
        $user = new User([
            'name' => 'Test User',
            'email' => 'test5@example.com',
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
        /** @var array<int> $wordIds */
        $wordIds = $words->pluck('id')->toArray();

        // Создаем сессию
        \Illuminate\Support\Facades\Cache::put("words.start.DE.{$user->id}", $wordIds);
        \Illuminate\Support\Facades\Cache::put("words.current.DE.{$user->id}", $wordIds[0]);
        \Illuminate\Support\Facades\Cache::put("words.next.DE.{$user->id}", $wordIds[1]);
        \Illuminate\Support\Facades\Cache::put("words.prev.DE.{$user->id}", null);

        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.v1.words.next', [
            'language' => 'DE',
        ]));

        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/vnd.api+json')
            ->assertJsonPath('data.id', (string) $wordIds[1])
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.next_id', $wordIds[2])
            ->assertJsonPath('meta.prev_id', $wordIds[0])
            ->assertJsonPath('meta.current_index', 2);
    }

    public function test_next_fails_without_authentication(): void
    {
        $response = $this->getJson(route('api.v1.words.next'));

        $response->assertStatus(401);
    }

    public function test_next_fails_when_no_next_word(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.v1.words.next', [
            'language' => 'EN',
        ]));

        $response->assertStatus(400)
            ->assertHeader('content-type', 'application/vnd.api+json')
            ->assertJsonPath('errors.0.status', '400')
            ->assertJsonPath('errors.0.title', 'Bad Request')
            ->assertJsonPath('errors.0.detail', 'No next word available');
    }

    public function test_next_fails_when_session_not_found(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Создаем кэш с next, но без сессии
        \Illuminate\Support\Facades\Cache::put("words.next.EN.{$user->id}", 999);

        $response = $this->getJson(route('api.v1.words.next', [
            'language' => 'EN',
        ]));

        $response->assertStatus(400)
            ->assertJsonPath('errors.0.detail', 'Study session not found');
    }

    public function test_next_increments_word_views(): void
    {
        $user = new User([
            'name' => 'Test User',
            'email' => 'test6@example.com',
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

        $words = Word::factory()->count(2)->create([
            'user_id' => $user->id,
            'language' => 'DE',
            'done_at' => null,
            'views' => 5,
        ]);
        /** @var array<int> $wordIds */
        $wordIds = $words->pluck('id')->toArray();

        // Создаем сессию
        \Illuminate\Support\Facades\Cache::put("words.start.DE.{$user->id}", $wordIds);
        \Illuminate\Support\Facades\Cache::put("words.current.DE.{$user->id}", $wordIds[0]);
        \Illuminate\Support\Facades\Cache::put("words.next.DE.{$user->id}", $wordIds[1]);
        \Illuminate\Support\Facades\Cache::put("words.prev.DE.{$user->id}", null);

        Sanctum::actingAs($user);

        $this->getJson(route('api.v1.words.next', [
            'language' => 'DE',
        ]));

        // Проверяем, что просмотры увеличились
        $word1 = Word::find($wordIds[1]);
        $this->assertInstanceOf(Word::class, $word1);
        $this->assertEquals(6, $word1->views);
    }

    public function test_next_reverse_swaps_original_and_translated(): void
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

        $words = Word::factory()->count(2)->create([
            'user_id' => $user->id,
            'language' => 'EN',
            'original' => 'hello',
            'translated' => 'привет',
            'done_at' => null,
        ]);
        /** @var array<int> $wordIds */
        $wordIds = $words->pluck('id')->toArray();

        \Illuminate\Support\Facades\Cache::put("words.start.EN.{$user->id}", $wordIds);
        \Illuminate\Support\Facades\Cache::put("words.current.EN.{$user->id}", $wordIds[0]);
        \Illuminate\Support\Facades\Cache::put("words.next.EN.{$user->id}", $wordIds[1]);
        \Illuminate\Support\Facades\Cache::put("words.prev.EN.{$user->id}", null);

        Sanctum::actingAs($user);

        $this->assertInstanceOf(Word::class, $words->first());
        $response = $this->getJson(route('api.v1.words.next', [
            'reverse' => true,
            'language' => 'EN',
        ]));

        $response->assertStatus(200)
            ->assertJsonPath('data.id', (string) $wordIds[1])
            // Проверяем, что original и translated поменялись местами
            ->assertJsonPath('data.attributes.original', 'привет')
            ->assertJsonPath('data.attributes.translated', 'hello');
    }

    public function test_next_reverse_false_keeps_original_and_translated(): void
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

        $words = Word::factory()->count(2)->create([
            'user_id' => $user->id,
            'language' => 'EN',
            'original' => 'hello',
            'translated' => 'привет',
            'done_at' => null,
        ]);
        /** @var array<int> $wordIds */
        $wordIds = $words->pluck('id')->toArray();

        \Illuminate\Support\Facades\Cache::put("words.start.EN.{$user->id}", $wordIds);
        \Illuminate\Support\Facades\Cache::put("words.current.EN.{$user->id}", $wordIds[0]);
        \Illuminate\Support\Facades\Cache::put("words.next.EN.{$user->id}", $wordIds[1]);
        \Illuminate\Support\Facades\Cache::put("words.prev.EN.{$user->id}", null);

        Sanctum::actingAs($user);

        $this->assertInstanceOf(Word::class, $words->first());
        $response = $this->getJson(route('api.v1.words.next', [
            'reverse' => false,
            'language' => 'EN',
        ]));

        $response->assertStatus(200)
            ->assertJsonPath('data.id', (string) $wordIds[1])
            // Проверяем, что original и translated на своих местах
            ->assertJsonPath('data.attributes.original', 'hello')
            ->assertJsonPath('data.attributes.translated', 'привет');
    }

    public function test_prev_goes_to_prev_word(): void
    {
        $user = new User([
            'name' => 'Test User',
            'email' => 'test7@example.com',
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
        /** @var array<int> $wordIds */
        $wordIds = $words->pluck('id')->toArray();

        // Создаем сессию на втором слове
        \Illuminate\Support\Facades\Cache::put("words.start.DE.{$user->id}", $wordIds);
        \Illuminate\Support\Facades\Cache::put("words.current.DE.{$user->id}", $wordIds[1]);
        \Illuminate\Support\Facades\Cache::put("words.next.DE.{$user->id}", $wordIds[2]);
        \Illuminate\Support\Facades\Cache::put("words.prev.DE.{$user->id}", $wordIds[0]);

        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.v1.words.prev', [
            'language' => 'DE',
        ]));

        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/vnd.api+json')
            ->assertJsonPath('data.id', (string) $wordIds[0])
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.next_id', $wordIds[1])
            ->assertJsonPath('meta.prev_id', null)
            ->assertJsonPath('meta.current_index', 1);
    }

    public function test_prev_fails_without_authentication(): void
    {
        $response = $this->getJson(route('api.v1.words.prev'));

        $response->assertStatus(401);
    }

    public function test_prev_fails_when_no_prev_word(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.v1.words.prev', [
            'language' => 'EN',
        ]));

        $response->assertStatus(400)
            ->assertHeader('content-type', 'application/vnd.api+json')
            ->assertJsonPath('errors.0.status', '400')
            ->assertJsonPath('errors.0.title', 'Bad Request')
            ->assertJsonPath('errors.0.detail', 'No previous word available');
    }

    public function test_prev_fails_when_session_not_found(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Создаем кэш с prev, но без сессии
        \Illuminate\Support\Facades\Cache::put("words.prev.EN.{$user->id}", 999);

        $response = $this->getJson(route('api.v1.words.prev', [
            'language' => 'EN',
        ]));

        $response->assertStatus(400)
            ->assertJsonPath('errors.0.detail', 'Study session not found');
    }

    public function test_prev_increments_word_views(): void
    {
        $user = new User([
            'name' => 'Test User',
            'email' => 'test8@example.com',
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

        $words = Word::factory()->count(2)->create([
            'user_id' => $user->id,
            'language' => 'DE',
            'done_at' => null,
            'views' => 5,
        ]);
        /** @var array<int> $wordIds */
        $wordIds = $words->pluck('id')->toArray();

        // Создаем сессию на втором слове
        \Illuminate\Support\Facades\Cache::put("words.start.DE.{$user->id}", $wordIds);
        \Illuminate\Support\Facades\Cache::put("words.current.DE.{$user->id}", $wordIds[1]);
        \Illuminate\Support\Facades\Cache::put("words.next.DE.{$user->id}", null);
        \Illuminate\Support\Facades\Cache::put("words.prev.DE.{$user->id}", $wordIds[0]);

        Sanctum::actingAs($user);

        $this->getJson(route('api.v1.words.prev', [
            'language' => 'DE',
        ]));

        // Проверяем, что просмотры увеличились
        $word0 = Word::find($wordIds[0]);
        $this->assertInstanceOf(Word::class, $word0);
        $this->assertEquals(6, $word0->views);
    }

    public function test_prev_reverse_swaps_original_and_translated(): void
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

        $words = Word::factory()->count(2)->create([
            'user_id' => $user->id,
            'language' => 'EN',
            'original' => 'hello',
            'translated' => 'привет',
            'done_at' => null,
        ]);
        /** @var array<int> $wordIds */
        $wordIds = $words->pluck('id')->toArray();

        \Illuminate\Support\Facades\Cache::put("words.start.EN.{$user->id}", $wordIds);
        \Illuminate\Support\Facades\Cache::put("words.current.EN.{$user->id}", $wordIds[1]);
        \Illuminate\Support\Facades\Cache::put("words.next.EN.{$user->id}", null);
        \Illuminate\Support\Facades\Cache::put("words.prev.EN.{$user->id}", $wordIds[0]);

        Sanctum::actingAs($user);

        $this->assertInstanceOf(Word::class, $words->first());
        $response = $this->getJson(route('api.v1.words.prev', [
            'reverse' => true,
            'language' => 'EN',
        ]));

        $response->assertStatus(200)
            ->assertJsonPath('data.id', (string) $wordIds[0])
            // Проверяем, что original и translated поменялись местами
            ->assertJsonPath('data.attributes.original', 'привет')
            ->assertJsonPath('data.attributes.translated', 'hello');
    }

    public function test_prev_reverse_false_keeps_original_and_translated(): void
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

        $words = Word::factory()->count(2)->create([
            'user_id' => $user->id,
            'language' => 'EN',
            'original' => 'hello',
            'translated' => 'привет',
            'done_at' => null,
        ]);
        /** @var array<int> $wordIds */
        $wordIds = $words->pluck('id')->toArray();

        \Illuminate\Support\Facades\Cache::put("words.start.EN.{$user->id}", $wordIds);
        \Illuminate\Support\Facades\Cache::put("words.current.EN.{$user->id}", $wordIds[1]);
        \Illuminate\Support\Facades\Cache::put("words.next.EN.{$user->id}", null);
        \Illuminate\Support\Facades\Cache::put("words.prev.EN.{$user->id}", $wordIds[0]);

        Sanctum::actingAs($user);

        $this->assertInstanceOf(Word::class, $words->first());
        $response = $this->getJson(route('api.v1.words.prev', [
            'reverse' => false,
            'language' => 'EN',
        ]));

        $response->assertStatus(200)
            ->assertJsonPath('data.id', (string) $wordIds[0])
            // Проверяем, что original и translated на своих местах
            ->assertJsonPath('data.attributes.original', 'hello')
            ->assertJsonPath('data.attributes.translated', 'привет');
    }

    public function test_next_validates_reverse_parameter(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.v1.words.next', [
            'reverse' => 'invalid',
            'language' => 'EN',
        ]));

        $response->assertStatus(422)
            ->assertHeader('content-type', 'application/vnd.api+json');
    }

    public function test_prev_validates_reverse_parameter(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.v1.words.prev', [
            'reverse' => 'invalid',
            'language' => 'EN',
        ]));

        $response->assertStatus(422)
            ->assertHeader('content-type', 'application/vnd.api+json');
    }
}
