<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Services\PolzaApiClient;
use App\Services\PolzaImageWordExtractor;
use Illuminate\Http\UploadedFile;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class PolzaImageWordExtractorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.polza.models.image' => 'image-model',
            'services.polza.temperature.image' => '0.2',
        ]);
    }

    public function test_extracts_words_from_image(): void
    {
        /** @var PolzaApiClient&MockInterface $mockClient */
        $mockClient = Mockery::mock(PolzaApiClient::class);
        $mockClient->shouldReceive('chat')
            ->once() // @phpstan-ignore method.notFound
            ->andReturn(json_encode([ // @phpstan-ignore method.nonObject
                'words' => [
                    ['original' => 'Haus', 'translation' => 'дом', 'language' => 'de'],
                    ['original' => 'Buch', 'translation' => 'книга', 'language' => 'de'],
                ],
            ], JSON_THROW_ON_ERROR));

        $result = new PolzaImageWordExtractor($mockClient)
            ->extractWords(UploadedFile::fake()->image('photo.jpg'), 'de');

        $this->assertCount(2, $result);
        $this->assertSame('Haus', $result[0]['original']);
        $this->assertSame('дом', $result[0]['translation']);
        $this->assertSame('de', $result[0]['language']);
    }

    public function test_sends_image_as_base64_with_configured_model(): void
    {
        /** @var PolzaApiClient&MockInterface $mockClient */
        $mockClient = Mockery::mock(PolzaApiClient::class);
        $mockClient->shouldReceive('chat')
            ->once() // @phpstan-ignore method.notFound
            ->andReturnUsing(function (string $model, array $messages, ?array $responseFormat, float|int|string|null $temperature): string { // @phpstan-ignore method.nonObject
                $this->assertSame('image-model', $model);
                $this->assertSame('0.2', $temperature);

                /** @var array{json_schema: array{name: string}} $responseFormat */
                $this->assertSame('extracted_words', $responseFormat['json_schema']['name']);

                /** @var array<int, array<string, mixed>> $messages */
                $userContent = $messages[1]['content'];
                /** @var array<int, array{type: string, image_url: array{url: string}}> $userContent */
                $imageContent = $userContent[1];
                $this->assertSame('image_url', $imageContent['type']);
                $this->assertStringStartsWith('data:', $imageContent['image_url']['url']);
                $this->assertStringContainsString(';base64,', $imageContent['image_url']['url']);

                return json_encode(['words' => []], JSON_THROW_ON_ERROR);
            });

        new PolzaImageWordExtractor($mockClient)
            ->extractWords(UploadedFile::fake()->image('photo.png'), 'en', 'ru');
    }
}
