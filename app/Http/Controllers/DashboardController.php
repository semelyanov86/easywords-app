<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\GetUserSettings;
use App\Actions\GetUserStatistics;
use Inertia\Inertia;

/**
 * Контроллер для отображения главной страницы приложения (dashboard).
 *
 * Предназначен для обработки запроса на главную страницу после авторизации.
 * Передает данные пользователя и его настройки в Inertia-компонент.
 */
final class DashboardController
{
    /**
     * Отображает главную страницу приложения (dashboard).
     *
     * Возвращает Inertia-ответ с данными пользователя и его настройками.
     * На странице отображаются кнопки для выбора направления изучения языков.
     */
    public function __invoke(GetUserSettings $getUserSettings, GetUserStatistics $getUserStatistics): \Inertia\Response
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        return Inertia::render('dashboard', [
            'user' => $user,
            'settings' => $getUserSettings->handle($user->id),
            'statistics' => $getUserStatistics->handle($user),
        ]);
    }
}
