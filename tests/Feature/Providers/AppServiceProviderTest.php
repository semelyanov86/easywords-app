<?php

declare(strict_types=1);

namespace Tests\Feature\Providers;

use App\Contracts\ImageWordExtractor;
use App\Contracts\WordExampleGenerator;
use App\Contracts\WordTranslator;
use App\Services\ClaudeConnector;
use App\Services\ClaudeImageWordExtractor;
use App\Services\ClaudeTranslator;
use App\Services\OpenAiConnector;
use App\Services\OpenAiImageWordExtractor;
use App\Services\OpenAiTranslator;
use Tests\TestCase;

final class AppServiceProviderTest extends TestCase
{
    public function test_resolves_openai_connector_by_default(): void
    {
        config(['services.ai.provider' => 'openai']);

        $resolved = $this->app->make(WordExampleGenerator::class);

        $this->assertInstanceOf(OpenAiConnector::class, $resolved);
    }

    public function test_resolves_claude_connector_when_configured(): void
    {
        config([
            'services.ai.provider' => 'claude',
            'services.claude.url' => 'https://claude-proxy.test',
            'services.claude.key' => 'test-key',
            'services.claude.timeout' => 30,
        ]);

        $resolved = $this->app->make(WordExampleGenerator::class);

        $this->assertInstanceOf(ClaudeConnector::class, $resolved);
    }

    public function test_defaults_to_openai_for_unknown_provider(): void
    {
        config(['services.ai.provider' => 'unknown']);

        $resolved = $this->app->make(WordExampleGenerator::class);

        $this->assertInstanceOf(OpenAiConnector::class, $resolved);
    }

    public function test_resolves_openai_translator_by_default(): void
    {
        config(['services.ai.provider' => 'openai']);

        $resolved = $this->app->make(WordTranslator::class);

        $this->assertInstanceOf(OpenAiTranslator::class, $resolved);
    }

    public function test_resolves_claude_translator_when_configured(): void
    {
        config([
            'services.ai.provider' => 'claude',
            'services.claude.url' => 'https://claude-proxy.test',
            'services.claude.key' => 'test-key',
            'services.claude.timeout' => 30,
        ]);

        $resolved = $this->app->make(WordTranslator::class);

        $this->assertInstanceOf(ClaudeTranslator::class, $resolved);
    }

    public function test_defaults_to_openai_translator_for_unknown_provider(): void
    {
        config(['services.ai.provider' => 'unknown']);

        $resolved = $this->app->make(WordTranslator::class);

        $this->assertInstanceOf(OpenAiTranslator::class, $resolved);
    }

    public function test_resolves_openai_image_word_extractor_by_default(): void
    {
        config(['services.ai.provider' => 'openai']);

        $resolved = $this->app->make(ImageWordExtractor::class);

        $this->assertInstanceOf(OpenAiImageWordExtractor::class, $resolved);
    }

    public function test_resolves_claude_image_word_extractor_when_configured(): void
    {
        config([
            'services.ai.provider' => 'claude',
            'services.claude.url' => 'https://claude-proxy.test',
            'services.claude.key' => 'test-key',
            'services.claude.timeout' => 30,
        ]);

        $resolved = $this->app->make(ImageWordExtractor::class);

        $this->assertInstanceOf(ClaudeImageWordExtractor::class, $resolved);
    }

    public function test_defaults_to_openai_image_word_extractor_for_unknown_provider(): void
    {
        config(['services.ai.provider' => 'unknown']);

        $resolved = $this->app->make(ImageWordExtractor::class);

        $this->assertInstanceOf(OpenAiImageWordExtractor::class, $resolved);
    }
}
