<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Spatie\LaravelData\Data;

final class WordData extends Data
{
    public function __construct(
        public int $id,
        public string $original,
        public string $translated,
        public string $language,
        public ?CarbonImmutable $done_at,
        public bool $starred,
        public int $views,
        public bool $from_sample,
        public int $user_id,
        public CarbonImmutable $created_at,
        public CarbonImmutable $updated_at,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toJsonArray(): array
    {
        return [
            'type' => 'words',
            'id' => (string) $this->id,
            'attributes' => [
                'original' => $this->original,
                'translated' => $this->translated,
                'language' => $this->language,
                'done_at' => $this->done_at,
                'starred' => $this->starred,
                'views' => $this->views,
                'from_sample' => $this->from_sample,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
        ];
    }

    #[\Override]
    public function toResponse($request = null): JsonResponse
    {
        return response()->json(['data' => $this->toJsonArray()], Response::HTTP_OK, ['Content-Type' => 'application/vnd.api+json']);
    }
}
