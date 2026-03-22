<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\GetUserWordsWithFilters;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\User;
use Inertia\Response;

/**
 * Контроллер для отображения списка выученных слов пользователя.
 *
 * Отображает таблицу выученных слов с пагинацией.
 * Позволяет пометить слова как невыученные через чекбоксы.
 */
final class LearnedWordsController
{
    /**
     * Отображает страницу со списком выученных слов.
     *
     * Возвращает Inertia-ответ с пагинированным списком выученных слов.
     * Слова фильтруются по полю done_at (не null).
     *
     * @param  GetUserWordsWithFilters  $getUserWordsWithFilters  Action для получения слов с фильтрами
     */
    public function __invoke(GetUserWordsWithFilters $getUserWordsWithFilters): Response
    {
        /** @var User $user */
        $user = Auth::user();

        $words = $getUserWordsWithFilters->handle($user->id, ['done' => 'true']);

        return Inertia::render('LearnedWords/index', [
            'words' => $words,
            'user' => $user,
        ]);
    }
}
