<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\GenerateWordExamples;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Client\RequestException;

#[Middleware('auth:sanctum')]
final class ExampleController extends Controller
{
    /**
     * Возвращает примеры использования слова.
     *
     * Генерирует примеры через OpenAI, если они отсутствуют.
     * Примеры включают: классику, афоризм и разговорный пример.
     *
     * @param  int  $word  ID слова (передается как route parameter)
     * @param  GenerateWordExamples  $generateExamples  Action для генерации примеров
     * @return JsonResponse JSON:API ответ с примерами
     */
    #[Get('api/v1/examples/{word}', name: 'api.v1.examples.show')]
    public function show(int $word, GenerateWordExamples $generateExamples): JsonResponse
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

        try {
            $exampleData = $generateExamples->handle(
                wordId: $word,
                userId: $user->id
            );

            return response()->json([
                'data' => $exampleData->toJsonArray(),
            ], Response::HTTP_OK, ['Content-Type' => 'application/vnd.api+json']);
        } catch (ModelNotFoundException) {
            return response()->json([
                'errors' => [
                    [
                        'status' => '404',
                        'title' => 'Not Found',
                        'detail' => 'Word not found or does not belong to user',
                    ],
                ],
            ], Response::HTTP_NOT_FOUND, ['Content-Type' => 'application/vnd.api+json']);
        } catch (\RuntimeException $e) {
            return response()->json([
                'errors' => [
                    [
                        'status' => '500',
                        'title' => 'Internal Server Error',
                        'detail' => $e->getMessage(),
                    ],
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR, ['Content-Type' => 'application/vnd.api+json']);
        } catch (RequestException) {
            return response()->json([
                'errors' => [
                    [
                        'status' => '502',
                        'title' => 'Bad Gateway',
                        'detail' => 'Failed to generate examples via OpenAI API',
                    ],
                ],
            ], Response::HTTP_BAD_GATEWAY, ['Content-Type' => 'application/vnd.api+json']);
        }
    }
}
