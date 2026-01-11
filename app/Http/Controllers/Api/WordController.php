<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\CreateWord;
use App\Actions\GetUserRandomWords;
use App\Actions\GetUserSettings;
use App\Actions\IncrementWordViews;
use App\Actions\MarkWordAsLearned;
use App\Actions\ToggleWordStarred;
use App\Data\WordData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GetRandomWordsRequest;
use App\Http\Requests\Api\StoreWordRequest;
use App\Http\Requests\Api\UpdateWordRequest;
use App\Models\Word;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Put;

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

    /**
     * Создаёт новое слово для авторизованного пользователя.
     *
     * Принимает original, translated и language, создаёт слово с привязкой к текущему пользователю.
     *
     * @param  StoreWordRequest  $request  Валидированный запрос с данными слова
     * @return JsonResponse JSON:API ответ с созданным словом
     */
    #[Post('api/v1/words', name: 'api.v1.words.store')]
    public function store(StoreWordRequest $request, CreateWord $createWord): JsonResponse
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

        /** @var array{original: string, translated: string, language: string} $validated */
        $validated = $request->validated();

        $wordData = $createWord->handle(
            userId: $user->id,
            original: $validated['original'],
            translated: $validated['translated'],
            language: $validated['language']
        );

        return response()->json([
            'data' => $wordData->toJsonArray(),
        ], Response::HTTP_CREATED, ['Content-Type' => 'application/vnd.api+json']);
    }

    /**
     * Увеличивает счётчик просмотров слова на 1.
     *
     * @param  Word  $word  Слово для просмотра
     * @param  IncrementWordViews  $incrementViews  Action для увеличения просмотров
     * @return JsonResponse JSON:API ответ с обновлённым словом
     */
    #[Post('api/v1/words/{word}/views', name: 'api.v1.words.views')]
    public function incrementViews(Word $word, IncrementWordViews $incrementViews): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = auth('sanctum')->user();

        if ($user === null || $word->user_id !== $user->id) {
            return response()->json([
                'errors' => [
                    [
                        'status' => '403',
                        'title' => 'Forbidden',
                    ],
                ],
            ], Response::HTTP_FORBIDDEN, ['Content-Type' => 'application/vnd.api+json']);
        }

        $wordData = $incrementViews->handle(
            wordId: $word->id,
            userId: $user->id
        );

        return response()->json([
            'data' => $wordData->toJsonArray(),
        ], Response::HTTP_OK, ['Content-Type' => 'application/vnd.api+json']);
    }

    /**
     * Обновляет статус избранного у слова.
     *
     * Принимает параметр starred (true или false) и меняет статус слова.
     *
     * @param  Word  $word  Слово для обновления
     * @param  UpdateWordRequest  $request  Валидированный запрос со статусом starred
     * @param  ToggleWordStarred  $toggleStarred  Action для изменения starred
     * @return JsonResponse JSON:API ответ с обновлённым словом
     */
    #[Put('api/v1/words/{word}/starred', name: 'api.v1.words.starred')]
    public function toggleStarred(Word $word, UpdateWordRequest $request, ToggleWordStarred $toggleStarred): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = auth('sanctum')->user();

        if ($user === null || $word->user_id !== $user->id) {
            return response()->json([
                'errors' => [
                    [
                        'status' => '403',
                        'title' => 'Forbidden',
                    ],
                ],
            ], Response::HTTP_FORBIDDEN, ['Content-Type' => 'application/vnd.api+json']);
        }

        $wordData = $toggleStarred->handle(
            wordId: $word->id,
            userId: $user->id,
            starred: $request->validatedStarred()
        );

        return response()->json([
            'data' => $wordData->toJsonArray(),
        ], Response::HTTP_OK, ['Content-Type' => 'application/vnd.api+json']);
    }

    /**
     * Помечает слово как выученное.
     *
     * Устанавливает поле done_at в текущее время.
     *
     * @param  Word  $word  Слово для пометки
     * @param  MarkWordAsLearned  $markAsLearned  Action для пометки как выученное
     * @return JsonResponse JSON:API ответ с обновлённым словом
     */
    #[Post('api/v1/words/{word}/learned', name: 'api.v1.words.learned')]
    public function markAsLearned(Word $word, MarkWordAsLearned $markAsLearned): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = auth('sanctum')->user();

        if ($user === null || $word->user_id !== $user->id) {
            return response()->json([
                'errors' => [
                    [
                        'status' => '403',
                        'title' => 'Forbidden',
                    ],
                ],
            ], Response::HTTP_FORBIDDEN, ['Content-Type' => 'application/vnd.api+json']);
        }

        $wordData = $markAsLearned->handle(
            wordId: $word->id,
            userId: $user->id
        );

        return response()->json([
            'data' => $wordData->toJsonArray(),
        ], Response::HTTP_OK, ['Content-Type' => 'application/vnd.api+json']);
    }
}
