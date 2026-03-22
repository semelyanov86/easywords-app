<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\CreateWord;
use App\Actions\DeleteWord;
use App\Actions\GetUserRandomWords;
use App\Actions\GetUserSettings;
use App\Actions\GetUserWords;
use App\Actions\GetUserWordsWithFilters;
use App\Actions\GetWord;
use App\Actions\IncrementWordViews;
use App\Actions\MarkWordAsLearned;
use App\Actions\SearchUserWords;
use App\Actions\ShareWord;
use App\Actions\ToggleWordStarred;
use App\Data\WordData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GetRandomWordsRequest;
use App\Http\Requests\Api\GetUserWordsRequest;
use App\Http\Requests\Api\GetUserWordsWithFiltersRequest;
use App\Http\Requests\Api\SearchUserWordsRequest;
use App\Http\Requests\Api\ShareWordRequest;
use App\Http\Requests\Api\StoreWordRequest;
use App\Models\User;
use App\Models\Word;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Put;
use Illuminate\Database\Eloquent\ModelNotFoundException;

#[Middleware('auth:sanctum')]
final class WordController extends Controller
{
    /**
     * Возвращает список слов авторизованного пользователя с фильтрацией.
     *
     * Получает список слов с фильтрацией по GET-параметрам:
     * - done: фильтрация по статусу изучения (true/false)
     * - shared: фильтрация по общим словам (true/false)
     * - from_sample: фильтрация по источнику (true/false)
     * - starred: фильтрация по избранным (true/false)
     * Игнорирует настройки пользователя из UserSettingsData.
     *
     * @param  GetUserWordsWithFiltersRequest  $request  Валидированный запрос с фильтрами
     * @param  GetUserWordsWithFilters  $getUserWordsWithFilters  Action для получения списка слов с фильтрами
     * @return JsonResponse JSON:API ответ с пагинированным списком слов
     */
    #[Get('api/v1/words/with-filters', name: 'api.v1.words.filtered')]
    public function filtered(GetUserWordsWithFiltersRequest $request, GetUserWordsWithFilters $getUserWordsWithFilters): JsonResponse
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

        $dataCollection = $getUserWordsWithFilters->handle(
            userId: $user->id,
            filters: $request->filters()
        );

        $data = collect($dataCollection->all())
            ->map(fn (mixed $wordData) => $wordData instanceof WordData ? $wordData->toJsonArray() : null)
            ->filter(fn (?array $item) => $item !== null)
            ->values();

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => '1',
                'per_page' => (string) $data->count(),
                'total' => (string) $data->count(),
                'last_page' => '1',
            ],
        ], Response::HTTP_OK, ['Content-Type' => 'application/vnd.api+json']);
    }

    /**
     * Ищет слова авторизованного пользователя по строке запроса.
     *
     * Ищет слова по полям original и translated с использованием частичного совпадения (%query%).
     * Поиск выполняется только среди слов, принадлежащих авторизованному пользователю.
     *
     * @param  SearchUserWordsRequest  $request  Валидированный запрос с параметром query
     * @param  SearchUserWords  $searchUserWords  Action для поиска слов
     * @return JsonResponse JSON:API ответ с массивом найденных слов
     */
    #[Get('api/v1/words/search', name: 'api.v1.words.search')]
    public function search(SearchUserWordsRequest $request, SearchUserWords $searchUserWords): JsonResponse
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

        $words = $searchUserWords->handle(
            userId: $user->id,
            query: $request->validatedQuery()
        );

        $data = collect($words)
            ->map(fn (WordData $wordData) => $wordData->toJsonArray())
            ->filter(fn (?array $item) => $item !== null)
            ->values();

        return response()->json([
            'data' => $data,
        ], Response::HTTP_OK, ['Content-Type' => 'application/vnd.api+json']);
    }

    /**
     * Возвращает слово по ID.
     *
     * Проверяет, что слово принадлежит авторизованному пользователю.
     *
     * @param  int  $word  ID слова (route model binding)
     * @param  GetWord  $getWord  Action для получения слова
     * @return JsonResponse JSON:API ответ со словом
     */
    #[Get('api/v1/words/{word}', name: 'api.v1.words.show')]
    public function show(int $word, GetWord $getWord): JsonResponse
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
            $wordData = $getWord->handle(
                wordId: $word,
                userId: $user->id
            );

            return response()->json([
                'data' => $wordData->toJsonArray(),
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
        }
    }

    /**
     * Удаляет слово по ID.
     *
     * Проверяет, что слово принадлежит авторизованному пользователю.
     *
     * @param  int  $word  ID слова (route model binding)
     * @param  DeleteWord  $deleteWord  Action для удаления слова
     * @return JsonResponse Пустой ответ со статусом 204 No Content
     */
    #[Delete('api/v1/words/{word}', name: 'api.v1.words.destroy')]
    public function destroy(int $word, DeleteWord $deleteWord): JsonResponse
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
            $deleteWord->handle(
                wordId: $word,
                userId: $user->id
            );

            return response()->json(null, Response::HTTP_NO_CONTENT, ['Content-Type' => 'application/vnd.api+json']);
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
        }
    }

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
     * Возвращает список слов авторизованного пользователя.
     *
     * Получает список слов с учётом настроек пользователя:
     * - фильтрация по языку (обязательный параметр language)
     * - режим сортировки (fresh_first или по просмотрам)
     * - показ/скрытие изученных, импортированных и общих слов
     *
     * @param  GetUserWordsRequest  $request  Валидированный запрос с параметром language
     * @param  GetUserWords  $getUserWords  Action для получения списка слов
     * @return JsonResponse JSON:API ответ с пагинированным списком слов
     */
    #[Get('api/v1/words', name: 'api.v1.words.index')]
    public function index(GetUserWordsRequest $request, GetUserWords $getUserWords): JsonResponse
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

        $dataCollection = $getUserWords->handle(
            userId: $user->id,
            language: $request->validatedLanguage(),
        );

        $data = collect($dataCollection->all())
            ->map(fn (mixed $wordData) => $wordData instanceof WordData ? $wordData->toJsonArray() : null)
            ->filter(fn (?array $item) => $item !== null)
            ->values();

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => 1,
                'per_page' => $data->count(),
                'total' => $data->count(),
                'last_page' => 1,
            ],
        ], Response::HTTP_OK, ['Content-Type' => 'application/vnd.api+json']);
    }

    /**
     * Создает новое слово для авторизованного пользователя.
     *
     * Принимает original, translated и language, создает слово с привязкой к текущему пользователю.
     *
     * @param  StoreWordRequest  $request  Валидированный запрос с данными слова
     * @return JsonResponse JSON:API ответ с созданным словом
     */
    #[Post('api/v1/words', name: 'api.v1.words.store')]
    public function store(StoreWordRequest $request, CreateWord $createWord): JsonResponse
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
        /** @var User|null $user */
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
     * Переключает статус избранного у слова.
     *
     * Автоматически переключает starred на противоположное значение.
     *
     * @param  Word  $word  Слово для обновления
     * @param  ToggleWordStarred  $toggleStarred  Action для переключения starred
     * @return JsonResponse JSON:API ответ с обновлённым словом
     */
    #[Put('api/v1/words/{word}/starred', name: 'api.v1.words.starred')]
    public function toggleStarred(Word $word, ToggleWordStarred $toggleStarred): JsonResponse
    {
        /** @var User|null $user */
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

        $isStarred = $toggleStarred->handle(
            wordId: $word->id,
            userId: $user->id
        );

        // Обновляем слово из базы данных
        $word->refresh();

        return response()->json([
            'data' => WordData::from($word)->toJsonArray(),
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
        /** @var User|null $user */
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

    /**
     * Делится словом с другим пользователем.
     *
     * Создает копию слова для указанного пользователя на основе оригинального слова.
     * Проверяет, что слово принадлежит авторизованному пользователю.
     *
     * @param  ShareWordRequest  $request  Валидированный запрос с word_id и user_id
     * @param  ShareWord  $shareWord  Action для шаринга слова
     * @return JsonResponse JSON:API ответ с созданным словом
     */
    #[Post('api/v1/words/share', name: 'api.v1.words.share')]
    public function share(ShareWordRequest $request, ShareWord $shareWord): JsonResponse
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

        /** @var array{word_id: int, user_id: int} $validated */
        $validated = $request->validated();

        $originalWord = Word::findOrFail($validated['word_id']);

        if ($originalWord->user_id !== $user->id) {
            return response()->json([
                'errors' => [
                    [
                        'status' => '403',
                        'title' => 'Forbidden',
                        'detail' => 'Word does not belong to user',
                    ],
                ],
            ], Response::HTTP_FORBIDDEN, ['Content-Type' => 'application/vnd.api+json']);
        }

        $targetUser = User::findOrFail($validated['user_id']);

        $sharedWord = $shareWord->handle(
            word: $originalWord,
            targetUser: $targetUser,
            author: $user
        );

        return response()->json([
            'data' => WordData::from($sharedWord)->toJsonArray(),
        ], Response::HTTP_CREATED, ['Content-Type' => 'application/vnd.api+json']);
    }
}
