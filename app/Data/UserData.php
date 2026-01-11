<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Spatie\LaravelData\Data;

final class UserData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public bool $is_admin,
        public bool $has_premium,
        public CarbonImmutable $created_at,
        public CarbonImmutable $updated_at,
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
                'email' => $this->email,
                'is_admin' => $this->is_admin,
                'has_premium' => $this->has_premium,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
        ];
    }

    public function toResponse($request = null): JsonResponse
    {
        return response()->json(['data' => $this->toJsonArray()], Response::HTTP_OK, ['Content-Type' => 'application/vnd.api+json']);
    }
}
