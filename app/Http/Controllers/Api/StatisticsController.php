<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\GetUserStatistics;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use App\Models\User;

#[Middleware('auth:sanctum')]
final class StatisticsController extends Controller
{
    /**
     * Возвращает статистику авторизованного пользователя.
     *
     * Возвращает статистику по словам пользователя:
     * - общее количество слов
     * - количество избранных слов
     * - количество изученных и неизученных слов
     * - общее количество просмотров
     * - топ-10 просматриваемых слов
     * - слова добавленные сегодня
     * - количество обновлённых слов сегодня и в этом месяце
     * - количество пользователей в системе
     *
     * @param  GetUserStatistics  $getUserStatistics  Action для получения статистики
     * @return JsonResponse JSON:API ответ со статистикой
     */
    #[Get('api/v1/statistics', name: 'api.v1.statistics')]
    public function statistics(GetUserStatistics $getUserStatistics): JsonResponse
    {
        /** @var User|null $user */
        $user = auth('sanctum')->user();

        if ($user === null) {
            return response()->json([
                'errors' => [
                    [
                        'status' => '401',
                        'title' => 'Unauthenticated',
                    ],
                ],
            ], Response::HTTP_UNAUTHORIZED, ['Content-Type' => 'application/vnd.api+json']);
        }

        $statistics = $getUserStatistics->handle(
            user: $user
        );

        return response()->json([
            'data' => $statistics->toJsonArray(),
        ], Response::HTTP_OK, ['Content-Type' => 'application/vnd.api+json']);
    }
}
