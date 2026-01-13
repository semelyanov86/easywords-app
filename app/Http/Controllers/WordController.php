<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateWord;
use App\Actions\GetUserSettings;
use App\Http\Requests\StoreWordWebRequest;
use Inertia\Inertia;

/**
 * Контроллер для управления словами через web-интерфейс.
 *
 * Предоставляет страницы для создания слов и обрабатывает форму добавления.
 */
final class WordController
{
    /**
     * Отображает форму создания нового слова.
     *
     * Передает список доступных языков из настроек пользователя.
     */
    public function create(GetUserSettings $getUserSettings): \Inertia\Response
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $settings = $getUserSettings->handle($user->id);

        return Inertia::render('Words/Create', [
            'languages_list' => $settings->languages_list ?? [],
            'user' => $user,
        ]);
    }

    /**
     * Сохраняет новое слово.
     *
     * Создает слово с привязкой к авторизованному пользователю.
     * Возвращает созданное слово для отображения успешного уведомления.
     */
    public function store(StoreWordWebRequest $request, CreateWord $createWord, GetUserSettings $getUserSettings): \Inertia\Response
    {
        /** @var array{original: string, translated: string, language: string} $validated */
        $validated = $request->validated();

        /** @var \App\Models\User $user */
        $user = auth()->user();
        $settings = $getUserSettings->handle($user->id);

        $wordData = $createWord->handle(
            userId: $user->id,
            original: $validated['original'],
            translated: $validated['translated'],
            language: $validated['language']
        );

        return Inertia::render('Words/Create', [
            'languages_list' => $settings->languages_list ?? [],
            'word' => $wordData,
            'user' => $user,
        ]);
    }
}
