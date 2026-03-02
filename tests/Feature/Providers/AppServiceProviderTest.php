<?php

declare(strict_types=1);

namespace Tests\Feature\Providers;

use App\Contracts\WordExampleGenerator;
use App\Services\ClaudeConnector;
use App\Services\OpenAiConnector;
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
}
