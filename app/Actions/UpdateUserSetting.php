<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Обновление конкретной настройки пользователя.
 *
 * Этот Action инкапсулирует логику обновления отдельной настройки пользователя
 * через пакет glorand/laravel-model-settings.
 * Выделен в отдельный класс для повторного использования и тестирования.
 */
final class UpdateUserSetting
{
    use AsAction;

    /**
     * Обновляет конкретную настройку пользователя и возвращает все настройки.
     *
     * @param  int  $userId  ID пользователя
     * @param  string  $settingName  Имя настройки
     * @param  mixed  $settingValue  Новое значение настройки
     * @return \App\Data\UserSettingsData Data-объект со всеми настройками пользователя после обновления
     */
    public function handle(int $userId, string $settingName, mixed $settingValue): \App\Data\UserSettingsData
    {
        /** @var User $user */
        $user = User::query()->findOrFail($userId);

        $user->settings()->set($settingName, $settingValue);
        $user->save();

        return GetUserSettings::make()->handle($userId);
    }
}
