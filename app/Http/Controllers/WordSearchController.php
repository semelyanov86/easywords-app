<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\SearchUserWords;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Контроллер для поиска слов пользователя.
 *
 * Обрабатывает поисковый запрос и отображает страницу с результатами поиска.
 * Поиск выполняется по полям original и translated с использованием Action SearchUserWords.
 */
final class WordSearchController
{
    /**
     * Отображает страницу с результатами поиска слов.
     *
     * Принимает параметр 'q' с поисковым запросом через GET-параметр или форму.
     * Передает авторизованного пользователя и результаты поиска на страницу.
     *
     * @param  Request  $request  HTTP запрос с параметром 'q' (поисковый запрос)
     * @param  SearchUserWords  $searchUserWords  Action для поиска слов
     * @return \Inertia\Response Ответ Inertia со страницей поиска
     */
    public function __invoke(Request $request, SearchUserWords $searchUserWords): \Inertia\Response
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        /** @var ?string $query */
        $query = $request->input('q', '');

        /** @var array<int, \App\Data\WordData> $results */
        $results = $searchUserWords->handle($user->id, $query);

        return Inertia::render('Words/Search', [
            'query' => $query,
            'results' => $results,
            'user' => $user,
        ]);
    }
}
