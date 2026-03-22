<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\ExtractedWordData;
use App\Http\Requests\WordImageExtractRequest;
use App\Contracts\ImageWordExtractor;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Inertia\Response;

/**
 * Контроллер для извлечения слов из изображений через web-интерфейс.
 *
 * Предоставляет страницу и форму для загрузки изображений,
 * распознавания текста и получения слов с переводами для изучения.
 * Результаты не сохраняются в базе данных.
 */
final class ExtractWordsFromImageController
{
    /**
     * Отображает страницу извлечения слов из изображения.
     */
    public function index(): Response
    {
        /** @var User $user */
        $user = auth()->user();

        return Inertia::render('Words/ExtractFromImage', [
            'user' => $user,
            'settings' => $user->settings()->all(),
        ]);
    }

    /**
     * Извлекает слова из изображения.
     *
     * Принимает файл изображения, распознаёт текст через AI API,
     * выделяет ключевые слова для изучения и предоставляет их переводы.
     * Результаты не сохраняются в базе данных.
     *
     * @param  ImageWordExtractor  $extractor  Сервис для извлечения слов из изображения
     * @return Response Ответ с массивом слов для добавления
     */
    public function extract(WordImageExtractRequest $request, ImageWordExtractor $extractor): Response
    {
        /** @var User $user */
        $user = auth()->user();
        /** @var array{image: UploadedFile, language: string, target_language?: string} $validated */
        $validated = $request->validated();

        $sourceLanguage = $validated['language'];
        $targetLanguage = $validated['target_language'] ?? 'ru';

        $extractedWords = $extractor->extractWords(
            image: $validated['image'],
            sourceLanguage: $sourceLanguage,
            targetLanguage: $targetLanguage
        );

        $wordsData = ExtractedWordData::collect($extractedWords, 'array');

        return Inertia::render('Words/ExtractFromImage', [
            'words' => $wordsData,
            'user' => $user,
            'settings' => $user->settings()->all(),
        ]);
    }
}
