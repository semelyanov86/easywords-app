<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ImageWordExtractor;
use Illuminate\Http\UploadedFile;

/**
 * Сервис для извлечения слов из изображений через Claude API Proxy.
 *
 * Использует ClaudeApiClient для отправки запросов с файлами изображений.
 * Формирует единый промпт с префиксом /language-image-words.
 */
class ClaudeImageWordExtractor implements ImageWordExtractor
{
    public function __construct(private readonly ClaudeApiClient $claudeApiClient) {}

    /**
     * {@inheritDoc}
     */
    public function extractWords(
        UploadedFile $image,
        string $sourceLanguage,
        string $targetLanguage = 'ru'
    ): array {
        $prompt = $this->buildPrompt();

        /** @var array{words: array<int, array{original: string, translation: string, language: string}>} $result */
        $result = $this->claudeApiClient->sendJsonPromptWithFile($prompt, $image);

        return $result['words'];
    }

    /**
     * Формирует промпт для извлечения слов из изображения через Claude API Proxy.
     */
    private function buildPrompt(): string
    {
        return '/language-image-words Извлеки слова для изучения из этого изображения и предоставь их переводы в формате JSON.';
    }
}
