<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\WordTranslator;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Переводчик слов через OpenAI-совместимый API.
 *
 * Отправляет запрос на перевод через OpenAI Chat Completions API.
 * Возвращает необработанный перевод (без пост-обработки).
 */
class OpenAiTranslator implements WordTranslator
{
    private readonly PendingRequest $client;

    public function __construct()
    {
        /** @var string $apiKey */
        $apiKey = config('services.openai.key');
        /** @var string $apiUrl */
        $apiUrl = config('services.openai.url');

        $this->client = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->baseUrl($apiUrl);
    }

    /**
     * {@inheritDoc}
     */
    public function translate(string $word, string $language): string
    {
        /** @var string $model */
        $model = config('services.openai.model', 'glm-4.7');
        /** @var float|int|string $temperatureValue */
        $temperatureValue = config('services.openai.temperature', 0.3);
        $temperature = (float) $temperatureValue;

        // @phpstan-ignore-next-line
        $timeout = (int) config('services.openai.timeout', 120);

        $systemPrompt = $this->buildSystemPrompt($language);
        $userPrompt = $this->buildUserPrompt($word, $language);

        $response = $this->client->timeout($timeout)->post('/chat/completions', [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => $temperature,
        ]);

        $response->throw();

        /** @var array{choices: array<int, array{message: array{content: string}}>} $data */
        $data = $response->json();

        if (! isset($data['choices'][0]['message']['content'])) {
            throw new \RuntimeException('Invalid response format from OpenAI API');
        }

        return trim($data['choices'][0]['message']['content']);
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
