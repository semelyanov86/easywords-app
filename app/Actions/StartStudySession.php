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
        $session = $this->loadExistingSession($userId);

        if ($session !== null) {
            return $this->resumeSession($session, $userId, $reverse);
        }

        return $this->startNewSession($userId, $limit, $reverse);
    }

    /**
     * @return array{wordIds: array<int>, currentId: int, nextId: ?int, prevId: ?int}|null
     */
    private function loadExistingSession(int $userId): ?array
    {
        /** @var array<int>|null $wordIds */
        $wordIds = Cache::get($this->cacheKey('start', $userId));
        /** @var int|null $currentId */
        $currentId = Cache::get($this->cacheKey('current', $userId));
        /** @var int|null $nextId */
        $nextId = Cache::get($this->cacheKey('next', $userId));

        if (! is_array($wordIds) || ! is_int($currentId) || ! is_int($nextId)) {
            return null;
        }

        /** @var int|null $prevId */
        $prevId = Cache::get($this->cacheKey('prev', $userId));

        return [
            'wordIds' => $wordIds,
            'currentId' => $currentId,
            'nextId' => $nextId,
            'prevId' => $prevId,
        ];
    }

    /**
     * @param  array{wordIds: array<int>, currentId: int, nextId: ?int, prevId: ?int}  $session
     * @return array{word_data: WordData, total: int, next_id: ?int, prev_id: ?int, current_index: int}
     */
    private function resumeSession(array $session, int $userId, bool $reverse): array
    {
        $word = $this->getWord->handle($session['currentId'], $userId);
        $this->incrementWordViews->handle($session['currentId'], $userId);
        /** @var int|false $currentIndex */
        $currentIndex = array_search($session['currentId'], $session['wordIds'], true);

        return [
            'word_data' => $this->prepareWordData($word, $reverse),
            'total' => count($session['wordIds']),
            'next_id' => $session['nextId'],
            'prev_id' => $session['prevId'],
            'current_index' => ($currentIndex !== false ? $currentIndex : 0) + 1,
        ];
    }

    /**
     * @return array{word_data: WordData, total: int, next_id: ?int, prev_id: ?int, current_index: int}
     */
    private function startNewSession(int $userId, int $limit, bool $reverse): array
    {
        $words = $this->getUserRandomWords->handle($userId, $limit);

        if ($words->isEmpty()) {
            throw new \RuntimeException('No words available for study session');
        }

        /** @var array<int> $wordIds */
        $wordIds = $words->map(fn (WordData $word) => $word->id)->toArray();

        if ($reverse) {
            $wordIds = array_reverse($wordIds);
        }

        $this->saveSession($userId, $wordIds);

        $word = $this->getWord->handle($wordIds[0], $userId);
        $this->incrementWordViews->handle($wordIds[0], $userId);

        return [
            'word_data' => $this->prepareWordData($word, $reverse),
            'total' => count($wordIds),
            'next_id' => $wordIds[1] ?? null,
            'prev_id' => null,
            'current_index' => 1,
        ];
    }

    /**
     * @param  array<int>  $wordIds
     */
    private function saveSession(int $userId, array $wordIds): void
    {
        Cache::put($this->cacheKey('start', $userId), $wordIds);
        Cache::put($this->cacheKey('current', $userId), $wordIds[0]);
        Cache::put($this->cacheKey('next', $userId), $wordIds[1] ?? null);
        Cache::put($this->cacheKey('prev', $userId), null);
    }

    private function prepareWordData(WordData $word, bool $reverse): WordData
    {
        if (! $reverse) {
            return $word;
        }

        return new WordData(
            id: $word->id,
            original: $word->translated,
            translated: $word->original,
            language: $word->language,
            done_at: $word->done_at,
            starred: $word->starred,
            views: $word->views,
            from_sample: $word->from_sample,
            user_id: $word->user_id,
            created_at: $word->created_at,
            updated_at: $word->updated_at,
        );
    }

    private function cacheKey(string $type, int $userId): string
    {
        return "words.{$type}.{$userId}";
    }
}
