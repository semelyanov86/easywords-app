<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

/**
 * Данные ответа для API импорта samples.
 *
 * Этот Data класс нужен для возврата информации о количестве
 * созданных слов и самих слов в формате JSON:API.
 *
 * Контракт для вызывающего кода:
 * - Соответствует JSON:API спецификации
 * - Содержит метаданные о количестве созданных слов
 * - Возвращает массив WordData в атрибутах
 */
final class ImportSamplesResponseData extends Data
{
    /**
     * @param  Collection<int, WordData>  $words
     */
    public function __construct(
        public Collection $words,
        public int $total_created,
        public int $total_skipped,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toJsonArray(): array
    {
        return [
            'type' => 'import-samples',
            'id' => '0',
            'attributes' => [
                'total_created' => $this->total_created,
                'total_skipped' => $this->total_skipped,
            ],
            'relationships' => [
                'words' => [
                    'data' => $this->words->map(fn (WordData $wordData) => $wordData->toJsonArray()),
                ],
            ],
        ];
    }

    public function toResponse($request = null): JsonResponse
    {
        return response()->json([
            'data' => $this->toJsonArray(),
            'included' => $this->words->map(fn (WordData $wordData) => $wordData->toJsonArray()),
        ], Response::HTTP_CREATED, ['Content-Type' => 'application/vnd.api+json']);
    }
}
