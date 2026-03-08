<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Spatie\LaravelData\Data;

/**
 * Данные извлеченного слова из изображения.
 *
 * Используется для JSON:API ответов при извлечении слов из изображений.
 * Не сохраняется в базе данных, возвращается только для использования в UI.
 */
final class ExtractedWordData extends Data
{
    public function __construct(
        public string $original,
        public string $translation,
        public string $language,
    ) {}

    /**
     * Преобразует данные в JSON:API формат.
     *
     * @return array<string, mixed>
     */
    public function toJsonArray(): array
    {
        return [
            'type' => 'extracted-words',
            'id' => md5($this->original . $this->language), // Используем хеш как временный ID
            'attributes' => [
                'original' => $this->original,
                'translation' => $this->translation,
                'language' => $this->language,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toResponseArray(): array
    {
        return [
            'data' => $this->toJsonArray(),
        ];
    }

    #[\Override]
    public function toResponse($request = null): JsonResponse
    {
        return response()->json(
            $this->toResponseArray(),
            Response::HTTP_OK,
            ['Content-Type' => 'application/vnd.api+json']
        );
    }
}
