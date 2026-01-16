<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\UpdateUserPassword;
use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Контроллер для управления паролем пользователя.
 *
 * Отвечает за отображение формы изменения пароля и обработку запроса на обновление.
 */
final class PasswordController
{
    /**
     * Отображает страницу изменения пароля.
     */
    public function edit(): Response
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        return Inertia::render('password/Edit', [
            'user' => $user
        ]);
    }

    /**
     * Обновляет пароль пользователя.
     *
     * Проверяет текущий пароль и устанавливает новый, затем перенаправляет на страницу профиля.
     */
    public function updatePassword(UpdatePasswordRequest $request, UpdateUserPassword $updatePassword): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $updatePassword->handle(
            userId: $user->id,
            currentPassword: $request->string('current_password')->toString(),
            newPassword: $request->string('password')->toString(),
        );

        return back()->with('success', __('Password has been updated successfully.'));
    }
}
