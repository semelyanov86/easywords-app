<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Services\OpenAiTranslator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class OpenAiTranslatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.openai.key', 'test-key');
        Config::set('services.openai.url', 'https://api.openai.test');
        Config::set('services.openai.model', 'test-model');
    }

    public function test_translates_word_via_openai_api(): void
    {
        Http::fake([
            'https://api.openai.test/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'тестовый перевод',
                        ],
                    ],
                ],
            ]),
        ]);

        $translator = new OpenAiTranslator();

        $result = $translator->translate('testword', 'en');

        $this->assertEquals('тестовый перевод', $result);
    }

    public function test_sends_correct_system_prompt_for_language(): void
    {
        Http::fake([
            'https://api.openai.test/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'translation',
                        ],
                    ],
                ],
            ]),
        ]);

        $translator = new OpenAiTranslator();

        $translator->translate('test', 'de');

        Http::assertSent(function (\Illuminate\Http\Client\Request $request): bool {
            $data = $request->data();

            if (! isset($data['messages']) || ! is_array($data['messages'])) {
                return false;
            }

            /** @var array<string, scalar> $systemMessage */
            $systemMessage = $data['messages'][0];

            return $systemMessage['role'] === 'system'
                && str_contains((string) $systemMessage['content'], 'с de языка')
                && str_contains((string) $systemMessage['content'], 'Максимальная длина перевода — 100 символов');
        });
    }

    public function test_sends_correct_user_prompt(): void
    {
        Http::fake([
            'https://api.openai.test/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'translation',
                        ],
                    ],
                ],
            ]),
        ]);

        $translator = new OpenAiTranslator();

        $translator->translate('helloworld', 'en');

        Http::assertSent(function (\Illuminate\Http\Client\Request $request): bool {
            $data = $request->data();

            if (! isset($data['messages']) || ! is_array($data['messages'])) {
                return false;
            }

            /** @var array<string, scalar> $userMessage */
            $userMessage = $data['messages'][1];

            return $userMessage['role'] === 'user'
                && str_contains((string) $userMessage['content'], 'helloworld')
                && str_contains((string) $userMessage['content'], 'с en языка');
        });
    }

    public function test_trims_whitespace_from_translation(): void
    {
        Http::fake([
            'https://api.openai.test/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => '  перевод с пробелами  ',
                        ],
                    ],
                ],
            ]),
        ]);

        $translator = new OpenAiTranslator();

        $result = $translator->translate('testword', 'en');

        $this->assertEquals('перевод с пробелами', $result);
    }

    public function test_throws_exception_on_request_failure(): void
    {
        Http::fake([
            'https://api.openai.test/chat/completions' => Http::response(status: 500),
        ]);

        $translator = new OpenAiTranslator();

        $this->expectException(\Illuminate\Http\Client\RequestException::class);

        $translator->translate('testword', 'en');
    }

    public function test_throws_exception_on_invalid_response_format(): void
    {
        Http::fake([
            'https://api.openai.test/chat/completions' => Http::response([
                'invalid' => 'response',
            ]),
        ]);

        $translator = new OpenAiTranslator();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid response format from OpenAI API');

        $translator->translate('testword', 'en');
    }
}
