<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\WordTranslator;

/**
 * Переводчик слов через polza.ai (OpenAI-совместимый API).
 *
 * Использует PolzaApiClient и модель, заданную в services.polza.models.translate.
 */
final readonly class PolzaTranslator implements WordTranslator
{
    public function __construct(private PolzaApiClient $client) {}

    /**
     * {@inheritDoc}
     */
    public function translate(string $word, string $language): string
    {
        $content = $this->client->chat(
            model: $this->model(),
            messages: [
                ['role' => 'system', 'content' => $this->buildSystemPrompt($language)],
                ['role' => 'user', 'content' => $this->buildUserPrompt($word, $language)],
            ],
            temperature: $this->temperature(),
        );

        return trim($content);
    }

    /**
     * Возвращает модель для перевода.
     */
    private function model(): string
    {
        /** @var string $model */
        $model = config('services.polza.models.translate');

        return $model;
    }

    /**
     * Возвращает температуру для перевода (null — параметр не отправляется).
     */
    private function temperature(): float|int|string|null
    {
        /** @var float|int|string|null $temperature */
        $temperature = config('services.polza.temperature.translate');

        return $temperature;
    }

    /**
     * Формирует системный промпт для перевода.
     *
     * @param  string  $language  Язык слова
     */
    private function buildSystemPrompt(string $language): string
    {
        return <<<PROMPT
            Ты — помощник для изучения языков. Твоя задача — переводить слова с {$language} языка на русский.

            Правила:
            1. Перевод должен быть кратким и точным
            2. Максимальная длина перевода — 100 символов
            3. Выводи ТОЛЬКО перевод, без каких-либо дополнительных слов или объяснений
            4. Если слово имеет несколько значений, выбери основное, самое распространенное
            PROMPT;
    }

    /**
     * Формирует пользовательский промпт с конкретным словом.
     *
     * @param  string  $word  Слово для перевода
     * @param  string  $language  Язык слова
     */
    private function buildUserPrompt(string $word, string $language): string
    {
        return "Переведи слово \"{$word}\" с {$language} языка на русский. Выведи только перевод, без дополнительных слов.";
    }
}
