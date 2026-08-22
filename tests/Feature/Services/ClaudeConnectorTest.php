<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Services\ClaudeApiClient;
use App\Services\ClaudeConnector;
use Mockery;
use Tests\TestCase;
use Mockery\MockInterface;

final class ClaudeConnectorTest extends TestCase
{
    public function test_generates_word_examples_via_claude_api_client(): void
    {
        $expectedResult = [
            'example_original' => ['Literature example', 'Aphorism example', 'Conversational example'],
            'example_translated' => ['Литературный пример', 'Афоризм', 'Разговорный пример'],
        ];

        /** @var ClaudeApiClient&MockInterface $mockClient */
        $mockClient = Mockery::mock(ClaudeApiClient::class);
        $mockClient->shouldReceive('sendJsonPrompt')
            ->once()
            ->withArgs(fn (string $prompt): bool => str_contains($prompt, '"hello"')
                    && str_contains($prompt, 'en языке'))
            ->andReturn($expectedResult);

        $connector = new ClaudeConnector($mockClient);

        $result = $connector->generateWordExamples('hello', 'en');

        $this->assertEquals($expectedResult, $result);
    }

    public function test_prompt_contains_word_and_language(): void
    {
        /** @var ClaudeApiClient&MockInterface $mockClient */
        $mockClient = Mockery::mock(ClaudeApiClient::class);
        $mockClient->shouldReceive('sendJsonPrompt')
            ->once()
            ->withArgs(fn (string $prompt): bool => str_contains($prompt, '"Haus"')
                    && str_contains($prompt, 'de языке')
                    && str_contains($prompt, '/language-examples'))
            ->andReturn([
                'example_original' => ['a', 'b', 'c'],
                'example_translated' => ['а', 'б', 'в'],
            ]);

        $connector = new ClaudeConnector($mockClient);

        $connector->generateWordExamples('Haus', 'de');
    }
}
