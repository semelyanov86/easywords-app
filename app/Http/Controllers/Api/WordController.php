<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\GetUserRandomWords;
use App\Actions\GetUserSettings;
use App\Data\WordData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GetRandomWordsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;

#[Middleware('auth:sanctum')]
final class WordController extends Controller
{
    /**
     * Возвращает случайные слова авторизованного пользователя.
     *
     * Получает случайные слова с учётом настроек пользователя:
     * - фильтрация по языку (default_language)
     * - исключение изученных слов (если known_enabled === false)
     * - ограничение количества слов (по умолчанию 20)
     *
     * @param  GetRandomWordsRequest  $request  Валидированный запрос с параметром limit
     * @return JsonResponse JSON:API ответ с массивом случайных слов
     */
    #[Get('api/v1/random', name: 'api.v1.words.random')]
    public function getRandom(GetRandomWordsRequest $request, GetUserSettings $getSettings, GetUserRandomWords $getRandomWords): JsonResponse
    {
        /** @var \App\Models\User|null $user */
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

        $words = $getRandomWords->handle(
            userId: $user->id,
            limit: $request->validatedLimit()
        );

        $data = $words->map(fn (WordData $word) => $word->toJsonArray());

        return response()->json([
            'data' => $data,
        ], Response::HTTP_OK, ['Content-Type' => 'application/vnd.api+json']);
    }
}
