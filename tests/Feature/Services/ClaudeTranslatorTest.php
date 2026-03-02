<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Services\ClaudeApiClient;
use App\Services\ClaudeTranslator;
use Mockery;
use Tests\TestCase;

final class ClaudeTranslatorTest extends TestCase
{
    public function test_translates_word_via_claude_api_client(): void
    {
        /** @var ClaudeApiClient&\Mockery\MockInterface $mockClient */
        $mockClient = Mockery::mock(ClaudeApiClient::class);
        $mockClient->shouldReceive('sendJsonPrompt') // @phpstan-ignore-line
            ->once()
            ->andReturn(['result' => 'автомобиль']);

        $translator = new ClaudeTranslator($mockClient);

        $result = $translator->translate('car', 'en');

        $this->assertEquals('автомобиль', $result);
    }

    public function test_prompt_contains_word_and_language(): void
    {
        /** @var ClaudeApiClient&\Mockery\MockInterface $mockClient */
        $mockClient = Mockery::mock(ClaudeApiClient::class);
        $mockClient->shouldReceive('sendJsonPrompt') // @phpstan-ignore-line
            ->once()
            ->withArgs(fn (string $prompt): bool => str_contains($prompt, '"Haus"')
                    && str_contains($prompt, 'с de языка')
                    && str_contains($prompt, '/language-translate'))
            ->andReturn(['result' => 'дом']);

        $translator = new ClaudeTranslator($mockClient);

        $result = $translator->translate('Haus', 'de');

        $this->assertEquals('дом', $result);
    }

    public function test_trims_whitespace_from_result(): void
    {
        /** @var ClaudeApiClient&\Mockery\MockInterface $mockClient */
        $mockClient = Mockery::mock(ClaudeApiClient::class);
        $mockClient->shouldReceive('sendJsonPrompt') // @phpstan-ignore-line
            ->once()
            ->andReturn(['result' => '  перевод  ']);

        $translator = new ClaudeTranslator($mockClient);

        $result = $translator->translate('word', 'en');

        $this->assertEquals('перевод', $result);
    }
}
