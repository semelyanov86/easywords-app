<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Sample;
use App\Models\User;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Тесты для контроллера импорта samples.
 */
final class SampleControllerTest extends TestCase
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
     * Тест успешного импорта samples.
     */
    public function test_can_import_samples(): void
    {
        Sample::factory()->count(3)->create([
            'language' => 'EN',
        ]);

        $token = $this->user->createToken('api-token')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson(route('api.v1.samples.import', ['language' => 'EN']));

        $response->assertStatus(201)
            ->assertHeader('content-type', 'application/vnd.api+json')
            ->assertJsonStructure([
                'data' => [
                    'type',
                    'id',
                    'attributes' => [
                        'total_created',
                        'total_skipped',
                    ],
                    'relationships' => [
                        'words' => [
                            'data' => [
                                '*' => ['type', 'id'],
                            ],
                        ],
                    ],
                ],
                'included' => [
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
            ]);

        /** @var array{data: array{attributes: array{total_created: int, total_skipped: int}}} $responseData */
        $responseData = $response->json();
        $this->assertEquals(3, $responseData['data']['attributes']['total_created']);
        $this->assertEquals(0, $responseData['data']['attributes']['total_skipped']);

        // Проверяем, что слова были созданы в базе
        $this->assertCount(3, Word::where('user_id', $this->user->id)->where('language', 'EN')->get());
    }

    /**
     * Тест ошибки при попытке импорта без токена.
     */
    public function test_cannot_import_samples_without_token(): void
    {
        Sample::factory()->count(2)->create(['language' => 'EN']);

        $response = $this->postJson(route('api.v1.samples.import', ['language' => 'EN']));

        $response->assertStatus(401);
    }

    /**
     * Тест ошибки при использовании неверного токена.
     */
    public function test_cannot_import_samples_with_invalid_token(): void
    {
        Sample::factory()->count(2)->create(['language' => 'EN']);

        $response = $this->withToken('invalid-token')
            ->postJson(route('api.v1.samples.import', ['language' => 'EN']));

        $response->assertStatus(401);
    }

    /**
     * Тест пропуска уже существующих слов.
     */
    public function test_skips_existing_words(): void
    {
        Sample::factory()->create([
            'original' => 'hello',
            'translated' => 'привет',
            'language' => 'EN',
        ]);

        Sample::factory()->create([
            'original' => 'world',
            'translated' => 'мир',
            'language' => 'EN',
        ]);

        // Создаём одно слово у пользователя заранее
        Word::factory()->create([
            'user_id' => $this->user->id,
            'original' => 'hello',
            'translated' => 'привет',
            'language' => 'EN',
            'from_sample' => false,
        ]);

        $token = $this->user->createToken('api-token')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson(route('api.v1.samples.import', ['language' => 'EN']));

        $response->assertStatus(201);

        /** @var array{data: array{attributes: array{total_created: int, total_skipped: int}}} $responseData */
        $responseData = $response->json();
        $this->assertEquals(1, $responseData['data']['attributes']['total_created']);
        $this->assertEquals(1, $responseData['data']['attributes']['total_skipped']);

        // Проверяем, что всего у пользователя 2 слова
        $this->assertCount(2, Word::where('user_id', $this->user->id)->where('language', 'EN')->get());

        // Проверяем, что существующее слово не изменилось
        $existingWord = Word::where('user_id', $this->user->id)
            ->where('original', 'hello')
            ->where('language', 'EN')
            ->first();

        $this->assertNotNull($existingWord);
        $this->assertFalse($existingWord->from_sample);
    }

    /**
     * Тест ошибки при несуществующем языке.
     */
    public function test_returns_404_for_nonexistent_language(): void
    {
        Sample::factory()->count(2)->create(['language' => 'EN']);

        $token = $this->user->createToken('api-token')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson(route('api.v1.samples.import', ['language' => 'FR']));

        $response->assertStatus(404);
    }

    /**
     * Тест, что импорт не затрагивает слова других пользователей.
     */
    public function test_does_not_affect_other_users_words(): void
    {
        $otherUser = User::factory()->create();

        // Создаём явные samples, чтобы избежать совпадений
        Sample::factory()->create([
            'original' => 'sample1',
            'translated' => 'пример1',
            'language' => 'EN',
        ]);

        Sample::factory()->create([
            'original' => 'sample2',
            'translated' => 'пример2',
            'language' => 'EN',
        ]);

        // Создаём слово для другого пользователя
        Word::factory()->create([
            'user_id' => $otherUser->id,
            'original' => 'other_word',
            'translated' => 'другое слово',
            'language' => 'EN',
        ]);

        $token = $this->user->createToken('api-token')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson(route('api.v1.samples.import', ['language' => 'EN']));

        $response->assertStatus(201);

        // Проверяем, что у первого пользователя создались оба слова
        $this->assertCount(2, Word::where('user_id', $this->user->id)->where('language', 'EN')->get());

        // Проверяем, что у другого пользователя только его слово
        $this->assertCount(1, Word::where('user_id', $otherUser->id)->where('language', 'EN')->get());
    }

    /**
     * Тегистерности к регистру языка.
     */
    public function test_respects_language_case(): void
    {
        Sample::factory()->count(2)->create(['language' => 'EN']);

        $token = $this->user->createToken('api-token')->plainTextToken;

        // С маленькой буквой - должно вернуть 404
        $response = $this->withToken($token)
            ->postJson(route('api.v1.samples.import', ['language' => 'en']));

        $response->assertStatus(404);

        // С заглавной буквой - должно работать
        $response = $this->withToken($token)
            ->postJson(route('api.v1.samples.import', ['language' => 'EN']));

        $response->assertStatus(201);

        /** @var array{data: array{attributes: array{total_created: int}}} $responseData */
        $responseData = $response->json();
        $this->assertEquals(2, $responseData['data']['attributes']['total_created']);
    }

    /**
     * Тест, что созданные слова имеют правильные атрибуты.
     */
    public function test_created_words_have_correct_attributes(): void
    {
        Sample::factory()->create([
            'original' => 'test',
            'translated' => 'тест',
            'language' => 'EN',
        ]);

        $token = $this->user->createToken('api-token')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson(route('api.v1.samples.import', ['language' => 'EN']));

        $response->assertStatus(201);

        /** @var array{included: array<array{type: string, attributes: array<string, mixed>}>} $responseData */
        $responseData = $response->json();
        $wordData = $responseData['included'][0];

        $this->assertEquals('words', $wordData['type']);
        $this->assertEquals('test', $wordData['attributes']['original']);
        $this->assertEquals('тест', $wordData['attributes']['translated']);
        $this->assertEquals('EN', $wordData['attributes']['language']);
        $this->assertTrue($wordData['attributes']['from_sample']);
        $this->assertFalse($wordData['attributes']['starred']);
        $this->assertEquals(0, $wordData['attributes']['views']);

        // Проверяем в базе данных
        $word = Word::where('user_id', $this->user->id)
            ->where('original', 'test')
            ->first();

        $this->assertNotNull($word);
        $this->assertTrue($word->from_sample);
        $this->assertFalse($word->starred);
        $this->assertEquals(0, $word->views);
    }
}
