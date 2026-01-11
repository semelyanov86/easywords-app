<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\User;
use App\Models\Word;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Тесты для контроллера получения случайных слов.
 */
final class WordControllerTest extends TestCase
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
     * Тест успешного получения случайных слов с дефолтным лимитом.
     */
    public function test_can_get_random_words_with_default_limit(): void
    {
        Word::factory()->count(25)->create([
            'user_id' => $this->user->id,
            'language' => 'DE',
            'done_at' => null,
        ]);

        $token = $this->user->createToken('api-token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson(route('api.v1.words.random'));

        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/vnd.api+json');

        /** @var array{data: array<array{type: string, id: string}>} $responseData */
        $responseData = $response->json();

        $this->assertCount(20, $responseData['data']);
        $this->assertEquals('words', $responseData['data'][0]['type']);
    }

    /**
     * Тест получения случайных слов с кастомным лимитом.
     */
    public function test_can_get_random_words_with_custom_limit(): void
    {
        Word::factory()->count(30)->create([
            'user_id' => $this->user->id,
            'language' => 'DE',
            'done_at' => null,
        ]);

        $token = $this->user->createToken('api-token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson(route('api.v1.words.random') . '?limit=10');

        $response->assertStatus(200);

        /** @var array{data: array<array{id: string}>} $responseData */
        $responseData = $response->json();
        $this->assertCount(10, $responseData['data']);
    }

    /**
     * Тест ошибки при попытке получения без токена.
     */
    public function test_cannot_get_random_words_without_token(): void
    {
        Word::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'language' => 'DE',
            'done_at' => null,
        ]);

        $response = $this->getJson(route('api.v1.words.random'));

        $response->assertStatus(401);
    }

    /**
     * Тест ошибки при использовании неверного токена.
     */
    public function test_cannot_get_random_words_with_invalid_token(): void
    {
        Word::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'language' => 'DE',
            'done_at' => null,
        ]);

        $response = $this->withToken('invalid-token')
            ->getJson(route('api.v1.words.random'));

        $response->assertStatus(401);
    }

    /**
     * Тест фильтрации по языку пользователя (default_language).
     */
    public function test_filters_by_default_language(): void
    {
        Word::factory()->count(10)->create([
            'user_id' => $this->user->id,
            'language' => 'DE',
            'done_at' => null,
        ]);

        Word::factory()->count(10)->create([
            'user_id' => $this->user->id,
            'language' => 'EN',
            'done_at' => null,
        ]);

        $token = $this->user->createToken('api-token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson(route('api.v1.words.random'));

        $response->assertStatus(200);

        /** @var array{data: array<array{attributes: array{language: string}}>} $responseData */
        $responseData = $response->json();

        // По умолчанию default_language = 'DE'
        $this->assertCount(10, $responseData['data']);
        foreach ($responseData['data'] as $word) {
            $this->assertEquals('DE', $word['attributes']['language']);
        }
    }

    /**
     * Тест исключения изученных слов (known_enabled === false).
     */
    public function test_excludes_known_words_when_known_enabled_is_false(): void
    {
        Word::factory()->count(10)->create([
            'user_id' => $this->user->id,
            'language' => 'DE',
            'done_at' => CarbonImmutable::now(),
        ]);

        Word::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'language' => 'DE',
            'done_at' => null,
        ]);

        // Устанавливаем known_enabled = false
        $this->user->settings()->set('known_enabled', false);
        $this->user->save();

        $token = $this->user->createToken('api-token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson(route('api.v1.words.random'));

        $response->assertStatus(200);

        /** @var array{data: array<array{attributes: array{done_at: string|null}}>} $responseData */
        $responseData = $response->json();
        $this->assertCount(5, $responseData['data']);

        foreach ($responseData['data'] as $word) {
            $this->assertNull($word['attributes']['done_at']);
        }
    }

    /**
     * Тест включения изученных слов (known_enabled === true).
     */
    public function test_includes_known_words_when_known_enabled_is_true(): void
    {
        Word::factory()->count(10)->create([
            'user_id' => $this->user->id,
            'language' => 'DE',
            'done_at' => CarbonImmutable::now(),
        ]);

        Word::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'language' => 'DE',
            'done_at' => null,
        ]);

        // Устанавливаем known_enabled = true
        $this->user->settings()->set('known_enabled', true);
        $this->user->save();

        $token = $this->user->createToken('api-token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson(route('api.v1.words.random'));

        $response->assertStatus(200);

        /** @var array{data: array<array{id: string}>} $responseData */
        $responseData = $response->json();
        $this->assertCount(15, $responseData['data']);
    }

    /**
     * Тест валидации лимита (минимальное значение).
     */
    public function test_validates_limit_minimum(): void
    {
        Word::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'language' => 'DE',
            'done_at' => null,
        ]);

        $token = $this->user->createToken('api-token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson(route('api.v1.words.random') . '?limit=0');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['limit']);
    }

    /**
     * Тест валидации лимита (максимальное значение).
     */
    public function test_validates_limit_maximum(): void
    {
        Word::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'language' => 'DE',
            'done_at' => null,
        ]);

        $token = $this->user->createToken('api-token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson(route('api.v1.words.random') . '?limit=101');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['limit']);
    }

    /**
     * Тест валидации лимита (некорректный тип).
     */
    public function test_validates_limit_type(): void
    {
        Word::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'language' => 'DE',
            'done_at' => null,
        ]);

        $token = $this->user->createToken('api-token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson(route('api.v1.words.random') . '?limit=abc');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['limit']);
    }

    /**
     * Тест, что возвращает все слова, если их меньше лимита.
     */
    public function test_returns_all_words_if_less_than_limit(): void
    {
        Word::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'language' => 'DE',
            'done_at' => null,
        ]);

        $token = $this->user->createToken('api-token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson(route('api.v1.words.random') . '?limit=20');

        $response->assertStatus(200);

        /** @var array{data: array<array{id: string}>} $responseData */
        $responseData = $response->json();
        $this->assertCount(5, $responseData['data']);
    }

    /**
     * Тест, что не возвращает слова других пользователей.
     */
    public function test_does_not_return_other_users_words(): void
    {
        $otherUser = User::factory()->create();

        Word::factory()->count(10)->create([
            'user_id' => $this->user->id,
            'language' => 'DE',
            'done_at' => null,
        ]);

        Word::factory()->count(10)->create([
            'user_id' => $otherUser->id,
            'language' => 'DE',
            'done_at' => null,
        ]);

        $token = $this->user->createToken('api-token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson(route('api.v1.words.random'));

        $response->assertStatus(200);

        /** @var array{data: array<array{id: string}>} $responseData */
        $responseData = $response->json();
        $this->assertCount(10, $responseData['data']);

        // Проверяем, что все возвращённые слова принадлежат текущему пользователю
        $returnedWordIds = collect($responseData['data'])
            ->pluck('id')
            ->map(fn (mixed $id): int => match (true) {
                is_string($id) => (int) $id,
                is_int($id) => $id,
                default => 0,
            })
            ->values();
        $userWordIds = Word::where('user_id', $this->user->id)->pluck('id');
        $this->assertEquals($userWordIds->sort()->values(), $returnedWordIds->sort()->values());
    }

    /**
     * Тест JSON:API формата ответа.
     */
    public function test_returns_correct_json_api_format(): void
    {
        Word::factory()->create([
            'user_id' => $this->user->id,
            'original' => 'test',
            'translated' => 'тест',
            'language' => 'DE',
            'done_at' => null,
            'starred' => false,
            'views' => 0,
            'from_sample' => false,
        ]);

        $token = $this->user->createToken('api-token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson(route('api.v1.words.random'));

        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/vnd.api+json')
            ->assertJson([
                'data' => [
                    [
                        'type' => 'words',
                        'attributes' => [
                            'original' => 'test',
                            'translated' => 'тест',
                            'language' => 'DE',
                            'done_at' => null,
                            'starred' => false,
                            'views' => 0,
                            'from_sample' => false,
                        ],
                    ],
                ],
            ]);
    }
}
