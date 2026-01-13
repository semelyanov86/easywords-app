<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Spatie\LaravelData\Data;

/**
 * Данные для ответа с переводом слова.
 *
 * Используется для API endpoint, который возвращает автоматический перевод слова.
 */
final class WordTranslationData extends Data
{
    public function __construct(
        public string $translation,
    ) {}

    /**
     * Преобразует данные в JSON:API формат.
     *
     * @return array<string, mixed>
     */
    public function toJsonArray(): array
    {
        return [
            'type' => 'word-translations',
            'id' => 'temp', // Временный ID, так как это не сущность из БД
            'attributes' => [
                'translation' => $this->translation,
            ],
        ];
    }

    /**
     * Возвращает JSON:API ответ.
     */
    public function toResponse($request = null): JsonResponse
    {
        return response()->json(['data' => $this->toJsonArray()], Response::HTTP_OK, ['Content-Type' => 'application/vnd.api+json']);
    }
}
