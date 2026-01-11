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
}
