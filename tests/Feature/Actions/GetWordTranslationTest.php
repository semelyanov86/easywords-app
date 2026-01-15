<?php

declare(strict_types=1);

namespace Tests\Feature\Actions;

use App\Actions\GetWordTranslation;
use App\Models\User;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class GetWordTranslationTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_translation_from_database_if_word_exists(): void
    {
        // Arrange
        $user = User::factory()->create();
        $word = Word::factory()->for($user)->create([
            'original' => 'hello',
            'language' => 'en',
            'translated' => 'привет',
        ]);

        // Не должно быть HTTP запросов к OpenAI
        Http::fake();

        $action = new GetWordTranslation();

        // Act
        $result = $action->handle('hello', 'en');

        // Assert
        $this->assertEquals('привет', $result->translation);
        Http::assertNothingSent();
    }

    public function test_requests_translation_from_openai_if_word_not_found(): void
    {
        // Arrange
        Config::set('services.openai.key', 'test-key');
        Config::set('services.openai.url', 'https://api.openai.test');
        Config::set('services.openai.model', 'test-model');

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

        $action = new GetWordTranslation();

        // Act
        $result = $action->handle('testword', 'en');

        // Assert
        $this->assertEquals('тестовый перевод', $result->translation);
        Http::assertSent(function (\Illuminate\Http\Client\Request $request): bool {
            $data = $request->data();

            return isset($data['model'], $data['messages'])
                && is_array($data['messages'])
                && count($data['messages']) === 2;
        });
    }

    public function test_removes_citation_marks_from_translation(): void
    {
        // Arrange
        Config::set('services.openai.key', 'test-key');
        Config::set('services.openai.url', 'https://api.openai.test');
        Config::set('services.openai.model', 'test-model');

        Http::fake([
            'https://api.openai.test/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'инвесторы[1][2][3]',
                        ],
                    ],
                ],
            ]),
        ]);

        $action = new GetWordTranslation();

        // Act
        $result = $action->handle('investors', 'en');

        // Assert
        $this->assertEquals('инвесторы', $result->translation);
    }

    public function test_removes_multiple_citation_marks_from_middle_of_text(): void
    {
        // Arrange
        Config::set('services.openai.key', 'test-key');
        Config::set('services.openai.url', 'https://api.openai.test');
        Config::set('services.openai.model', 'test-model');

        Http::fake([
            'https://api.openai.test/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'финансовые[1] инвесторы[2][3] и партнеры[10]',
                        ],
                    ],
                ],
            ]),
        ]);

        $action = new GetWordTranslation();

        // Act
        $result = $action->handle('investors', 'en');

        // Assert
        $this->assertEquals('финансовые инвесторы и партнеры', $result->translation);
    }

    public function test_truncates_long_translations_to_100_characters(): void
    {
        // Arrange
        Config::set('services.openai.key', 'test-key');
        Config::set('services.openai.url', 'https://api.openai.test');
        Config::set('services.openai.model', 'test-model');

        $longTranslation = str_repeat('очень длинный перевод ', 5);
        $this->assertGreaterThan(100, mb_strlen($longTranslation));

        Http::fake([
            'https://api.openai.test/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => $longTranslation,
                        ],
                    ],
                ],
            ]),
        ]);

        $action = new GetWordTranslation();

        // Act
        $result = $action->handle('testword', 'en');

        // Assert
        $this->assertLessThanOrEqual(100, mb_strlen($result->translation));
        $this->assertEquals('...', mb_substr($result->translation, -3));
    }

    public function test_handles_exact_100_character_translation(): void
    {
        // Arrange
        Config::set('services.openai.key', 'test-key');
        Config::set('services.openai.url', 'https://api.openai.test');
        Config::set('services.openai.model', 'test-model');

        $exactTranslation = str_repeat('а', 100);

        Http::fake([
            'https://api.openai.test/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => $exactTranslation,
                        ],
                    ],
                ],
            ]),
        ]);

        $action = new GetWordTranslation();

        // Act
        $result = $action->handle('testword', 'en');

        // Assert
        $this->assertEquals(100, mb_strlen($result->translation));
        $this->assertEquals($exactTranslation, $result->translation);
    }

    public function test_trims_whitespace_from_translation(): void
    {
        // Arrange
        Config::set('services.openai.key', 'test-key');
        Config::set('services.openai.url', 'https://api.openai.test');
        Config::set('services.openai.model', 'test-model');

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

        $action = new GetWordTranslation();

        // Act
        $result = $action->handle('testword', 'en');

        // Assert
        $this->assertEquals('перевод с пробелами', $result->translation);
    }

    public function test_removes_citations_and_then_truncates_if_needed(): void
    {
        // Arrange
        Config::set('services.openai.key', 'test-key');
        Config::set('services.openai.url', 'https://api.openai.test');
        Config::set('services.openai.model', 'test-model');

        $longTranslation = str_repeat('слово[1] ', 20) . '[2][3][4]';

        Http::fake([
            'https://api.openai.test/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => $longTranslation,
                        ],
                    ],
                ],
            ]),
        ]);

        $action = new GetWordTranslation();

        // Act
        $result = $action->handle('testword', 'en');

        // Assert
        $this->assertLessThanOrEqual(100, mb_strlen($result->translation));
        $this->assertStringNotContainsString('[1]', $result->translation);
        $this->assertStringNotContainsString('[2]', $result->translation);
    }

    public function test_sends_correct_system_prompt_for_english(): void
    {
        // Arrange
        Config::set('services.openai.key', 'test-key');
        Config::set('services.openai.url', 'https://api.openai.test');
        Config::set('services.openai.model', 'test-model');

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

        $action = new GetWordTranslation();

        // Act
        $action->handle('test', 'en');

        // Assert
        Http::assertSent(function (\Illuminate\Http\Client\Request $request): bool {
            $data = $request->data();

            if (! isset($data['messages']) || ! is_array($data['messages'])) {
                return false;
            }
            /** @var array<string, scalar> $systemMessage */
            $systemMessage = $data['messages'][0];

            return $systemMessage['role'] === 'system'
                && str_contains((string) $systemMessage['content'], 'с en языка')
                && str_contains((string) $systemMessage['content'], 'Максимальная длина перевода — 100 символов');
        });
    }

    public function test_sends_correct_system_prompt_for_german(): void
    {
        // Arrange
        Config::set('services.openai.key', 'test-key');
        Config::set('services.openai.url', 'https://api.openai.test');
        Config::set('services.openai.model', 'test-model');

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

        $action = new GetWordTranslation();

        // Act
        $action->handle('test', 'de');

        // Assert
        Http::assertSent(function (\Illuminate\Http\Client\Request $request): bool {
            $data = $request->data();

            if (! isset($data['messages']) || ! is_array($data['messages'])) {
                return false;
            }
            /** @var array<string, scalar>  $systemMessage */
            $systemMessage = $data['messages'][0];

            return $systemMessage['role'] === 'system'
                && str_contains((string) $systemMessage['content'], 'с de языка');
        });
    }

    public function test_sends_correct_user_prompt(): void
    {
        // Arrange
        Config::set('services.openai.key', 'test-key');
        Config::set('services.openai.url', 'https://api.openai.test');
        Config::set('services.openai.model', 'test-model');

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

        $action = new GetWordTranslation();

        // Act
        $action->handle('helloworld', 'en');

        // Assert
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

    public function test_throws_exception_on_openai_request_failure(): void
    {
        // Arrange
        Config::set('services.openai.key', 'test-key');
        Config::set('services.openai.url', 'https://api.openai.test');
        Config::set('services.openai.model', 'test-model');

        Http::fake([
            'https://api.openai.test/chat/completions' => Http::response(status: 500),
        ]);

        $action = new GetWordTranslation();

        // Expect
        $this->expectException(\Illuminate\Http\Client\RequestException::class);

        // Act
        $action->handle('testword', 'en');
    }

    public function test_throws_exception_on_invalid_openai_response_format(): void
    {
        // Arrange
        Config::set('services.openai.key', 'test-key');
        Config::set('services.openai.url', 'https://api.openai.test');
        Config::set('services.openai.model', 'test-model');

        Http::fake([
            'https://api.openai.test/chat/completions' => Http::response([
                'invalid' => 'response',
            ]),
        ]);

        $action = new GetWordTranslation();

        // Expect
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid response format from OpenAI API');

        // Act
        $action->handle('testword', 'en');
    }

    public function test_finds_word_by_case_sensitive_match(): void
    {
        // Arrange
        $user = User::factory()->create();
        Word::factory()->for($user)->create([
            'original' => 'Hello',
            'language' => 'en',
            'translated' => 'Привет',
        ]);

        Config::set('services.openai.key', 'test-key');
        Config::set('services.openai.url', 'https://api.openai.test');
        Config::set('services.openai.model', 'test-model');

        Http::fake([
            'https://api.openai.test/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'другой перевод',
                        ],
                    ],
                ],
            ]),
        ]);

        $action = new GetWordTranslation();

        // Act - точное совпадение
        $result1 = $action->handle('Hello', 'en');
        $this->assertEquals('Привет', $result1->translation);

        // Act - разный регистр не найдется, запрос идет к OpenAI
        $result2 = $action->handle('hello', 'en');
        $this->assertEquals('другой перевод', $result2->translation);
    }

    public function test_matches_both_word_and_language(): void
    {
        // Arrange
        $user = User::factory()->create();
        Word::factory()->for($user)->create([
            'original' => 'test',
            'language' => 'en',
            'translated' => 'тест (en)',
        ]);

        Word::factory()->for($user)->create([
            'original' => 'test',
            'language' => 'de',
            'translated' => 'тест (de)',
        ]);

        Http::fake();

        $action = new GetWordTranslation();

        // Act
        $resultEn = $action->handle('test', 'en');
        $resultDe = $action->handle('test', 'de');

        // Assert
        $this->assertEquals('тест (en)', $resultEn->translation);
        $this->assertEquals('тест (de)', $resultDe->translation);
        Http::assertNothingSent();
    }
}
