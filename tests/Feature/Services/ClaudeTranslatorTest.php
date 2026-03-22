<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Services\ClaudeApiClient;
use App\Services\ClaudeTranslator;
use Mockery;
use Tests\TestCase;
use Mockery\MockInterface;

final class ClaudeTranslatorTest extends TestCase
{
    public function test_translates_word_via_claude_api_client(): void
    {
        /** @var ClaudeApiClient&MockInterface $mockClient */
        $mockClient = Mockery::mock(ClaudeApiClient::class);
        $mockClient->shouldReceive('sendJsonPrompt')
            ->once() // @phpstan-ignore method.notFound
            ->andReturn(['result' => 'автомобиль']); // @phpstan-ignore method.nonObject

        $translator = new ClaudeTranslator($mockClient);

        $result = $translator->translate('car', 'en');

        $this->assertEquals('автомобиль', $result);
    }

    public function test_prompt_contains_word_and_language(): void
    {
        /** @var ClaudeApiClient&MockInterface $mockClient */
        $mockClient = Mockery::mock(ClaudeApiClient::class);
        $mockClient->shouldReceive('sendJsonPrompt')
            ->once() // @phpstan-ignore method.notFound
            ->withArgs(fn (string $prompt): bool => str_contains($prompt, '"Haus"') // @phpstan-ignore method.nonObject
                    && str_contains($prompt, 'с de языка')
                    && str_contains($prompt, '/language-translate'))
            ->andReturn(['result' => 'дом']); // @phpstan-ignore method.nonObject

        $translator = new ClaudeTranslator($mockClient);

        $result = $translator->translate('Haus', 'de');

        $this->assertEquals('дом', $result);
    }

    public function test_trims_whitespace_from_result(): void
    {
        /** @var ClaudeApiClient&MockInterface $mockClient */
        $mockClient = Mockery::mock(ClaudeApiClient::class);
        $mockClient->shouldReceive('sendJsonPrompt')
            ->once() // @phpstan-ignore method.notFound
            ->andReturn(['result' => '  перевод  ']); // @phpstan-ignore method.nonObject

        $translator = new ClaudeTranslator($mockClient);

        $result = $translator->translate('word', 'en');

        $this->assertEquals('перевод', $result);
    }
}
