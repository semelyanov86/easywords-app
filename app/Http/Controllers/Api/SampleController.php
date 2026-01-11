<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\ImportSamplesToWords;
use App\Data\ImportSamplesResponseData;
use App\Http\Controllers\Controller;
use App\Models\Sample;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;

#[Middleware('auth:sanctum')]
final class SampleController extends Controller
{
    /**
     * Импортирует samples указанного языка в личный словарь пользователя.
     *
     * Создаёт слова из глобальных samples, пропуская те, что уже есть у пользователя.
     *
     * @param  string  $language  Код языка (например: 'EN', 'DE')
     * @return JsonResponse JSON:API ответ с информацией о созданных словах
     * @throws ValidationException
     */
    #[Post('api/v1/samples/import/{language}', name: 'api.v1.samples.import')]
    public function import(Request $request, string $language, ImportSamplesToWords $action): JsonResponse
    {
        /** @var User|null $user */
        $user = auth('sanctum')->user();

        if (! $user) {
            return response()->json([
                'errors' => [
                    [
                        'status' => '401',
                        'title' => 'Unauthenticated',
                    ],
                ],
            ], Response::HTTP_UNAUTHORIZED, ['Content-Type' => 'application/vnd.api+json']);
        }

        $createdWords = $action->handle($user, $language);

        $totalSamples = Sample::where('language', $language)->count();
        $totalSkipped = $totalSamples - $createdWords->count();

        $responseData = new ImportSamplesResponseData(
            words: $createdWords,
            total_created: $createdWords->count(),
            total_skipped: $totalSkipped,
        );

        return $responseData->toResponse($request);
    }
}
