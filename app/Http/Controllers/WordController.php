<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateWord;
use App\Actions\GetUserSettings;
use App\Actions\GetWord;
use App\Actions\IncrementWordViews;
use App\Http\Requests\StoreWordWebRequest;
use Illuminate\Http\Request;
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
    public function store(StoreWordWebRequest $request, CreateWord $createWord, GetUserSettings $getUserSettings): \Illuminate\Http\JsonResponse|\Inertia\Response
    {
        /** @var array{original: string, translated: string, language: string} $validated */
        $validated = $request->validated();

        /** @var \App\Models\User $user */
        $user = auth()->user();

        $wordData = $createWord->handle(
            userId: $user->id,
            original: $validated['original'],
            translated: $validated['translated'],
            language: $validated['language']
        );

        // Для JSON запросов
        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('Word ":word" has been added successfully', ['word' => $wordData->original]),
                'word' => $wordData,
            ]);
        }

        $settings = $getUserSettings->handle($user->id);

        return Inertia::render('Words/Create', [
            'languages_list' => $settings->languages_list ?? [],
            'word' => $wordData,
            'user' => $user,
        ]);
    }

    /**
     * Отображает страницу изучения слова.
     *
     * Показывает карточку слова с возможностью перевернуть, отметить как выученное,
     * удалить, добавить в избранное и поделиться с другим пользователем.
     * Принимает опциональные мета-данные для навигации между словами.
     */
    public function show(Request $request, GetWord $getWord, IncrementWordViews $increment, int $id): \Inertia\Response
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $word = $getWord->handle($id, $user->id);
        $increment->handle($word->id, $user->id);

        return Inertia::render('Words/Show', [
            'word' => $word,
            'user' => $user,
            'meta' => null, // Для прямого доступа к слову мета-данные не передаются
        ]);
    }
}
