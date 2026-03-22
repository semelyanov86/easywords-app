<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\GetUserSettings;
use App\Http\Requests\UpdateSettingsWebRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\User;

/**
 * Контроллер страницы настроек пользователя.
 *
 * Предназначен для отображения формы настроек пользователя и обработки
 * их обновления через Inertia. Пользователь может настроить различные
 * параметры отображения и поведения приложения.
 */
final class SettingsController
{
    /**
     * Отображает страницу настроек пользователя.
     *
     * Возвращает Inertia-компонент с формой настроек.
     * Все настройки передаются как начальные данные формы.
     *
     * @return Response Inertia-ответ с данными настроек
     */
    public function show(Request $request, GetUserSettings $getUserSettings): Response
    {
        /** @var User $user */
        $user = $request->user();

        return Inertia::render('settings/index', [
            'settings' => $getUserSettings->handle($user->id),
            'user' => $user,
        ]);
    }

    /**
     * Обновляет настройки пользователя.
     *
     * Обрабатывает отправку формы настроек и обновляет все настройки
     * пользователя через пакет glorand/laravel-model-settings.
     * После успешного обновления возвращает пользователя назад с уведомлением.
     *
     * @param  UpdateSettingsWebRequest  $request  Запрос с данными формы настроек
     * @return RedirectResponse Перенаправление на страницу настроек с уведомлением
     */
    public function update(UpdateSettingsWebRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        /** @var array{paginate: int, main_language: string, show_starred: bool, known_enabled: bool, latest_first: bool, show_imported: bool, show_shared: bool, fresh_first: bool} $validated */
        $validated = $request->validated();

        // Обновляем все настройки пользователя
        foreach ($validated as $key => $value) {
            $user->settings()->set($key, $value);
        }

        $user->save();

        return to_route('settings.show')
            ->with('success', __('settings.updated_successfully'));
    }
}
