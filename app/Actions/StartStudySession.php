<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\WordData;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Запуск сессии изучения слов.
 *
 * Этот Action управляет началом сессии изучения слов:
 * - Получает случайные слова через GetUserRandomWords
 * - Сохраняет состояние сессии в кэше (start, current, next, prev)
 * - Возвращает текущее слово с мета-информацией о сессии
 * - Если сессия уже существует, использует существующие данные
 *
 * Выделен в отдельный класс для переиспользования между API и Inertia контроллерами.
 */
final readonly class StartStudySession
{
    use AsAction;

    public function __construct(
        private GetUserRandomWords $getUserRandomWords,
        private GetWord $getWord,
        private IncrementWordViews $incrementWordViews,
    ) {}

    /**
     * Запускает или возобновляет сессию изучения слов.
     *
     * @param  int  $userId  ID пользователя
     * @param  int  $limit  Количество слов для изучения
     * @param  bool  $reverse  Порядок слов (true - обратный, false - прямой)
     * @return array{word_data: WordData, total: int, next_id: ?int, prev_id: ?int, current_index: int}
     */
    public function handle(int $userId, int $limit = 20, bool $reverse = false): array
    {
        $startKey = "words.start.{$userId}";
        $currentKey = "words.current.{$userId}";
        $nextKey = "words.next.{$userId}";
        $prevKey = "words.prev.{$userId}";

        // Проверяем, существует ли уже активная сессия
        /** @var int[]|null $existingStart */
        $existingStart = Cache::get($startKey);
        /** @var ?int $existingCurrent */
        $existingCurrent = Cache::get($currentKey);
        /** @var ?int $existingNext */
        $existingNext = Cache::get($nextKey);

        if (is_array($existingStart) && is_int($existingCurrent) && is_int($existingNext)) {
            $word = $this->getWord->handle($existingCurrent, $userId);

            $this->incrementWordViews->handle($existingCurrent, $userId);

            $wordData = $word;

            if ($reverse) {
                $wordData = new WordData(
                    id: $wordData->id,
                    original: $wordData->translated,
                    translated: $wordData->original,
                    language: $wordData->language,
                    done_at: $wordData->done_at,
                    starred: $wordData->starred,
                    views: $wordData->views + 1, // Уже инкрементировано
                    from_sample: $wordData->from_sample,
                    user_id: $wordData->user_id,
                    created_at: $wordData->created_at,
                    updated_at: $wordData->updated_at,
                );
            } else {
                $wordData = new WordData(
                    id: $wordData->id,
                    original: $wordData->original,
                    translated: $wordData->translated,
                    language: $wordData->language,
                    done_at: $wordData->done_at,
                    starred: $wordData->starred,
                    views: $wordData->views + 1, // Уже инкрементировано
                    from_sample: $wordData->from_sample,
                    user_id: $wordData->user_id,
                    created_at: $wordData->created_at,
                    updated_at: $wordData->updated_at,
                );
            }

            $currentIndex = array_search($existingCurrent, $existingStart, true);
            if ($currentIndex === false) {
                $currentIndex = 0;
            }

            /** @var ?int $existingPrev */
            $existingPrev = Cache::get($prevKey);

            /** @var int $currentIndexInt */
            $currentIndexInt = $currentIndex;

            return [
                'word_data' => $wordData,
                'total' => count($existingStart),
                'next_id' => $existingNext,
                'prev_id' => is_int($existingPrev) ? $existingPrev : null,
                'current_index' => $currentIndexInt + 1,
            ];
        }

        $words = $this->getUserRandomWords->handle($userId, $limit);

        if ($words->isEmpty()) {
            throw new \RuntimeException('No words available for study session');
        }

        /** @var array<int> $wordIds */
        $wordIds = $words->map(fn (WordData $word) => $word->id)->toArray();

        if ($reverse) {
            $wordIds = array_reverse($wordIds);
        }

        Cache::put($startKey, $wordIds);

        $currentId = $wordIds[0];
        Cache::put($currentKey, $currentId);

        $nextId = $wordIds[1] ?? null;
        Cache::put($nextKey, $nextId);

        Cache::put($prevKey, null);

        $word = $this->getWord->handle($currentId, $userId);

        // Инкрементируем просмотры
        $this->incrementWordViews->handle($currentId, $userId);

        $wordData = $word;

        if ($reverse) {
            $wordData = new WordData(
                id: $wordData->id,
                original: $wordData->translated,
                translated: $wordData->original,
                language: $wordData->language,
                done_at: $wordData->done_at,
                starred: $wordData->starred,
                views: $wordData->views + 1, // Уже инкрементировано
                from_sample: $wordData->from_sample,
                user_id: $wordData->user_id,
                created_at: $wordData->created_at,
                updated_at: $wordData->updated_at,
            );
        } else {
            $wordData = new WordData(
                id: $wordData->id,
                original: $wordData->original,
                translated: $wordData->translated,
                language: $wordData->language,
                done_at: $wordData->done_at,
                starred: $wordData->starred,
                views: $wordData->views + 1, // Уже инкрементировано
                from_sample: $wordData->from_sample,
                user_id: $wordData->user_id,
                created_at: $wordData->created_at,
                updated_at: $wordData->updated_at,
            );
        }

        return [
            'word_data' => $wordData,
            'total' => count($wordIds),
            'next_id' => $nextId,
            'prev_id' => null,
            'current_index' => 1,
        ];
    }
}
