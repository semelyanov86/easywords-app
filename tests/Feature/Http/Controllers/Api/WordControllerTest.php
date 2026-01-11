<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\User;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class WordControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_creates_word_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson(route('api.v1.words.store'), [
            'original' => 'hello',
            'translated' => 'привет',
            'language' => 'EN',
        ]);

        $response->assertStatus(201)
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
            ])
            ->assertJsonPath('data.attributes.original', 'hello')
            ->assertJsonPath('data.attributes.translated', 'привет')
            ->assertJsonPath('data.attributes.language', 'EN');

        $this->assertDatabaseHas(Word::class, [
            'user_id' => $user->id,
            'original' => 'hello',
            'translated' => 'привет',
            'language' => 'EN',
        ]);
    }

    public function test_store_fails_without_authentication(): void
    {
        $response = $this->postJson(route('api.v1.words.store'), [
            'original' => 'hello',
            'translated' => 'привет',
            'language' => 'EN',
        ]);

        $response->assertStatus(401);
    }

    public function test_store_validates_required_fields(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson(route('api.v1.words.store'), [
            'original' => 'hello',
        ]);

        $response->assertStatus(422);
    }

    public function test_increment_views_increments_word_views(): void
    {
        $user = User::factory()->create();
        $word = Word::factory()->create([
            'user_id' => $user->id,
            'views' => 5,
        ]);
        Sanctum::actingAs($user);

        $response = $this->postJson(route('api.v1.words.views', ['word' => $word->id]));

        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/vnd.api+json')
            ->assertJsonPath('data.attributes.views', 6);

        $this->assertDatabaseHas(Word::class, [
            'id' => $word->id,
            'views' => 6,
        ]);
    }

    public function test_increment_views_fails_for_different_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $word = Word::factory()->create([
            'user_id' => $user1->id,
            'views' => 5,
        ]);
        Sanctum::actingAs($user2);

        $response = $this->postJson(route('api.v1.words.views', ['word' => $word->id]));

        $response->assertStatus(403)
            ->assertHeader('content-type', 'application/vnd.api+json');
    }

    public function test_toggle_starred_updates_starred_status(): void
    {
        $user = User::factory()->create();
        $word = Word::factory()->create([
            'user_id' => $user->id,
            'starred' => false,
        ]);
        Sanctum::actingAs($user);

        $response = $this->putJson(route('api.v1.words.starred', ['word' => $word->id]), [
            'starred' => true,
        ]);

        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/vnd.api+json')
            ->assertJsonPath('data.attributes.starred', true);

        $this->assertDatabaseHas(Word::class, [
            'id' => $word->id,
            'starred' => true,
        ]);
    }

    public function test_toggle_starred_fails_for_different_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $word = Word::factory()->create([
            'user_id' => $user1->id,
            'starred' => false,
        ]);
        Sanctum::actingAs($user2);

        $response = $this->putJson(route('api.v1.words.starred', ['word' => $word->id]), [
            'starred' => true,
        ]);

        $response->assertStatus(403)
            ->assertHeader('content-type', 'application/vnd.api+json');
    }

    public function test_mark_as_learned_sets_done_at(): void
    {
        $user = User::factory()->create();
        $word = Word::factory()->create([
            'user_id' => $user->id,
            'done_at' => null,
        ]);
        Sanctum::actingAs($user);

        $response = $this->postJson(route('api.v1.words.learned', ['word' => $word->id]));

        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/vnd.api+json')
            ->assertJsonPath('data.attributes.done_at', fn ($doneAt) => $doneAt !== null);

        $this->assertNotNull(Word::find($word->id)?->done_at);
    }

    public function test_mark_as_learned_fails_for_different_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $word = Word::factory()->create([
            'user_id' => $user1->id,
            'done_at' => null,
        ]);
        Sanctum::actingAs($user2);

        $response = $this->postJson(route('api.v1.words.learned', ['word' => $word->id]));

        $response->assertStatus(403)
            ->assertHeader('content-type', 'application/vnd.api+json');
    }

    public function test_show_returns_word_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $word = Word::factory()->create([
            'user_id' => $user->id,
            'original' => 'Hallo',
            'translated' => 'Привет',
            'language' => 'DE',
        ]);
        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.v1.words.show', ['word' => $word->id]));

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
            ])
            ->assertJsonPath('data.id', (string) $word->id)
            ->assertJsonPath('data.attributes.original', 'Hallo')
            ->assertJsonPath('data.attributes.translated', 'Привет')
            ->assertJsonPath('data.attributes.language', 'DE');
    }

    public function test_show_fails_without_authentication(): void
    {
        $user = User::factory()->create();
        $word = Word::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->getJson(route('api.v1.words.show', ['word' => $word->id]));

        $response->assertStatus(401)
            ->assertHeader('content-type', 'application/json');
    }

    public function test_show_fails_when_word_belongs_to_other_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $word = Word::factory()->create([
            'user_id' => $user1->id,
        ]);
        Sanctum::actingAs($user2);

        $response = $this->getJson(route('api.v1.words.show', ['word' => $word->id]));

        $response->assertStatus(404)
            ->assertHeader('content-type', 'application/vnd.api+json')
            ->assertJsonPath('errors.0.status', '404')
            ->assertJsonPath('errors.0.title', 'Not Found');
    }

    public function test_show_fails_when_word_does_not_exist(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.v1.words.show', ['word' => 999]));

        $response->assertStatus(404)
            ->assertHeader('content-type', 'application/vnd.api+json')
            ->assertJsonPath('errors.0.status', '404')
            ->assertJsonPath('errors.0.title', 'Not Found');
    }

    public function test_destroy_deletes_word_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $word = Word::factory()->create([
            'user_id' => $user->id,
        ]);
        Sanctum::actingAs($user);

        $response = $this->deleteJson(route('api.v1.words.destroy', ['word' => $word->id]));

        $response->assertStatus(204);

        $this->assertDatabaseMissing('words', [
            'id' => $word->id,
        ]);
    }

    public function test_destroy_fails_without_authentication(): void
    {
        $user = User::factory()->create();
        $word = Word::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->deleteJson(route('api.v1.words.destroy', ['word' => $word->id]));

        $response->assertStatus(401)
            ->assertHeader('content-type', 'application/json');

        $this->assertDatabaseHas('words', [
            'id' => $word->id,
        ]);
    }

    public function test_destroy_fails_when_word_belongs_to_other_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $word = Word::factory()->create([
            'user_id' => $user1->id,
        ]);
        Sanctum::actingAs($user2);

        $response = $this->deleteJson(route('api.v1.words.destroy', ['word' => $word->id]));

        $response->assertStatus(404)
            ->assertHeader('content-type', 'application/vnd.api+json')
            ->assertJsonPath('errors.0.status', '404')
            ->assertJsonPath('errors.0.title', 'Not Found');

        $this->assertDatabaseHas('words', [
            'id' => $word->id,
        ]);
    }

    public function test_destroy_fails_when_word_does_not_exist(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->deleteJson(route('api.v1.words.destroy', ['word' => 999]));

        $response->assertStatus(404)
            ->assertHeader('content-type', 'application/vnd.api+json')
            ->assertJsonPath('errors.0.status', '404')
            ->assertJsonPath('errors.0.title', 'Not Found');
    }

    public function test_index_returns_words_for_authenticated_user(): void
    {
        // Создаем пользователя напрямую с настройками
        $user = new User([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'settings' => [
                'paginate' => 20,
                'fresh_first' => true,
                'show_starred' => true,
                'latest_first' => false,
                'known_enabled' => true, // Показывать ВСЕ слова, включая изученные
                'main_language' => 'RU',
                'show_imported' => true,
                'languages_list' => ['DE', 'EN'],
                'starred_enabled' => false,
                'default_language' => 'DE',
            ],
        ]);
        $user->save();

        $word1 = Word::factory()->create([
            'user_id' => $user->id,
            'language' => 'DE',
            'original' => 'Hallo',
        ]);
        $word2 = Word::factory()->create([
            'user_id' => $user->id,
            'language' => 'DE',
            'original' => 'Tschüss',
        ]);
        Word::factory()->create([
            'user_id' => $user->id,
            'language' => 'EN',
            'original' => 'Hello',
        ]);
        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.v1.words.index', ['language' => 'de']));

        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/vnd.api+json')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
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
                ],
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                ],
            ])
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.attributes.original', 'Hallo')
            ->assertJsonPath('data.1.attributes.original', 'Tschüss');

        /** @var array<mixed> $data */
        $data = $response->json('data');
        $originals = collect($data)->pluck('attributes.original')->sort()->values()->toArray();
        $this->assertEquals(['Hallo', 'Tschüss'], $originals);
    }

    public function test_index_fails_without_authentication(): void
    {
        $response = $this->getJson(route('api.v1.words.index', ['language' => 'de']));

        $response->assertStatus(401)
            ->assertHeader('content-type', 'application/json');
    }

    public function test_index_validates_language_parameter(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.v1.words.index'));

        $response->assertStatus(422);

        $response = $this->getJson(route('api.v1.words.index', ['language' => 'invalid']));

        $response->assertStatus(422);
    }

    public function test_index_respects_pagination(): void
    {
        // Создаем пользователя напрямую с настройками
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
            'known_enabled' => true,
            'main_language' => 'RU',
            'show_imported' => true,
            'languages_list' => ['DE', 'EN'],
            'starred_enabled' => false,
            'default_language' => 'DE',
        ]);

        Word::factory()->count(25)->create([
            'user_id' => $user->id,
            'language' => 'DE',
            'shared_by' => null,
            'from_sample' => false,
        ]);
        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.v1.words.index', ['language' => 'de']));

        $response->assertStatus(200)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 20)
            ->assertJsonPath('meta.total', 20)
            ->assertJsonPath('meta.last_page', 1)
            ->assertJsonCount(20, 'data');
    }
}
