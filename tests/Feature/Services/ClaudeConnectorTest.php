<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Services\ClaudeApiClient;
use App\Services\ClaudeConnector;
use Mockery;
use Tests\TestCase;

final class ClaudeConnectorTest extends TestCase
{
    public function test_generates_word_examples_via_claude_api_client(): void
    {
        $expectedResult = [
            'example_original' => ['Literature example', 'Aphorism example', 'Conversational example'],
            'example_translated' => ['Литературный пример', 'Афоризм', 'Разговорный пример'],
        ];

        /** @var ClaudeApiClient&\Mockery\MockInterface $mockClient */
        $mockClient = Mockery::mock(ClaudeApiClient::class);
        $mockClient->shouldReceive('sendJsonPrompt')
            ->once() // @phpstan-ignore method.notFound
            ->withArgs(fn (string $prompt): bool => str_contains($prompt, '"hello"') // @phpstan-ignore method.nonObject
                    && str_contains($prompt, 'en языке'))
            ->andReturn($expectedResult); // @phpstan-ignore method.nonObject

        $connector = new ClaudeConnector($mockClient);

        $result = $connector->generateWordExamples('hello', 'en');

        $this->assertEquals($expectedResult, $result);
    }

    public function test_prompt_contains_word_and_language(): void
    {
        /** @var ClaudeApiClient&\Mockery\MockInterface $mockClient */
        $mockClient = Mockery::mock(ClaudeApiClient::class);
        $mockClient->shouldReceive('sendJsonPrompt')
            ->once() // @phpstan-ignore method.notFound
            ->withArgs(fn (string $prompt): bool => str_contains($prompt, '"Haus"') // @phpstan-ignore method.nonObject
                    && str_contains($prompt, 'de языке')
                    && str_contains($prompt, '/language-examples'))
            ->andReturn([ // @phpstan-ignore method.nonObject
                'example_original' => ['a', 'b', 'c'],
                'example_translated' => ['а', 'б', 'в'],
            ]);

        $connector = new ClaudeConnector($mockClient);

        $connector->generateWordExamples('Haus', 'de');
    }
}
