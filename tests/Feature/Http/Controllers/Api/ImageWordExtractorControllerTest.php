<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Contracts\ImageWordExtractor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Tests\TestCase;

final class ImageWordExtractorControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_extracted_words_for_authenticated_user(): void
    {
        // Arrange
        $user = User::factory()->create([
            'has_premium' => true,
        ]);
        Sanctum::actingAs($user);

        $image = UploadedFile::fake()->image('test.jpg');

        /** @var ImageWordExtractor&\Mockery\MockInterface $mockExtractor */
        $mockExtractor = Mockery::mock(ImageWordExtractor::class);
        $mockExtractor->shouldReceive('extractWords') // @phpstan-ignore-line
            ->once()
            ->andReturn([
                ['original' => 'hello', 'translation' => 'привет', 'language' => 'en'],
                ['original' => 'world', 'translation' => 'мир', 'language' => 'en'],
            ]);

        $this->app->instance(ImageWordExtractor::class, $mockExtractor);

        // Act
        $response = $this->postJson(route('api.v1.images.extract-words'), [
            'image' => $image,
            'language' => 'en',
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'type',
                        'id',
                        'attributes' => [
                            'original',
                            'translation',
                            'language',
                        ],
                    ],
                ],
            ])
            ->assertJsonCount(2, 'data')
            ->assertJson([
                'data' => [
                    [
                        'type' => 'extracted-words',
                        'attributes' => [
                            'original' => 'hello',
                            'translation' => 'привет',
                            'language' => 'en',
                        ],
                    ],
                    [
                        'type' => 'extracted-words',
                        'attributes' => [
                            'original' => 'world',
                            'translation' => 'мир',
                            'language' => 'en',
                        ],
                    ],
                ],
            ]);
    }

    public function test_returns_unauthorized_for_unauthenticated_user(): void
    {
        // Arrange - не вызываем Sanctum::actingAs

        $image = UploadedFile::fake()->image('test.jpg');

        // Act
        $response = $this->postJson(route('api.v1.images.extract-words'), [
            'image' => $image,
            'language' => 'en',
        ]);

        // Assert
        $response->assertStatus(401);
    }

    public function test_returns_validation_error_when_image_is_missing(): void
    {
        // Arrange
        $user = User::factory()->create([
            'has_premium' => true,
        ]);
        Sanctum::actingAs($user);

        // Act
        $response = $this->postJson(route('api.v1.images.extract-words'), [
            'language' => 'en',
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_returns_validation_error_when_language_is_missing(): void
    {
        // Arrange
        $user = User::factory()->create([
            'has_premium' => true,
        ]);
        Sanctum::actingAs($user);

        $image = UploadedFile::fake()->image('test.jpg');

        // Act
        $response = $this->postJson(route('api.v1.images.extract-words'), [
            'image' => $image,
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['language']);
    }

    public function test_returns_validation_error_when_language_is_invalid_size(): void
    {
        // Arrange
        $user = User::factory()->create([
            'has_premium' => true,
        ]);
        Sanctum::actingAs($user);

        $image = UploadedFile::fake()->image('test.jpg');

        // Act
        $response = $this->postJson(route('api.v1.images.extract-words'), [
            'image' => $image,
            'language' => 'eng',
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['language']);
    }

    public function test_returns_validation_error_when_target_language_is_invalid_size(): void
    {
        // Arrange
        $user = User::factory()->create([
            'has_premium' => true,
        ]);
        Sanctum::actingAs($user);

        $image = UploadedFile::fake()->image('test.jpg');

        // Act
        $response = $this->postJson(route('api.v1.images.extract-words'), [
            'image' => $image,
            'language' => 'en',
            'target_language' => 'rus',
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['target_language']);
    }

    public function test_returns_validation_error_when_file_is_not_image(): void
    {
        // Arrange
        $user = User::factory()->create([
            'has_premium' => true,
        ]);
        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->create('document.pdf', 1024);

        // Act
        $response = $this->postJson(route('api.v1.images.extract-words'), [
            'image' => $file,
            'language' => 'en',
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_returns_validation_error_when_image_exceeds_max_size(): void
    {
        // Arrange
        $user = User::factory()->create([
            'has_premium' => true,
        ]);
        Sanctum::actingAs($user);

        // 11 MB - превышает лимит в 10MB
        $image = UploadedFile::fake()->create('test.jpg', 11264);

        // Act
        $response = $this->postJson(route('api.v1.images.extract-words'), [
            'image' => $image,
            'language' => 'en',
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_uses_default_target_language_when_not_provided(): void
    {
        // Arrange
        $user = User::factory()->create([
            'has_premium' => true,
        ]);
        Sanctum::actingAs($user);

        /** @var ImageWordExtractor&\Mockery\MockInterface $mockExtractor */
        $mockExtractor = Mockery::mock(ImageWordExtractor::class);
        $mockExtractor->shouldReceive('extractWords') // @phpstan-ignore-line
            ->once()
            ->andReturn([
                ['original' => 'test', 'translation' => 'тест', 'language' => 'en'],
            ]);

        $this->app->instance(ImageWordExtractor::class, $mockExtractor);

        $image = UploadedFile::fake()->image('test.jpg');

        // Act
        $response = $this->postJson(route('api.v1.images.extract-words'), [
            'image' => $image,
            'language' => 'en',
        ]);

        // Assert
        $response->assertStatus(200);
    }

    public function test_handles_german_language(): void
    {
        // Arrange
        $user = User::factory()->create([
            'has_premium' => true,
        ]);
        Sanctum::actingAs($user);

        /** @var ImageWordExtractor&\Mockery\MockInterface $mockExtractor */
        $mockExtractor = Mockery::mock(ImageWordExtractor::class);
        $mockExtractor->shouldReceive('extractWords') // @phpstan-ignore-line
            ->once()
            ->andReturn([
                ['original' => 'hallo', 'translation' => 'привет', 'language' => 'de'],
            ]);

        $this->app->instance(ImageWordExtractor::class, $mockExtractor);

        $image = UploadedFile::fake()->image('test.jpg');

        // Act
        $response = $this->postJson(route('api.v1.images.extract-words'), [
            'image' => $image,
            'language' => 'de',
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'type' => 'extracted-words',
                        'attributes' => [
                            'original' => 'hallo',
                            'translation' => 'привет',
                            'language' => 'de',
                        ],
                    ],
                ],
            ]);
    }

    public function test_follows_json_api_specification(): void
    {
        // Arrange
        $user = User::factory()->create([
            'has_premium' => true,
        ]);
        Sanctum::actingAs($user);

        /** @var ImageWordExtractor&\Mockery\MockInterface $mockExtractor */
        $mockExtractor = Mockery::mock(ImageWordExtractor::class);
        $mockExtractor->shouldReceive('extractWords') // @phpstan-ignore-line
            ->once()
            ->andReturn([
                ['original' => 'test', 'translation' => 'тест', 'language' => 'en'],
            ]);

        $this->app->instance(ImageWordExtractor::class, $mockExtractor);

        $image = UploadedFile::fake()->image('test.jpg');

        // Act
        $response = $this->postJson(route('api.v1.images.extract-words'), [
            'image' => $image,
            'language' => 'en',
        ]);

        // Assert - проверяем JSON:API структуру
        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'type',
                        'id',
                        'attributes',
                    ],
                ],
            ])
            ->assertJsonMissing(['errors', 'included', 'meta', 'links']);
    }
}
