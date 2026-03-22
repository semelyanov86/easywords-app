<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Validation\ValidationException;

/**
 * Изменение пароля пользователя.
 *
 * Этот Action инкапсулирует логику проверки текущего пароля
 * и установки нового пароля пользователя.
 * Выделен в отдельный класс для повторного использования и тестирования.
 */
final class UpdateUserPassword
{
    use AsAction;

    /**
     * Проверяет текущий пароль и устанавливает новый.
     *
     * @param  int  $userId  ID пользователя
     * @param  string  $currentPassword  Текущий пароль
     * @param  string  $newPassword  Новый пароль
     *
     * @throws ValidationException Если текущий пароль неверный
     */
    public function handle(int $userId, string $currentPassword, string $newPassword): void
    {
        /** @var User $user */
        $user = User::query()->findOrFail($userId);

        // Проверяем текущий пароль
        if (! Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => [__('The provided password does not match your current password.')],
            ]);
        }

        // Устанавливаем новый пароль
        $user->password = Hash::make($newPassword);
        $user->save();
    }
}
