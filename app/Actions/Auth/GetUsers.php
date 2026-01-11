<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Data\ShortUserData;
use App\Data\UserData;
use App\Models\User;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Получает список всех пользователей.
 *
 * Логика вынесена из контроллера для соблюдения принципа thin controllers.
 * В зависимости от флага short_mode возвращает полные или краткие данные пользователей.
 */
final class GetUsers
{
    use AsAction;

    /**
     * Получает список пользователей.
     *
     * @param  bool  $shortMode  Флаг краткого режима (только id и name)
     * @return Collection<int|string, ShortUserData|UserData>
     */
    public function handle(bool $shortMode = false): Collection
    {
        $users = User::all();

        if ($shortMode) {
            // @phpstan-ignore-next-line
            return ShortUserData::collect($users, Collection::class);
        }

        // @phpstan-ignore-next-line
        return UserData::collect($users, Collection::class);
    }
}
