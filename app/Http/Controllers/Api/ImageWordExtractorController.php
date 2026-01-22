<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Data\ExtractedWordData;
use App\Data\ExtractedWordsCollectionData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ExtractWordsFromImageRequest;
use App\Services\ImageWordExtractor;
use Illuminate\Http\JsonResponse;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;

/**
 * Контроллер для извлечения слов из изображений.
 *
 * Предоставляет API endpoint для распознавания текста на изображениях
 * и получения слов с переводами для изучения. Слова не сохраняются в базе данных.
 */
#[Middleware('auth:sanctum')]
final class ImageWordExtractorController extends Controller
{
    /**
     * Извлекает слова из изображения и возвращает их переводы.
     *
     * Принимает файл изображения, распознаёт текст через AI API,
     * выделяет ключевые слова для изучения и предоставляет их переводы.
     * Результаты не сохраняются в базе данных.
     *
     * @param  ExtractWordsFromImageRequest  $request  Валидированный запрос с изображением и языком
     * @param  ImageWordExtractor  $extractor  Сервис для извлечения слов из изображения
     * @return JsonResponse JSON:API ответ с массивом слов и переводов
     */
    #[Post('api/v1/images/extract-words', name: 'api.v1.images.extract-words')]
    public function extract(
        ExtractWordsFromImageRequest $request,
        ImageWordExtractor $extractor
    ): JsonResponse {
        /** @var array{image: \Illuminate\Http\UploadedFile, language: string, target_language?: string} $validated */
        $validated = $request->validated();

        $sourceLanguage = $validated['language'];
        $targetLanguage = $validated['target_language'] ?? 'ru';

        $extractedWords = $extractor->extractWords(
            image: $validated['image'],
            sourceLanguage: $sourceLanguage,
            targetLanguage: $targetLanguage
        );

        $wordsData = array_map(
            fn (array $word) => ExtractedWordData::from([
                'original' => $word['original'],
                'translation' => $word['translation'],
                'language' => $word['language'],
            ]),
            $extractedWords
        );

        return ExtractedWordsCollectionData::from(['words' => $wordsData])->toResponse();
    }
}
