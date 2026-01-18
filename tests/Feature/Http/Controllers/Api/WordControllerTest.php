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

        $response = $this->putJson(route('api.v1.words.starred', ['word' => $word->id]));

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

        $response = $this->putJson(route('api.v1.words.starred', ['word' => $word->id]));

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

    public function test_share_creates_copy_for_target_user(): void
    {
        $author = User::factory()->create();
        $targetUser = User::factory()->create();
        Sanctum::actingAs($author);

        $originalWord = Word::factory()->create([
            'user_id' => $author->id,
            'original' => 'share',
            'translated' => 'поделиться',
            'language' => 'EN',
            'starred' => true,
            'views' => 10,
            'from_sample' => true,
        ]);

        $response = $this->postJson(route('api.v1.words.share'), [
            'word_id' => $originalWord->id,
            'user_id' => $targetUser->id,
        ]);

        $response->assertStatus(201)
            ->assertHeader('content-type', 'application/vnd.api+json')
            ->assertJsonPath('data.attributes.original', 'share')
            ->assertJsonPath('data.attributes.translated', 'поделиться')
            ->assertJsonPath('data.attributes.starred', false)
            ->assertJsonPath('data.attributes.views', 0)
            ->assertJsonPath('data.attributes.from_sample', false);

        $this->assertDatabaseHas(Word::class, [
            'user_id' => $targetUser->id,
            'original' => 'share',
            'translated' => 'поделиться',
            'shared_by' => $author->id,
            'starred' => false,
            'views' => 0,
            'from_sample' => false,
        ]);

        // Оригинальное слово не должно измениться
        $originalWord->refresh();
        $this->assertEquals($author->id, $originalWord->user_id);
        $this->assertNull($originalWord->shared_by);
    }

    public function test_share_fails_without_authentication(): void
    {
        $author = User::factory()->create();
        $targetUser = User::factory()->create();
        $word = Word::factory()->create(['user_id' => $author->id]);

        $response = $this->postJson(route('api.v1.words.share'), [
            'word_id' => $word->id,
            'user_id' => $targetUser->id,
        ]);

        $response->assertStatus(401);
    }

    public function test_share_fails_when_word_does_not_belong_to_user(): void
    {
        $author = User::factory()->create();
        $user2 = User::factory()->create();
        $targetUser = User::factory()->create();
        Sanctum::actingAs($author);

        $word = Word::factory()->create(['user_id' => $user2->id]);

        $response = $this->postJson(route('api.v1.words.share'), [
            'word_id' => $word->id,
            'user_id' => $targetUser->id,
        ]);

        $response->assertStatus(403)
            ->assertHeader('content-type', 'application/vnd.api+json')
            ->assertJsonPath('errors.0.status', '403')
            ->assertJsonPath('errors.0.title', 'Forbidden');
    }

    public function test_share_fails_when_sharing_with_same_user(): void
    {
        $author = User::factory()->create();
        Sanctum::actingAs($author);

        $word = Word::factory()->create(['user_id' => $author->id]);

        $response = $this->postJson(route('api.v1.words.share'), [
            'word_id' => $word->id,
            'user_id' => $author->id,
        ]);

        $response->assertStatus(422);
    }

    public function test_filtered_returns_all_words_without_filters(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Word::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->getJson(route('api.v1.words.filtered'));

        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/vnd.api+json')
            ->assertJsonPath('meta.total', '3');
    }

    public function test_filtered_filters_by_done_true(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $doneWord = Word::factory()->create([
            'user_id' => $user->id,
            'done_at' => now(),
        ]);
        Word::factory()->create([
            'user_id' => $user->id,
            'done_at' => null,
        ]);

        $response = $this->getJson(route('api.v1.words.filtered', ['done' => 'true']));

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', '1')
            ->assertJsonPath('data.0.id', (string) $doneWord->id);
    }

    public function test_filtered_filters_by_done_false(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Word::factory()->create([
            'user_id' => $user->id,
            'done_at' => now(),
        ]);
        $notDoneWord = Word::factory()->create([
            'user_id' => $user->id,
            'done_at' => null,
        ]);

        $response = $this->getJson(route('api.v1.words.filtered', ['done' => 'false']));

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', '1')
            ->assertJsonPath('data.0.id', (string) $notDoneWord->id);
    }

    public function test_filtered_filters_by_shared_true(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $sharedWord = Word::factory()->create([
            'user_id' => $user->id,
            'shared_by' => 1,
        ]);
        Word::factory()->create([
            'user_id' => $user->id,
            'shared_by' => null,
        ]);

        $response = $this->getJson(route('api.v1.words.filtered', ['shared' => 'true']));

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', '1')
            ->assertJsonPath('data.0.id', (string) $sharedWord->id);
    }

    public function test_filtered_filters_by_shared_false(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Word::factory()->create([
            'user_id' => $user->id,
            'shared_by' => 1,
        ]);
        $notSharedWord = Word::factory()->create([
            'user_id' => $user->id,
            'shared_by' => null,
        ]);

        $response = $this->getJson(route('api.v1.words.filtered', ['shared' => 'false']));

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', '1')
            ->assertJsonPath('data.0.id', (string) $notSharedWord->id);
    }

    public function test_filtered_filters_by_from_sample_true(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $sampleWord = Word::factory()->create([
            'user_id' => $user->id,
            'from_sample' => true,
        ]);
        Word::factory()->create([
            'user_id' => $user->id,
            'from_sample' => false,
        ]);

        $response = $this->getJson(route('api.v1.words.filtered', ['from_sample' => 'true']));

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', '1')
            ->assertJsonPath('data.0.id', (string) $sampleWord->id);
    }

    public function test_filtered_filters_by_starred_true(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $starredWord = Word::factory()->create([
            'user_id' => $user->id,
            'starred' => true,
        ]);
        Word::factory()->create([
            'user_id' => $user->id,
            'starred' => false,
        ]);

        $response = $this->getJson(route('api.v1.words.filtered', ['starred' => 'true']));

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', '1')
            ->assertJsonPath('data.0.id', (string) $starredWord->id);
    }

    public function test_filtered_returns_only_user_words(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        Sanctum::actingAs($user1);

        Word::factory()->count(3)->create(['user_id' => $user1->id]);
        Word::factory()->count(2)->create(['user_id' => $user2->id]);

        $response = $this->getJson(route('api.v1.words.filtered'));

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', '3');
    }

    public function test_statistics_returns_user_statistics(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Word::factory()->create([
            'user_id' => $user->id,
            'starred' => true,
            'done_at' => now(),
            'views' => 10,
        ]);
        Word::factory()->create([
            'user_id' => $user->id,
            'starred' => false,
            'done_at' => null,
            'views' => 5,
        ]);
        Word::factory()->create([
            'user_id' => $user->id,
            'starred' => true,
            'done_at' => now(),
            'views' => 15,
        ]);

        $response = $this->getJson(route('api.v1.statistics'));

        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/vnd.api+json')
            ->assertJsonStructure([
                'data' => [
                    'type',
                    'attributes' => [
                        'total_words',
                        'starred_words',
                        'not_done_words',
                        'done_words',
                        'total_views',
                        'total_users',
                        'top_viewed_words',
                        'words_added_today',
                        'words_updated_today',
                        'words_updated_this_month',
                    ],
                ],
            ])
            ->assertJsonPath('data.attributes.total_words', 3)
            ->assertJsonPath('data.attributes.starred_words', 2)
            ->assertJsonPath('data.attributes.not_done_words', 1)
            ->assertJsonPath('data.attributes.done_words', 2)
            ->assertJsonPath('data.attributes.total_views', 30);
    }

    public function test_statistics_includes_total_users(): void
    {
        User::factory()->count(5)->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.v1.statistics'));

        $response->assertStatus(200)
            ->assertJsonPath('data.attributes.total_users', 6);
    }

    public function test_statistics_fails_without_authentication(): void
    {
        $response = $this->getJson(route('api.v1.statistics'));

        $response->assertStatus(401);
    }

    public function test_search_returns_matching_words_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Word::factory()->create([
            'user_id' => $user->id,
            'original' => 'hello',
            'translated' => 'привет',
        ]);
        Word::factory()->create([
            'user_id' => $user->id,
            'original' => 'help',
            'translated' => 'помощь',
        ]);
        Word::factory()->create([
            'user_id' => $user->id,
            'original' => 'world',
            'translated' => 'мир',
        ]);

        $response = $this->getJson(route('api.v1.words.search', ['query' => 'hel']));

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
            ])
            ->assertJsonCount(2, 'data');

        /** @var array<mixed> $data */
        $data = $response->json('data');
        $originals = collect($data)->pluck('attributes.original')->toArray();
        $this->assertContains('hello', $originals);
        $this->assertContains('help', $originals);
    }

    public function test_search_fails_without_authentication(): void
    {
        $response = $this->getJson(route('api.v1.words.search', ['query' => 'test']));

        $response->assertStatus(401)
            ->assertHeader('content-type', 'application/json');
    }

    public function test_search_by_original_field(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Word::factory()->create([
            'user_id' => $user->id,
            'original' => 'hello',
            'translated' => 'привет',
        ]);
        Word::factory()->create([
            'user_id' => $user->id,
            'original' => 'world',
            'translated' => 'мир',
        ]);

        $response = $this->getJson(route('api.v1.words.search', ['query' => 'hello']));

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.original', 'hello')
            ->assertJsonPath('data.0.attributes.translated', 'привет');
    }

    public function test_search_by_translated_field(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Word::factory()->create([
            'user_id' => $user->id,
            'original' => 'hello',
            'translated' => 'привет',
        ]);
        Word::factory()->create([
            'user_id' => $user->id,
            'original' => 'goodbye',
            'translated' => 'до свидания',
        ]);

        $response = $this->getJson(route('api.v1.words.search', ['query' => 'привет']));

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.original', 'hello')
            ->assertJsonPath('data.0.attributes.translated', 'привет');
    }

    public function test_search_returns_words_matching_in_both_fields(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Word::factory()->create([
            'user_id' => $user->id,
            'original' => 'hello',
            'translated' => 'привет',
        ]);
        Word::factory()->create([
            'user_id' => $user->id,
            'original' => 'hi',
            'translated' => 'приветствие',
        ]);
        Word::factory()->create([
            'user_id' => $user->id,
            'original' => 'greeting',
            'translated' => 'привет',
        ]);

        $response = $this->getJson(route('api.v1.words.search', ['query' => 'привет']));

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_search_returns_empty_array_when_no_matches(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Word::factory()->create([
            'user_id' => $user->id,
            'original' => 'hello',
            'translated' => 'привет',
        ]);

        $response = $this->getJson(route('api.v1.words.search', ['query' => 'nonexistent']));

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_search_returns_only_user_words(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        Sanctum::actingAs($user1);

        Word::factory()->create([
            'user_id' => $user1->id,
            'original' => 'hello',
            'translated' => 'привет',
        ]);
        Word::factory()->create([
            'user_id' => $user2->id,
            'original' => 'hello',
            'translated' => 'привет',
        ]);

        $response = $this->getJson(route('api.v1.words.search', ['query' => 'hello']));

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.original', 'hello');
    }

    public function test_search_results_are_sorted_alphabetically(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Word::factory()->create([
            'user_id' => $user->id,
            'original' => 'zebra',
            'translated' => 'зебра',
        ]);
        Word::factory()->create([
            'user_id' => $user->id,
            'original' => 'apple',
            'translated' => 'яблоко',
        ]);
        Word::factory()->create([
            'user_id' => $user->id,
            'original' => 'banana',
            'translated' => 'банан',
        ]);

        $response = $this->getJson(route('api.v1.words.search', ['query' => '']));

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.attributes.original', 'apple')
            ->assertJsonPath('data.1.attributes.original', 'banana')
            ->assertJsonPath('data.2.attributes.original', 'zebra');
    }

    public function test_search_is_case_insensitive(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Word::factory()->create([
            'user_id' => $user->id,
            'original' => 'Hello',
            'translated' => 'Привет',
        ]);

        $response1 = $this->getJson(route('api.v1.words.search', ['query' => 'hello']));
        $response2 = $this->getJson(route('api.v1.words.search', ['query' => 'HELLO']));
        $response3 = $this->getJson(route('api.v1.words.search', ['query' => 'Hello']));

        $response1->assertStatus(200)->assertJsonCount(1, 'data');
        $response2->assertStatus(200)->assertJsonCount(1, 'data');
        $response3->assertStatus(200)->assertJsonCount(1, 'data');
    }

    public function test_search_performs_partial_match(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Word::factory()->create([
            'user_id' => $user->id,
            'original' => 'hello',
            'translated' => 'приветствие',
        ]);
        Word::factory()->create([
            'user_id' => $user->id,
            'original' => 'help',
            'translated' => 'помощь',
        ]);

        $response = $this->getJson(route('api.v1.words.search', ['query' => 'hel']));

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');

        /** @var array<mixed> $data */
        $data = $response->json('data');
        $originals = collect($data)->pluck('attributes.original')->toArray();
        $this->assertContains('hello', $originals);
        $this->assertContains('help', $originals);
    }
}
