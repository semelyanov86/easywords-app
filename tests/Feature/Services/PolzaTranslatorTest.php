<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Services\PolzaApiClient;
use App\Services\PolzaTranslator;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class PolzaTranslatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.polza.models.translate' => 'translate-model',
            'services.polza.temperature.translate' => '0.3',
        ]);
    }

    public function test_translates_word_and_trims_result(): void
    {
        /** @var PolzaApiClient&MockInterface $mockClient */
        $mockClient = Mockery::mock(PolzaApiClient::class);
        $mockClient->shouldReceive('chat')
            ->once()
            ->andReturn('  дом  ');

        $translator = new PolzaTranslator($mockClient);

        $this->assertSame('дом', $translator->translate('Haus', 'de'));
    }

    public function test_uses_configured_model_and_prompts(): void
    {
        /** @var PolzaApiClient&MockInterface $mockClient */
        $mockClient = Mockery::mock(PolzaApiClient::class);
        $mockClient->shouldReceive('chat')
            ->once()
            ->andReturnUsing(function (string $model, array $messages, ?array $responseFormat, float|int|string|null $temperature): string {
                $this->assertSame('translate-model', $model);
                $this->assertNull($responseFormat);
                $this->assertSame('0.3', $temperature);

                /** @var array<int, array{role: string, content: string}> $messages */
                $this->assertSame('system', $messages[0]['role']);
                $this->assertStringContainsString('с de языка', $messages[0]['content']);
                $this->assertSame('user', $messages[1]['role']);
                $this->assertStringContainsString('Haus', $messages[1]['content']);

                return 'дом';
            });

        new PolzaTranslator($mockClient)->translate('Haus', 'de');
    }
}
