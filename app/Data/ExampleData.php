<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Spatie\LaravelData\Data;

/**
 * Data класс для примеров использования слова.
 *
 * Содержит три примера на оригинальном языке и их переводы на русский.
 * Используется для JSON:API ответов с примерами слов.
 */
final class ExampleData extends Data
{
    /**
     * @param  array<int, string>  $example_original  Три примера на оригинальном языке
     * @param  array<int, string>  $example_translated  Три перевода на русский язык
     */
    public function __construct(
        public string $id,
        public array $example_original,
        public array $example_translated,
    ) {}

    /**
     * Преобразует данные в формат JSON:API.
     *
     * @return array<string, mixed>
     */
    public function toJsonArray(): array
    {
        return [
            'id' => $this->id,
            'type' => 'examples',
            'attributes' => [
                'example_original' => $this->example_original,
                'example_translated' => $this->example_translated,
            ],
        ];
    }

    /**
     * Возвращает JSON:API ответ.
     */
    #[\Override]
    public function toResponse($request = null): JsonResponse
    {
        return response()->json(
            ['data' => $this->toJsonArray()],
            Response::HTTP_OK,
            ['Content-Type' => 'application/vnd.api+json']
        );
    }
}
