<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Services\ClaudeApiClient;
use App\Services\ClaudeImageWordExtractor;
use Illuminate\Http\UploadedFile;
use Mockery;
use Tests\TestCase;
use Mockery\MockInterface;

final class ClaudeImageWordExtractorTest extends TestCase
{
    public function test_extracts_words_via_claude_api_client(): void
    {
        $expectedWords = [
            ['original' => 'hello', 'translation' => 'привет', 'language' => 'en'],
            ['original' => 'world', 'translation' => 'мир', 'language' => 'en'],
        ];

        $image = UploadedFile::fake()->image('test.jpg');

        /** @var ClaudeApiClient&MockInterface $mockClient */
        $mockClient = Mockery::mock(ClaudeApiClient::class);
        $mockClient->shouldReceive('sendJsonPromptWithFile')
            ->once() // @phpstan-ignore method.notFound
            ->withArgs(fn (string $prompt, UploadedFile $file): bool => str_contains($prompt, '/language-image-words') // @phpstan-ignore method.nonObject
                    && $file === $image)
            ->andReturn(['words' => $expectedWords]); // @phpstan-ignore method.nonObject

        $extractor = new ClaudeImageWordExtractor($mockClient);

        $result = $extractor->extractWords($image, 'en');

        $this->assertEquals($expectedWords, $result);
    }

    public function test_prompt_contains_language_image_words_prefix(): void
    {
        $image = UploadedFile::fake()->image('photo.png');

        /** @var ClaudeApiClient&MockInterface $mockClient */
        $mockClient = Mockery::mock(ClaudeApiClient::class);
        $mockClient->shouldReceive('sendJsonPromptWithFile')
            ->once() // @phpstan-ignore method.notFound
            ->withArgs(fn (string $prompt): bool => str_starts_with($prompt, '/language-image-words')) // @phpstan-ignore method.nonObject
            ->andReturn(['words' => []]); // @phpstan-ignore method.nonObject

        $extractor = new ClaudeImageWordExtractor($mockClient);

        $extractor->extractWords($image, 'de', 'en');
    }

    public function test_passes_image_file_to_api_client(): void
    {
        $image = UploadedFile::fake()->image('test.jpg');

        /** @var ClaudeApiClient&MockInterface $mockClient */
        $mockClient = Mockery::mock(ClaudeApiClient::class);
        $mockClient->shouldReceive('sendJsonPromptWithFile')
            ->once() // @phpstan-ignore method.notFound
            ->withArgs(fn (string $prompt, UploadedFile $file): bool => $file === $image) // @phpstan-ignore method.nonObject
            ->andReturn(['words' => [ // @phpstan-ignore method.nonObject
                ['original' => 'test', 'translation' => 'тест', 'language' => 'en'],
            ]]);

        $extractor = new ClaudeImageWordExtractor($mockClient);

        $result = $extractor->extractWords($image, 'en');

        $this->assertCount(1, $result);
        $this->assertEquals('test', $result[0]['original']);
    }
}
