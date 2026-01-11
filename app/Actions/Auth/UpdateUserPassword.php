<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Lorisleiva\Actions\Concerns\AsAction;

final class UpdateUserPassword
{
    use AsAction;

    /**
     * Обновляет пароль авторизованного пользователя.
     *
     * @param  string  $currentPassword  Текущий пароль пользователя
     * @param  string  $newPassword  Новый пароль
     *
     * @throws \Illuminate\Auth\AuthenticationException Если текущий пароль неверен
     */
    public function handle(string $currentPassword, string $newPassword): void
    {
        $user = auth('sanctum')->user();

        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        // Проверяем, что пользователь - это модель User
        if (! $user instanceof User) {
            abort(401, 'Unauthenticated.');
        }

        // Проверяем текущий пароль
        if (! Hash::check($currentPassword, $user->password)) {
            abort(403, 'The current password is incorrect.');
        }

        // Обновляем пароль
        $user->update([
            'password' => $newPassword,
        ]);
    }
}
