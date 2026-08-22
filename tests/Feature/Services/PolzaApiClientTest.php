<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Services\PolzaApiClient;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class PolzaApiClientTest extends TestCase
{
    private const string ENDPOINT = 'https://polza.test/api/v1/chat/completions';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.polza.key', 'test-key');
        Config::set('services.polza.url', 'https://polza.test/api/v1');
        Config::set('services.polza.timeout', 30);
    }

    public function test_sends_chat_request_and_returns_content(): void
    {
        Http::fake([
            self::ENDPOINT => Http::response([
                'choices' => [
                    ['message' => ['content' => 'привет']],
                ],
            ]),
        ]);

        $content = new PolzaApiClient()->chat('google/gemini-2.5-flash-lite', [
            ['role' => 'user', 'content' => 'hi'],
        ]);

        $this->assertSame('привет', $content);

        Http::assertSent(fn (Request $request): bool => str_contains($request->url(), '/chat/completions')
            && $request->hasHeader('Authorization', 'Bearer test-key')
            && $request->data()['model'] === 'google/gemini-2.5-flash-lite');
    }

    public function test_omits_temperature_when_null(): void
    {
        Http::fake([self::ENDPOINT => Http::response(['choices' => [['message' => ['content' => 'x']]]])]);

        new PolzaApiClient()->chat('m', [['role' => 'user', 'content' => 'hi']], temperature: null);

        Http::assertSent(fn (Request $request): bool => ! array_key_exists('temperature', $request->data()));
    }

    public function test_omits_temperature_when_empty_string(): void
    {
        Http::fake([self::ENDPOINT => Http::response(['choices' => [['message' => ['content' => 'x']]]])]);

        new PolzaApiClient()->chat('m', [['role' => 'user', 'content' => 'hi']], temperature: '');

        Http::assertSent(fn (Request $request): bool => ! array_key_exists('temperature', $request->data()));
    }

    public function test_includes_temperature_when_set(): void
    {
        Http::fake([self::ENDPOINT => Http::response(['choices' => [['message' => ['content' => 'x']]]])]);

        new PolzaApiClient()->chat('m', [['role' => 'user', 'content' => 'hi']], temperature: '0.3');

        Http::assertSent(fn (Request $request): bool => str_contains($request->body(), '"temperature":0.3'));
    }

    public function test_includes_response_format_when_provided(): void
    {
        Http::fake([self::ENDPOINT => Http::response(['choices' => [['message' => ['content' => 'x']]]])]);

        $responseFormat = [
            'type' => 'json_schema',
            'json_schema' => ['name' => 'word_examples', 'schema' => ['type' => 'object']],
        ];

        new PolzaApiClient()->chat('m', [['role' => 'user', 'content' => 'hi']], responseFormat: $responseFormat);

        Http::assertSent(fn (Request $request): bool => str_contains($request->body(), '"response_format"')
            && str_contains($request->body(), '"name":"word_examples"'));
    }

    public function test_throws_exception_on_http_error(): void
    {
        Http::fake([self::ENDPOINT => Http::response('Server Error', 500)]);

        $this->expectException(RequestException::class);

        new PolzaApiClient()->chat('m', [['role' => 'user', 'content' => 'hi']]);
    }

    public function test_throws_exception_on_missing_content(): void
    {
        Http::fake([self::ENDPOINT => Http::response(['choices' => []])]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageIsOrContains('Invalid response format from Polza API');

        new PolzaApiClient()->chat('m', [['role' => 'user', 'content' => 'hi']]);
    }
}
