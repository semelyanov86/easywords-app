<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\WordExampleGenerator;

/**
 * Генератор примеров использования слов через polza.ai (OpenAI-совместимый API).
 *
 * Использует PolzaApiClient и модель, заданную в services.polza.models.examples.
 * Запрашивает структурированный JSON через response_format (json_schema).
 */
final readonly class PolzaConnector implements WordExampleGenerator
{
    public function __construct(private PolzaApiClient $client) {}

    /**
     * {@inheritDoc}
     *
     * @throws \JsonException если не удалось декодировать JSON ответ
     */
    public function generateWordExamples(string $word, string $language): array
    {
        $content = $this->client->chat(
            model: $this->model(),
            messages: [
                ['role' => 'system', 'content' => $this->buildSystemPrompt($language)],
                ['role' => 'user', 'content' => $this->buildUserPrompt($word, $language)],
            ],
            responseFormat: $this->responseFormat(),
            temperature: $this->temperature(),
        );

        /** @var array{example_original: array<int, string>, example_translated: array<int, string>} $result */
        $result = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        return $result;
    }

    /**
     * Возвращает модель для генерации примеров.
     */
    private function model(): string
    {
        /** @var string $model */
        $model = config('services.polza.models.examples');

        return $model;
    }

    /**
     * Возвращает температуру для примеров (null — параметр не отправляется).
     */
    private function temperature(): float|int|string|null
    {
        /** @var float|int|string|null $temperature */
        $temperature = config('services.polza.temperature.examples');

        return $temperature;
    }

    /**
     * Описывает требуемую JSON-схему ответа.
     *
     * @return array<string, mixed>
     */
    private function responseFormat(): array
    {
        return [
            'type' => 'json_schema',
            'json_schema' => [
                'name' => 'word_examples',
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'example_original' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                        ],
                        'example_translated' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                        ],
                    ],
                    'required' => ['example_original', 'example_translated'],
                    'additionalProperties' => false,
                ],
            ],
        ];
    }

    /**
     * Формирует системный промпт для генерации примеров.
     *
     * @param  string  $language  Язык генерации примеров
     */
    private function buildSystemPrompt(string $language): string
    {
        return <<<PROMPT
            Ты — помощник для изучения языков. Твоя задача — генерировать примеры использования слов на {$language} языке.

            Правила:
            1. Генерируй ТОЛЬКО три примера на {$language} языке:
               - Первый: цитата из классической или современной художественной литературы
               - Второй: афоризм, пословица или мудрое изречение
               - Третий: пример из повседневной разговорной речи

            2. Для каждого примера на {$language} языке предоставь точный перевод на русский язык

            3. Формат ответа должен быть строго в JSON:
            {
              "example_original": ["пример1", "пример2", "пример3"],
              "example_translated": ["перевод1", "перевод2", "перевод3"]
            }

            4. Примеры должны быть релевантны для изучения этого конкретного слова
            5. Переводы должны быть точными и естественно звучать на русском языке
            6. Не включай никаких объяснений, комментариев или дополнительного текста — только JSON
            PROMPT;
    }

    /**
     * Формирует пользовательский промпт с конкретным словом.
     *
     * @param  string  $word  Слово для генерации примеров
     * @param  string  $language  Язык слова
     */
    private function buildUserPrompt(string $word, string $language): string
    {
        return "Сгенерируй три примера использования слова \"{$word}\" на {$language} языке и их переводы на русский язык в формате JSON.";
    }
}
