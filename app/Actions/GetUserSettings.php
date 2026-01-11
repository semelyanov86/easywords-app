<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\UserSettingsData;
use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Получение настроек пользователя.
 *
 * Этот Action инкапсулирует логику получения настроек пользователя
 * через пакет glorand/laravel-model-settings.
 * Выделен в отдельный класс для повторного использования и тестирования.
 */
final class GetUserSettings
{
    use AsAction;

    /**
     * Возвращает Data-объект с настройками пользователя.
     *
     * @param  int  $userId  ID пользователя
     * @return UserSettingsData Data-объект с настройками
     */
    public function handle(int $userId): UserSettingsData
    {
        /** @var User $user */
        $user = User::query()->findOrFail($userId);

        /** @var array{paginate?: int, fresh_first?: bool, show_starred?: bool, latest_first?: bool, known_enabled?: bool, main_language?: string, show_imported?: bool, languages_list?: string[], starred_enabled?: bool, default_language?: string, show_shared?: bool} $settings */
        $settings = $user->settings()->all();

        return new UserSettingsData(
            paginate: $settings['paginate'] ?? 20,
            fresh_first: $settings['fresh_first'] ?? true,
            show_starred: $settings['show_starred'] ?? true,
            latest_first: $settings['latest_first'] ?? true,
            known_enabled: $settings['known_enabled'] ?? false,
            main_language: $settings['main_language'] ?? 'RU',
            show_imported: $settings['show_imported'] ?? true,
            languages_list: $settings['languages_list'] ?? ['DE', 'EN'], // @phpstan-ignore-line
            starred_enabled: $settings['starred_enabled'] ?? false,
            default_language: $settings['default_language'] ?? 'DE',
            show_shared: $settings['show_shared'] ?? true,
        );
    }
}
