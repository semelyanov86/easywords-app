<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ImageWordExtractor;
use Illuminate\Http\UploadedFile;

/**
 * Извлечение слов из изображений через polza.ai (OpenAI-совместимый API).
 *
 * Использует PolzaApiClient и vision-модель, заданную в services.polza.models.image.
 * Изображение передаётся как base64 data URL, ответ запрашивается строго в JSON
 * через response_format (json_schema).
 */
final readonly class PolzaImageWordExtractor implements ImageWordExtractor
{
    public function __construct(private PolzaApiClient $client) {}

    /**
     * {@inheritDoc}
     *
     * @throws \JsonException если не удалось декодировать JSON ответ
     */
    public function extractWords(
        UploadedFile $image,
        string $sourceLanguage,
        string $targetLanguage = 'ru'
    ): array {
        $base64Image = $this->convertImageToBase64($image);

        $content = $this->client->chat(
            model: $this->model(),
            messages: [
                ['role' => 'system', 'content' => $this->buildSystemPrompt($sourceLanguage, $targetLanguage)],
                [
                    'role' => 'user',
                    'content' => [
                        ['type' => 'text', 'text' => $this->buildUserPrompt()],
                        ['type' => 'image_url', 'image_url' => ['url' => $base64Image]],
                    ],
                ],
            ],
            responseFormat: $this->responseFormat(),
            temperature: $this->temperature(),
        );

        /** @var array{words: array<int, array{original: string, translation: string, language: string}>} $result */
        $result = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        return $result['words'];
    }

    /**
     * Возвращает vision-модель для распознавания слов.
     */
    private function model(): string
    {
        /** @var string $model */
        $model = config('services.polza.models.image');

        return $model;
    }

    /**
     * Возвращает температуру для распознавания (null — параметр не отправляется).
     */
    private function temperature(): float|int|string|null
    {
        /** @var float|int|string|null $temperature */
        $temperature = config('services.polza.temperature.image');

        return $temperature;
    }

    /**
     * Конвертирует загруженный файл изображения в base64 data URL.
     *
     * @param  UploadedFile  $image  Файл изображения
     * @return string Изображение в формате base64 с data URL префиксом
     */
    private function convertImageToBase64(UploadedFile $image): string
    {
        $mimeType = $image->getMimeType();
        $imageData = base64_encode($image->getContent());

        return "data:{$mimeType};base64,{$imageData}";
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
        ];
    }

    /**
     * Формирует системный промпт для извлечения слов из изображения.
     *
     * @param  string  $sourceLanguage  Язык текста на изображении
     * @param  string  $targetLanguage  Язык для переводов
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
     */
    private function buildUserPrompt(): string
    {
        return 'Извлеки слова для изучения из этого изображения и предоставь их переводы в формате JSON.';
    }
}
