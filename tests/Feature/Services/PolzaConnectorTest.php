<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Services\PolzaApiClient;
use App\Services\PolzaConnector;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class PolzaConnectorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.polza.models.examples' => 'examples-model',
            'services.polza.temperature.examples' => null,
        ]);
    }

    public function test_generates_and_decodes_word_examples(): void
    {
        /** @var PolzaApiClient&MockInterface $mockClient */
        $mockClient = Mockery::mock(PolzaApiClient::class);
        $mockClient->shouldReceive('chat')
            ->once() // @phpstan-ignore method.notFound
            ->andReturn(json_encode([ // @phpstan-ignore method.nonObject
                'example_original' => ['one', 'two', 'three'],
                'example_translated' => ['один', 'два', 'три'],
            ], JSON_THROW_ON_ERROR));

        $result = new PolzaConnector($mockClient)->generateWordExamples('word', 'en');

        $this->assertSame(['one', 'two', 'three'], $result['example_original']);
        $this->assertSame(['один', 'два', 'три'], $result['example_translated']);
    }

    public function test_requests_json_schema_with_configured_model(): void
    {
        /** @var PolzaApiClient&MockInterface $mockClient */
        $mockClient = Mockery::mock(PolzaApiClient::class);
        $mockClient->shouldReceive('chat')
            ->once() // @phpstan-ignore method.notFound
            ->andReturnUsing(function (string $model, array $messages, ?array $responseFormat, float|int|string|null $temperature): string { // @phpstan-ignore method.nonObject
                $this->assertSame('examples-model', $model);
                $this->assertNull($temperature);

                /** @var array{type: string, json_schema: array{name: string}} $responseFormat */
                $this->assertSame('json_schema', $responseFormat['type']);
                $this->assertSame('word_examples', $responseFormat['json_schema']['name']);

                /** @var array<int, array{role: string, content: string}> $messages */
                $this->assertStringContainsString('Buch', $messages[1]['content']);

                return json_encode([
                    'example_original' => ['a', 'b', 'c'],
                    'example_translated' => ['а', 'б', 'в'],
                ], JSON_THROW_ON_ERROR);
            });

        new PolzaConnector($mockClient)->generateWordExamples('Buch', 'de');
    }
}
