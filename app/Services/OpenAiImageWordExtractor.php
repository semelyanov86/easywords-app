<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ImageWordExtractor;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

/**
 * Сервис для извлечения слов из изображений через OpenAI-совместимый API.
 *
 * Позволяет распознавать текст на изображениях и получать слова с переводами
 * для изучения. Совместим с OpenAI API, Perplexity (Sonar Pro) и другими
 * OpenAI-совместимыми провайдерами, поддерживающими vision capabilities.
 */
final readonly class OpenAiImageWordExtractor implements ImageWordExtractor
{
    private PendingRequest $client;

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
     *
     * @throws RequestException если запрос к API не удался
     * @throws \JsonException если не удалось декодировать JSON ответ
     */
    public function extractWords(
        UploadedFile $image,
        string $sourceLanguage,
        string $targetLanguage = 'ru'
    ): array {
        $base64Image = $this->convertImageToBase64($image);
        $systemPrompt = $this->buildSystemPrompt($sourceLanguage, $targetLanguage);
        $userPrompt = $this->buildUserPrompt();

        /** @var string $model */
        $model = config('services.openai.model', 'gpt-4o');
        /** @var float|int|string $temperatureValue */
        $temperatureValue = config('services.openai.temperature', 0.3);
        $temperature = (float) $temperatureValue;

        // @phpstan-ignore-next-line
        $timeout = (int) config('services.openai.timeout', 120);

        $response = $this->client->timeout($timeout)->post('/chat/completions', [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemPrompt,
                ],
                [
                    'role' => 'user',
                    'content' => [
                        ['type' => 'text', 'text' => $userPrompt],
                        [
                            'type' => 'image_url',
                            'image_url' => ['url' => $base64Image],
                        ],
                    ],
                ],
            ],
            'temperature' => $temperature,
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'extracted_words',
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'words' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'original' => ['type' => 'string'],
                                        'translation' => ['type' => 'string'],
                                        'language' => ['type' => 'string'],
                                    ],
                                    'required' => ['original', 'translation', 'language'],
                                ],
                            ],
                        ],
                        'required' => ['words'],
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

        /** @var array{words: array<int, array{original: string, translation: string, language: string}>} $result */
        $result = json_decode(
            $content,
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        return $result['words'];
    }

    /**
     * Конвертирует загруженный файл изображения в base64 формат.
     *
     * @param  UploadedFile  $image  Файл изображения
     * @return string Изображение в формате base64 с data URL prefix
     */
    private function convertImageToBase64(UploadedFile $image): string
    {
        $mimeType = $image->getMimeType();
        $imageData = base64_encode($image->getContent());

        return "data:{$mimeType};base64,{$imageData}";
    }

    /**
     * Формирует системный промпт для извлечения слов из изображения.
     *
     * @param  string  $sourceLanguage  Язык текста на изображении
     * @param  string  $targetLanguage  Язык для переводов
     * @return string Системный промпт
     */
    private function buildSystemPrompt(string $sourceLanguage, string $targetLanguage): string
    {
        return <<<PROMPT
            Ты — помощник для изучения языков. Твоя задача — извлекать слова из изображений и предоставлять переводы.

            Требования:
            1. Распознай текст на предоставленном изображении на {$sourceLanguage} языке.
            2. Выдели 5-10 ключевых слов для изучения (существительные, глаголы, прилагательные).
            3. Исключи слишком простые слова, цифры, символы и служебные слова.
            4. Для каждого слова предоставь точный перевод на {$targetLanguage} язык.
            5. Формат ответа должен быть строго в JSON:
            {
              "words": [
                {
                  "original": "слово на оригинальном языке",
                  "translation": "перевод",
                  "language": "{$sourceLanguage}"
                }
              ]
            }

            6. Слова должны быть релевантны для изучения и полезны в повседневной речи.
            7. Переводы должны быть точными и естественно звучать на {$targetLanguage} языке.
            8. Не включай никаких объяснений, комментариев или дополнительного текста — только JSON.
            PROMPT;
    }

    /**
     * Формирует пользовательский промпт.
     *
     * @return string Пользовательский промпт
     */
    private function buildUserPrompt(): string
    {
        return 'Извлеки слова для изучения из этого изображения и предоставь их переводы в формате JSON.';
    }
}
