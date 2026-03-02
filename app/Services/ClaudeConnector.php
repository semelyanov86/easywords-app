<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\WordExampleGenerator;

/**
 * Коннектор для генерации примеров слов через Claude API Proxy.
 *
 * Использует ClaudeApiClient для отправки запросов. Формирует единый промпт,
 * объединяя системные инструкции и пользовательский запрос, так как Claude Proxy
 * принимает одно поле `prompt`.
 */
class ClaudeConnector implements WordExampleGenerator
{
    public function __construct(private readonly ClaudeApiClient $claudeApiClient) {}

    /**
     * {@inheritDoc}
     */
    public function generateWordExamples(string $word, string $language): array
    {
        $prompt = $this->buildPrompt($word, $language);

        /** @var array{example_original: array<int, string>, example_translated: array<int, string>} $result */
        $result = $this->claudeApiClient->sendJsonPrompt($prompt);

        return $result;
    }

    /**
     * Формирует единый промпт, объединяя системные инструкции и пользовательский запрос.
     *
     * @param  string  $word  Слово для генерации примеров
     * @param  string  $language  Язык слова
     */
    private function buildPrompt(string $word, string $language): string
    {
        return "/language-examples Сгенерируй три примера использования слова \"{$word}\" на {$language} языке и их переводы на русский язык в формате JSON.";
    }
}
