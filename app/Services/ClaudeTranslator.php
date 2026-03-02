<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\WordTranslator;

/**
 * Переводчик слов через Claude API Proxy.
 *
 * Использует ClaudeApiClient для отправки запроса на перевод.
 * Формирует единый промпт с префиксом /language-translate.
 */
class ClaudeTranslator implements WordTranslator
{
    public function __construct(private readonly ClaudeApiClient $claudeApiClient) {}

    /**
     * {@inheritDoc}
     */
    public function translate(string $word, string $language): string
    {
        $prompt = $this->buildPrompt($word, $language);

        /** @var array{result: string} $result */
        $result = $this->claudeApiClient->sendJsonPrompt($prompt);

        return trim($result['result']);
    }

    /**
     * Формирует промпт для перевода через Claude API Proxy.
     *
     * @param  string  $word  Слово для перевода
     * @param  string  $language  Язык слова
     */
    private function buildPrompt(string $word, string $language): string
    {
        return "/language-translate Переведи слово \"{$word}\" с {$language} языка на русский. Выведи только перевод, без дополнительных слов.";
    }
}
