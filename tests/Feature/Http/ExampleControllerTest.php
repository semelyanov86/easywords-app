<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Actions\GenerateWordExamples;
use App\Models\User;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Data\ExampleData;
use Mockery\MockInterface;

final class ExampleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_examples_for_authenticated_user(): void
    {
        // Arrange
        $user = User::factory()->create();
        $word = Word::factory()->for($user)->create([
            'example_original' => ['Example 1', 'Example 2', 'Example 3'],
            'example_translated' => ['Пример 1', 'Пример 2', 'Пример 3'],
        ]);

        Sanctum::actingAs($user);

        // Act
        $response = $this->getJson(route('api.v1.examples.show', ['word' => $word->id]));

        // Assert
        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonStructure([
                'data' => [
                    'type',
                    'attributes' => [
                        'example_original',
                        'example_translated',
                    ],
                ],
            ])
            ->assertJson([
                'data' => [
                    'type' => 'examples',
                    'attributes' => [
                        'example_original' => ['Example 1', 'Example 2', 'Example 3'],
                        'example_translated' => ['Пример 1', 'Пример 2', 'Пример 3'],
                    ],
                ],
            ]);
    }

    public function test_generates_examples_when_missing(): void
    {
        // Arrange
        $user = User::factory()->create();
        $word = Word::factory()->for($user)->create([
            'example_original' => null,
            'example_translated' => null,
        ]);

        Sanctum::actingAs($user);

        $mockExamples = [
            'example_original' => ['New example 1', 'New example 2', 'New example 3'],
            'example_translated' => ['Новый пример 1', 'Новый пример 2', 'Новый пример 3'],
        ];

        $this->app->instance(
            GenerateWordExamples::class,
            Mockery::mock(GenerateWordExamples::class, function ($mock) use ($word, $user, $mockExamples): void {
                /** @var GenerateWordExamples&MockInterface $mockInstance */
                $mockInstance = $mock;
                $mockInstance->shouldReceive('handle')
                    ->once()
                    ->with($word->id, $user->id)
                    ->andReturn(new ExampleData(
                        id: '112',
                        example_original: $mockExamples['example_original'],
                        example_translated: $mockExamples['example_translated']
                    ));
            })
        );

        // Act
        $response = $this->getJson(route('api.v1.examples.show', ['word' => $word->id]));

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'type' => 'examples',
                    'attributes' => [
                        'example_original' => $mockExamples['example_original'],
                        'example_translated' => $mockExamples['example_translated'],
                    ],
                ],
            ]);
    }

    public function test_returns_not_found_for_non_existent_word(): void
    {
        // Arrange
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Act
        $response = $this->getJson(route('api.v1.examples.show', ['word' => 999]));

        // Assert
        $response->assertStatus(404)
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJson([
                'errors' => [
                    [
                        'status' => '404',
                        'title' => 'Not Found',
                        'detail' => 'Word not found or does not belong to user',
                    ],
                ],
            ]);
    }

    public function test_returns_not_found_for_word_belonging_to_other_user(): void
    {
        // Arrange
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $word = Word::factory()->for($user1)->create();

        Sanctum::actingAs($user2);

        // Act
        $response = $this->getJson(route('api.v1.examples.show', ['word' => $word->id]));

        // Assert
        $response->assertStatus(404)
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJson([
                'errors' => [
                    [
                        'status' => '404',
                        'title' => 'Not Found',
                        'detail' => 'Word not found or does not belong to user',
                    ],
                ],
            ]);
    }

    public function test_returns_internal_server_error_on_runtime_exception(): void
    {
        // Arrange
        $user = User::factory()->create();
        $word = Word::factory()->for($user)->create([
            'example_original' => null,
            'example_translated' => null,
        ]);

        Sanctum::actingAs($user);

        $this->app->instance(
            GenerateWordExamples::class,
            Mockery::mock(GenerateWordExamples::class, function ($mock) use ($word, $user): void {
                /** @var GenerateWordExamples&MockInterface $mockInstance */
                $mockInstance = $mock;
                $mockInstance->shouldReceive('handle')
                    ->once()
                    ->with($word->id, $user->id)
                    ->andThrow(new \RuntimeException('Test error'));
            })
        );

        // Act
        $response = $this->getJson(route('api.v1.examples.show', ['word' => $word->id]));

        // Assert
        $response->assertStatus(500)
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJson([
                'errors' => [
                    [
                        'status' => '500',
                        'title' => 'Internal Server Error',
                        'detail' => 'Test error',
                    ],
                ],
            ]);
    }
}
