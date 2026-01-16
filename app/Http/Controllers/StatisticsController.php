<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\GetUserStatistics;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Контроллер страницы персональной статистики пользователя.
 *
 * Вынесен в отдельный класс для соблюдения принципа single responsibility controller.
 * Использует Action GetUserStatistics для получения данных статистики.
 */
final class StatisticsController
{
    /**
     * Отображает страницу статистики пользователя.
     *
     * @param  Request  $request  HTTP запрос
     * @return Response Inertia ответ с данными статистики
     */
    public function __invoke(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $statistics = GetUserStatistics::run($request->user());

        return Inertia::render('statistics/index', [
            'statistics' => $statistics,
            'user' => $user,
        ]);
    }
}
