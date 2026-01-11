<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Spatie\LaravelData\Data;

final class TokenResponseData extends Data
{
    public function __construct(
        public string $type,
        public string $token,
        public string $token_type,
        public UserData $user,
    ) {}

    public static function fromTokenAndUser(string $token, UserData $user): self
    {
        return new self(
            type: 'auth-token',
            token: $token,
            token_type: 'Bearer',
            user: $user,
        );
    }

    public function toResponse($request = null): JsonResponse
    {
        return response()->json([
            'data' => [
                'type' => $this->type,
                'id' => (string) $this->user->id,
                'attributes' => [
                    'token' => $this->token,
                    'token_type' => $this->token_type,
                ],
                'relationships' => [
                    'user' => [
                        'data' => [
                            'type' => 'user',
                            'id' => (string) $this->user->id,
                        ],
                    ],
                ],
            ],
            'included' => [
                [
                    'type' => 'user',
                    'id' => (string) $this->user->id,
                    'attributes' => [
                        'name' => $this->user->name,
                        'email' => $this->user->email,
                        'is_admin' => $this->user->is_admin,
                        'has_premium' => $this->user->has_premium,
                        'created_at' => $this->user->created_at,
                        'updated_at' => $this->user->updated_at,
                    ],
                ],
            ],
        ], Response::HTTP_CREATED, ['Content-Type' => 'application/vnd.api+json']);
    }
}
