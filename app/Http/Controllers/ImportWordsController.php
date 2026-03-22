<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\GetUserSettings;
use App\Actions\ImportSamplesToWords;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

/**
 * Контроллер для импорта sample слов в словарь пользователя.
 *
 * Предназначен для массового добавления готовых примеров слов из
 * глобальной коллекции samples в личный словарь авторизованного пользователя.
 * Использует язык, установленный по умолчанию в настройках пользователя.
 */
final class ImportWordsController
{
    /**
     * Импортирует слова для языка по умолчанию пользователя.
     *
     * Получает язык по умолчанию из настроек пользователя (main_language),
     * затем импортирует все sample слова для этого языка в словарь пользователя.
     * Пропускает слова, которые уже есть у пользователя.
     * После успешного импорта возвращает пользователя назад с уведомлением.
     *
     * @return RedirectResponse Перенаправление назад с уведомлением о результате
     */
    public function __invoke(GetUserSettings $getUserSettings): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $settings = $getUserSettings->handle($user->id);
        $language = $settings->main_language;

        $importedWords = ImportSamplesToWords::make()->handle($user, $language);
        $importedCount = $importedWords->count();

        if ($importedCount === 0) {
            return to_route('settings.show')
                ->with('info', __('import.no_words_imported'));
        }

        return to_route('settings.show')
            ->with('success', __('import.words_imported', ['count' => $importedCount]));
    }
}
