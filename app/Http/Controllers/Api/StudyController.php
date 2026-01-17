<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\StartStudySession;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StartStudyRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;

#[Middleware('auth:sanctum')]
final class StudyController extends Controller
{
    /**
     * Запускает сессию изучения слов.
     *
     * Получает случайные слова для изучения, сохраняет состояние сессии в кэше
     * и возвращает текущее слово с мета-информацией о прогрессе.
     *
     * Параметры запроса:
     * - limit: количество слов для изучения (опционально, по умолчанию 20)
     * - reverse: порядок слов (опционально, true/false, по умолчанию false)
     *
     * Возвращает JSON:API ответ с текущим словом и мета-данными:
     * - total: количество слов в сессии
     * - next_id: ID следующего слова
     * - prev_id: ID предыдущего слова
     * - current_index: индекс текущего слова (начинается с 1)
     *
     * @param  StartStudyRequest  $request  Валидированный запрос с параметрами
     * @param  StartStudySession  $startStudySession  Action для запуска сессии
     * @return JsonResponse JSON:API ответ с текущим словом и мета-данными
     */
    #[Get('api/v1/words/start', name: 'api.v1.words.start')]
    public function start(StartStudyRequest $request, StartStudySession $startStudySession): JsonResponse
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

        try {
            $result = $startStudySession->handle(
                userId: $user->id,
                limit: $request->validatedLimit(),
                reverse: $request->validatedReverse()
            );

            return response()->json([
                'data' => $result['word_data']->toJsonArray(),
                'meta' => [
                    'total' => $result['total'],
                    'next_id' => $result['next_id'],
                    'prev_id' => $result['prev_id'],
                    'current_index' => $result['current_index'],
                ],
            ], Response::HTTP_OK, ['Content-Type' => 'application/vnd.api+json']);
        } catch (\RuntimeException $e) {
            return response()->json([
                'errors' => [
                    [
                        'status' => '400',
                        'title' => 'Bad Request',
                        'detail' => $e->getMessage(),
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST, ['Content-Type' => 'application/vnd.api+json']);
        }
    }
}
