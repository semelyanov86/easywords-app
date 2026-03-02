<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Http\UploadedFile;

interface ImageWordExtractor
{
    /**
     * @param  UploadedFile  $image  Файл изображения
     * @param  string  $sourceLanguage  Язык текста на изображении (например: "en", "de", "es")
     * @param  string  $targetLanguage  Язык для переводов (по умолчанию русский)
     * @return array<int, array{original: string, translation: string, language: string}>
     */
    public function extractWords(
        UploadedFile $image,
        string $sourceLanguage,
        string $targetLanguage = 'ru'
    ): array;
}
