<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonImmutable;
use Laravel\Sanctum\PersonalAccessToken as SanctumToken;
use Spatie\LaravelData\Data;

/**
 * Data-класс для личного токена доступа пользователя.
 *
 * Предоставляет структурированное представление токена Sanctum для API ответов
 * и передачи данных в Inertia-компоненты.
 */
final class PersonalAccessTokenData extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public string $tokenable_type,
        public int $tokenable_id,
        public string $abilities,
        public CarbonImmutable $last_used_at,
        public CarbonImmutable $created_at,
        public CarbonImmutable $updated_at,
        public ?CarbonImmutable $expires_at,
    ) {}

    /**
     * Создает экземпляр из токена Sanctum.
     *
     * @param  SanctumToken  $token  Токен из базы данных
     */
    public static function fromToken(SanctumToken $token): PersonalAccessTokenData
    {
        return new self(
            id: (string) $token->id,
            name: $token->name,
            tokenable_type: $token->tokenable_type,
            tokenable_id: $token->tokenable_id,
            abilities: is_array($token->abilities) ? implode(', ', $token->abilities) : '*',
            last_used_at: CarbonImmutable::parse($token->last_used_at),
            created_at: CarbonImmutable::parse($token->created_at),
            updated_at: CarbonImmutable::parse($token->updated_at),
            expires_at: $token->expires_at ? CarbonImmutable::parse($token->expires_at) : null,
        );
    }
}
