<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Spatie\LaravelData\Data;

/**
 * Коллекция извлеченных слов из изображения.
 *
 * Используется для JSON:API ответов с массивом слов из изображений.
 * Возвращает несколько слов в JSON:API формате.
 */
final class ExtractedWordsCollectionData extends Data
{
    /**
     * @param  array<int, ExtractedWordData>  $words
     */
    public function __construct(
        public array $words,
    ) {}

    /**
     * Преобразует коллекцию в JSON:API формат.
     *
     * @return array<string, mixed>
     */
    public function toJsonArray(): array
    {
        return [
            'data' => array_map(
                fn (ExtractedWordData $word) => $word->toJsonArray(),
                $this->words
            ),
        ];
    }

    public function toResponse($request = null): JsonResponse
    {
        return response()->json(
            $this->toJsonArray(),
            Response::HTTP_OK,
            ['Content-Type' => 'application/vnd.api+json']
        );
    }
}
