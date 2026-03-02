<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\WordExampleGenerator;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Коннектор для работы с OpenAI-совместимыми API.
 *
 * Позволяет взаимодействовать с любым сервером, совместимым с OpenAI API
 * (включая модели от Z.ai, OpenAI, и другие). Читает конфигурацию из .env файла.
 */
class OpenAiConnector implements WordExampleGenerator
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
     * Генерирует примеры использования слова через OpenAI API.
     *
     * Запрашивает три примера: классика/литература, афоризм, разговорный пример.
     * Получает результаты на оригинальном языке и их переводы на русский.
     *
     * @param  string  $word  Слово для генерации примеров
     * @param  string  $language  Язык слова (например: "en", "de", "es")
     * @return array{example_original: array<int, string>, example_translated: array<int, string>}
     *
     * @throws \Illuminate\Http\Client\RequestException если запрос к API не удался
     * @throws \JsonException если не удалось декодировать JSON ответ
     */
    public function generateWordExamples(string $word, string $language): array
    {
        $systemPrompt = $this->buildSystemPrompt($language);
        $userPrompt = $this->buildUserPrompt($word, $language);

        /** @var string $model */
        $model = config('services.openai.model', 'glm-4.7');
        /** @var float|int|string $temperatureValue */
        $temperatureValue = config('services.openai.temperature', 0.7);
        $temperature = (float) $temperatureValue;

        // @phpstan-ignore-next-line
        $timeout = (int) config('services.openai.timeout', 120);

        $response = $this->client->timeout($timeout)->post('/chat/completions', [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => $temperature,
            'response_format' => [
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
            ],
        ]);

        $response->throw();

        /** @var array{choices: array<int, array{message: array{content: string}}>} $data */
        $data = $response->json();

        if (! isset($data['choices'][0]['message']['content'])) {
            throw new \RuntimeException('Invalid response format from OpenAI API');
        }

        $content = $data['choices'][0]['message']['content'];

        /** @var array{example_original: array<int, string>, example_translated: array<int, string>} $result */
        $result = json_decode(
            $content,
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        return $result;
    }

    /**
     * Формирует системный промпт для генерации примеров.
     *
     * @param  string  $language  Язык генерации примеров
     * @return string Системный промпт
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
     * @return string Пользовательский промпт
     */
    private function buildUserPrompt(string $word, string $language): string
    {
        return "Сгенерируй три примера использования слова \"{$word}\" на {$language} языке и их переводы на русский язык в формате JSON.";
    }
}
