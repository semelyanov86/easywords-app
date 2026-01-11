<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Spatie\LaravelData\Data;

/**
 * Краткие данные пользователя для API (только id и name).
 * Используется в режиме short_mode для уменьшения размера ответа.
 */
final class ShortUserData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toJsonArray(): array
    {
        return [
            'type' => 'users',
            'id' => (string) $this->id,
            'attributes' => [
                'name' => $this->name,
            ],
        ];
    }

    public function toResponse($request = null): JsonResponse
    {
        return response()->json($this->toJsonArray(), Response::HTTP_OK, ['Content-Type' => 'application/vnd.api+json']);
    }
}
