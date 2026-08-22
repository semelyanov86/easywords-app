<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Services\OpenAiImageWordExtractor;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Illuminate\Http\Client\RequestException;

final class OpenAiImageWordExtractorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.openai.key' => 'test-key',
            'services.openai.url' => 'https://api.openai.test',
            'services.openai.model' => 'test-model',
            'services.openai.temperature' => 0.3,
            'services.openai.timeout' => 30,
        ]);
    }

    public function test_extracts_words_from_image(): void
    {
        Http::fake([
            'api.openai.test/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'words' => [
                                    ['original' => 'hello', 'translation' => 'привет', 'language' => 'en'],
                                    ['original' => 'world', 'translation' => 'мир', 'language' => 'en'],
                                ],
                            ]),
                        ],
                    ],
                ],
            ]),
        ]);

        $image = UploadedFile::fake()->image('test.jpg');
        $extractor = new OpenAiImageWordExtractor();

        $result = $extractor->extractWords($image, 'en');

        $this->assertCount(2, $result);
        $this->assertEquals('hello', $result[0]['original']);
        $this->assertEquals('привет', $result[0]['translation']);
        $this->assertEquals('en', $result[0]['language']);
    }

    public function test_sends_correct_request_to_openai(): void
    {
        Http::fake([
            'api.openai.test/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'words' => [
                                    ['original' => 'test', 'translation' => 'тест', 'language' => 'en'],
                                ],
                            ]),
                        ],
                    ],
                ],
            ]),
        ]);

        $image = UploadedFile::fake()->image('test.jpg');
        $extractor = new OpenAiImageWordExtractor();

        $extractor->extractWords($image, 'en', 'ru');

        Http::assertSent(function (Request $request): bool {
            $body = $request->body();

            return str_contains($request->url(), '/chat/completions')
                && $request->hasHeader('Authorization', 'Bearer test-key')
                && str_contains($body, 'test-model')
                && str_contains($body, 'system')
                && str_contains($body, 'user')
                && str_contains($body, 'image_url')
                && str_contains($body, 'data:');
        });
    }

    public function test_uses_custom_target_language(): void
    {
        Http::fake([
            'api.openai.test/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'words' => [
                                    ['original' => 'hallo', 'translation' => 'hello', 'language' => 'de'],
                                ],
                            ]),
                        ],
                    ],
                ],
            ]),
        ]);

        $image = UploadedFile::fake()->image('test.jpg');
        $extractor = new OpenAiImageWordExtractor();

        $extractor->extractWords($image, 'de', 'en');

        Http::assertSent(fn (Request $request): bool => str_contains($request->url(), '/chat/completions')
                && str_contains($request->body(), 'de')
                && str_contains($request->body(), 'en'));
    }

    public function test_throws_exception_on_api_error(): void
    {
        Http::fake([
            'api.openai.test/chat/completions' => Http::response(status: 500),
        ]);

        $image = UploadedFile::fake()->image('test.jpg');
        $extractor = new OpenAiImageWordExtractor();

        $this->expectException(RequestException::class);

        $extractor->extractWords($image, 'en');
    }

    public function test_throws_exception_on_invalid_response_format(): void
    {
        Http::fake([
            'api.openai.test/chat/completions' => Http::response([
                'choices' => [],
            ]),
        ]);

        $image = UploadedFile::fake()->image('test.jpg');
        $extractor = new OpenAiImageWordExtractor();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageIsOrContains('Invalid response format from OpenAI API');

        $extractor->extractWords($image, 'en');
    }

    public function test_converts_image_to_base64(): void
    {
        Http::fake([
            'api.openai.test/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'words' => [
                                    ['original' => 'test', 'translation' => 'тест', 'language' => 'en'],
                                ],
                            ]),
                        ],
                    ],
                ],
            ]),
        ]);

        $image = UploadedFile::fake()->image('photo.png');
        $extractor = new OpenAiImageWordExtractor();

        $extractor->extractWords($image, 'en');

        Http::assertSent(function (Request $request): bool {
            $body = $request->body();

            return str_contains($body, 'data:') && str_contains($body, ';base64,');
        });
    }
}
