<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\PersonalAccessTokenData;
use Illuminate\Support\Collection;
use App\Models\User;

/**
 * Получает список всех личных токенов доступа пользователя.
 *
 * Извлекает все токены Sanctum, созданные пользователем,
 * и возвращает их в виде Data-объектов для отображения на фронтенде.
 */
final readonly class GetUserPersonalAccessTokens
{
    /**
     * Возвращает коллекцию токенов доступа пользователя.
     *
     * @param  int  $userId  Идентификатор пользователя
     * @return Collection<int, PersonalAccessTokenData> Коллекция данных о токенах
     */
    public function handle(int $userId): Collection
    {
        $user = User::findOrFail($userId);

        return PersonalAccessTokenData::collect($user->tokens, Collection::class);
    }
}
