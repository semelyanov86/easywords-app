<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Services\ClaudeApiClient;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Illuminate\Http\Client\RequestException;

final class ClaudeApiClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.claude.url' => 'https://claude-proxy.test',
            'services.claude.key' => 'test-api-key',
            'services.claude.timeout' => 30,
        ]);
    }

    public function test_sends_prompt_and_returns_result(): void
    {
        Http::fake([
            'claude-proxy.test/api/claude/json' => Http::response([
                'result' => [
                    'example_original' => ['one', 'two', 'three'],
                    'example_translated' => ['один', 'два', 'три'],
                ],
            ]),
        ]);

        $client = new ClaudeApiClient();

        $result = $client->sendJsonPrompt('Test prompt');

        $this->assertArrayHasKey('example_original', $result);
        $this->assertArrayHasKey('example_translated', $result);
        /** @var array<int, string> $originals */
        $originals = $result['example_original'];
        $this->assertCount(3, $originals);

        Http::assertSent(fn (Request $request): bool => str_contains($request->url(), '/api/claude/json')
                && $request->hasHeader('Authorization', 'Bearer test-api-key'));
    }

    public function test_throws_exception_on_missing_result_field(): void
    {
        Http::fake([
            'claude-proxy.test/api/claude/json' => Http::response([
                'data' => 'unexpected',
            ]),
        ]);

        $client = new ClaudeApiClient();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid response format from Claude API: missing "result" field');

        $client->sendJsonPrompt('Test prompt');
    }

    public function test_throws_exception_on_http_error(): void
    {
        Http::fake([
            'claude-proxy.test/api/claude/json' => Http::response('Server Error', 500),
        ]);

        $client = new ClaudeApiClient();

        $this->expectException(RequestException::class);

        $client->sendJsonPrompt('Test prompt');
    }

    public function test_sends_prompt_with_file_and_returns_result(): void
    {
        Http::fake([
            'claude-proxy.test/api/claude/json' => Http::response([
                'result' => [
                    'words' => [
                        ['original' => 'hello', 'translation' => 'привет', 'language' => 'en'],
                    ],
                ],
            ]),
        ]);

        $file = UploadedFile::fake()->image('test.jpg');
        $client = new ClaudeApiClient();

        $result = $client->sendJsonPromptWithFile('Test prompt', $file);

        $this->assertArrayHasKey('words', $result);

        Http::assertSent(fn (Request $request): bool => str_contains($request->url(), '/api/claude/json')
                && $request->hasHeader('Authorization', 'Bearer test-api-key')
                && str_contains($request->body(), 'Test prompt')
                && str_contains($request->body(), 'test.jpg'));
    }

    public function test_file_method_throws_exception_on_missing_result_field(): void
    {
        Http::fake([
            'claude-proxy.test/api/claude/json' => Http::response([
                'data' => 'unexpected',
            ]),
        ]);

        $file = UploadedFile::fake()->image('test.jpg');
        $client = new ClaudeApiClient();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid response format from Claude API: missing "result" field');

        $client->sendJsonPromptWithFile('Test prompt', $file);
    }

    public function test_file_method_throws_exception_on_http_error(): void
    {
        Http::fake([
            'claude-proxy.test/api/claude/json' => Http::response('Server Error', 500),
        ]);

        $file = UploadedFile::fake()->image('test.jpg');
        $client = new ClaudeApiClient();

        $this->expectException(RequestException::class);

        $client->sendJsonPromptWithFile('Test prompt', $file);
    }
}
