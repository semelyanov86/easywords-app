<?php

declare(strict_types=1);

namespace Tests\Feature\Actions;

use App\Actions\GenerateWordExamples;
use App\Contracts\WordExampleGenerator;
use App\Models\User;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mockery\MockInterface;

final class GenerateWordExamplesTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_existing_examples_if_present(): void
    {
        // Arrange
        $user = User::factory()->create();
        $word = Word::factory()->for($user)->create([
            'example_original' => ['Example 1', 'Example 2', 'Example 3'],
            'example_translated' => ['Пример 1', 'Пример 2', 'Пример 3'],
        ]);

        /** @var WordExampleGenerator&MockInterface $connector */
        $connector = Mockery::mock(WordExampleGenerator::class);
        $connector->shouldNotReceive('generateWordExamples');

        $action = new GenerateWordExamples($connector);

        // Act
        $result = $action->handle($word->id, $user->id);

        // Assert
        $this->assertEquals(['Example 1', 'Example 2', 'Example 3'], $result->example_original);
        $this->assertEquals(['Пример 1', 'Пример 2', 'Пример 3'], $result->example_translated);
    }

    public function test_generates_and_saves_new_examples(): void
    {
        // Arrange
        $user = User::factory()->create();
        $word = Word::factory()->for($user)->create([
            'original' => 'test',
            'language' => 'en',
            'example_original' => null,
            'example_translated' => null,
        ]);

        $mockExamples = [
            'example_original' => [
                'Literature example',
                'Aphorism example',
                'Conversational example',
            ],
            'example_translated' => [
                'Литературный пример',
                'Афоризм пример',
                'Разговорный пример',
            ],
        ];

        /** @var WordExampleGenerator&MockInterface $connector */
        $connector = Mockery::mock(WordExampleGenerator::class);
        $connector->shouldReceive('generateWordExamples')
            ->once()
            ->with('test', 'en')
            ->andReturn($mockExamples);

        $action = new GenerateWordExamples($connector);

        // Act
        $result = $action->handle($word->id, $user->id);

        // Assert
        $this->assertEquals($mockExamples['example_original'], $result->example_original);
        $this->assertEquals($mockExamples['example_translated'], $result->example_translated);

        // Verify database update
        $word->refresh();
        $this->assertEquals($mockExamples['example_original'], $word->example_original);
        $this->assertEquals($mockExamples['example_translated'], $word->example_translated);
    }

    public function test_throws_exception_for_word_not_found(): void
    {
        // Arrange
        $user = User::factory()->create();

        /** @var WordExampleGenerator&MockInterface $connector */
        $connector = Mockery::mock(WordExampleGenerator::class);
        $connector->shouldNotReceive('generateWordExamples');

        $action = new GenerateWordExamples($connector);

        // Expect
        $this->expectException(ModelNotFoundException::class);

        // Act
        $action->handle(999, $user->id);
    }

    public function test_throws_exception_for_word_belonging_to_other_user(): void
    {
        // Arrange
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $word = Word::factory()->for($user1)->create();

        /** @var WordExampleGenerator&MockInterface $connector */
        $connector = Mockery::mock(WordExampleGenerator::class);
        $connector->shouldNotReceive('generateWordExamples');

        $action = new GenerateWordExamples($connector);

        // Expect
        $this->expectException(ModelNotFoundException::class);

        // Act
        $action->handle($word->id, $user2->id);
    }

    public function test_validates_example_structure(): void
    {
        // Arrange
        $user = User::factory()->create();
        $word = Word::factory()->for($user)->create([
            'original' => 'test',
            'language' => 'en',
            'example_original' => null,
            'example_translated' => null,
        ]);

        $invalidExamples = [
            'example_original' => ['Only one example'],
            'example_translated' => ['Only one translation'],
        ];

        /** @var WordExampleGenerator&MockInterface $connector */
        $connector = Mockery::mock(WordExampleGenerator::class);
        $connector->shouldReceive('generateWordExamples')
            ->once()
            ->with('test', 'en')
            ->andReturn($invalidExamples);

        $action = new GenerateWordExamples($connector);

        // Expect
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid examples structure: each field must contain exactly 3 examples');

        // Act
        $action->handle($word->id, $user->id);
    }

    public function test_validates_non_empty_examples(): void
    {
        // Arrange
        $user = User::factory()->create();
        $word = Word::factory()->for($user)->create([
            'original' => 'test',
            'language' => 'en',
            'example_original' => null,
            'example_translated' => null,
        ]);

        $invalidExamples = [
            'example_original' => ['', 'Valid', 'Valid'],
            'example_translated' => ['Valid', 'Valid', 'Valid'],
        ];

        /** @var WordExampleGenerator&MockInterface $connector */
        $connector = Mockery::mock(WordExampleGenerator::class);
        $connector->shouldReceive('generateWordExamples')
            ->once()
            ->with('test', 'en')
            ->andReturn($invalidExamples);

        $action = new GenerateWordExamples($connector);

        // Expect
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid example_original[0]: must be non-empty string');

        // Act
        $action->handle($word->id, $user->id);
    }

    public function test_validates_missing_fields(): void
    {
        // Arrange
        $user = User::factory()->create();
        $word = Word::factory()->for($user)->create([
            'original' => 'test',
            'language' => 'en',
            'example_original' => null,
            'example_translated' => null,
        ]);

        $invalidExamples = [
            'example_original' => ['Valid', 'Valid', 'Valid'],
        ];

        /** @var WordExampleGenerator&MockInterface $connector */
        $connector = Mockery::mock(WordExampleGenerator::class);
        $connector->shouldReceive('generateWordExamples')
            ->once()
            ->with('test', 'en')
            ->andReturn($invalidExamples);

        $action = new GenerateWordExamples($connector);

        // Expect
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid examples structure: missing required fields');

        // Act
        $action->handle($word->id, $user->id);
    }
}
