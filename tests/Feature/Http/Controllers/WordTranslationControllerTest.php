<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Contracts\WordTranslator;
use App\Models\User;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Mockery\MockInterface;
use Tests\TestCase;

final class WordTranslationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_translation_for_authenticated_user(): void
    {
        // Arrange
        $user = User::factory()->create();
        Word::factory()->for($user)->create([
            'original' => 'hello',
            'language' => 'en',
            'translated' => 'тестовый перевод',
        ]);

        Sanctum::actingAs($user);

        // Act
        $response = $this->getJson(route('api.v1.translate', [
            'word' => 'hello',
            'language' => 'en',
        ]));

        // Assert
        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJson([
                'data' => [
                    'type' => 'word-translations',
                    'id' => 'temp',
                    'attributes' => [
                        'translation' => 'тестовый перевод',
                    ],
                ],
            ]);
    }

    public function test_returns_translation_from_database(): void
    {
        // Arrange
        $user = User::factory()->create();
        Word::factory()->for($user)->create([
            'original' => 'hello',
            'language' => 'en',
            'translated' => 'привет',
        ]);

        Sanctum::actingAs($user);

        // Act
        $response = $this->getJson(route('api.v1.translate', [
            'word' => 'hello',
            'language' => 'en',
        ]));

        // Assert
        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJson([
                'data' => [
                    'type' => 'word-translations',
                    'id' => 'temp',
                    'attributes' => [
                        'translation' => 'привет',
                    ],
                ],
            ]);
    }

    public function test_returns_unauthorized_for_unauthenticated_user(): void
    {
        // Arrange - не вызываем Sanctum::actingAs

        // Act
        $response = $this->getJson(route('api.v1.translate', [
            'word' => 'hello',
            'language' => 'en',
        ]));

        // Assert
        $response->assertStatus(401);
    }

    public function test_returns_validation_error_when_word_is_missing(): void
    {
        // Arrange
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Act
        $response = $this->getJson(route('api.v1.translate', [
            'language' => 'en',
        ]));

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['word']);
    }

    public function test_returns_validation_error_when_language_is_missing(): void
    {
        // Arrange
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Act
        $response = $this->getJson(route('api.v1.translate', [
            'word' => 'hello',
        ]));

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['language']);
    }

    public function test_returns_validation_error_when_language_is_invalid_size(): void
    {
        // Arrange
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Act
        $response = $this->getJson(route('api.v1.translate', [
            'word' => 'hello',
            'language' => 'eng',
        ]));

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['language']);
    }

    public function test_returns_validation_error_when_word_exceeds_max_length(): void
    {
        // Arrange
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $longWord = str_repeat('a', 256);

        // Act
        $response = $this->getJson(route('api.v1.translate', [
            'word' => $longWord,
            'language' => 'en',
        ]));

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['word']);
    }

    public function test_returns_translation_from_translator_when_not_in_database(): void
    {
        // Arrange
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->mock(WordTranslator::class, function (MockInterface $mock): void {
            $mock->shouldReceive('translate') // @phpstan-ignore-line
                ->once()
                ->with('nonexistent', 'en')
                ->andReturn('перевод');
        });

        // Act
        $response = $this->getJson(route('api.v1.translate', [
            'word' => 'nonexistent',
            'language' => 'en',
        ]));

        // Assert
        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJson([
                'data' => [
                    'type' => 'word-translations',
                    'id' => 'temp',
                    'attributes' => [
                        'translation' => 'перевод',
                    ],
                ],
            ]);
    }

    public function test_returns_error_on_translator_failure(): void
    {
        // Arrange
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->mock(WordTranslator::class, function (MockInterface $mock): void {
            $mock->shouldReceive('translate') // @phpstan-ignore-line
                ->once()
                ->andThrow(new \RuntimeException('API error'));
        });

        // Act
        $response = $this->getJson(route('api.v1.translate', [
            'word' => 'nonexistent',
            'language' => 'en',
        ]));

        // Assert
        $response->assertStatus(500);
    }

    public function test_handles_german_language(): void
    {
        // Arrange
        $user = User::factory()->create();
        Word::factory()->for($user)->create([
            'original' => 'hallo',
            'language' => 'de',
            'translated' => 'привет',
        ]);

        Sanctum::actingAs($user);

        // Act
        $response = $this->getJson(route('api.v1.translate', [
            'word' => 'hallo',
            'language' => 'de',
        ]));

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'type' => 'word-translations',
                    'attributes' => [
                        'translation' => 'привет',
                    ],
                ],
            ]);
    }

    public function test_follows_json_api_specification(): void
    {
        // Arrange
        $user = User::factory()->create();
        Word::factory()->for($user)->create([
            'original' => 'test',
            'language' => 'en',
            'translated' => 'перевод',
        ]);

        Sanctum::actingAs($user);

        // Act
        $response = $this->getJson(route('api.v1.translate', [
            'word' => 'test',
            'language' => 'en',
        ]));

        // Assert - проверяем JSON:API структуру
        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonStructure([
                'data' => [
                    'type',
                    'id',
                    'attributes',
                ],
            ])
            ->assertJsonMissing(['errors', 'included', 'meta', 'links']);
    }

    public function test_truncates_long_translations_to_100_characters(): void
    {
        // Arrange
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $longTranslation = str_repeat('очень длинный перевод ', 5);

        $this->mock(WordTranslator::class, function (MockInterface $mock) use ($longTranslation): void {
            $mock->shouldReceive('translate') // @phpstan-ignore-line
                ->once()
                ->andReturn($longTranslation);
        });

        // Act
        $response = $this->getJson(route('api.v1.translate', [
            'word' => 'longword',
            'language' => 'en',
        ]));

        // Assert
        $response->assertStatus(200);
        /** @var string $data */
        $data = $response->json('data.attributes.translation');
        $this->assertLessThanOrEqual(100, mb_strlen($data));
    }
}
