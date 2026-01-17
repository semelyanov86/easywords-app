<?php

declare(strict_types=1);

namespace App\Support;

use App\Data\WordData;

/**
 * Управление кэшем сессии изучения слов.
 *
 * Инкапсулирует всю логику работы с кэшированным состоянием сессии:
 * - Загрузка и сохранение списка слов
 * - Управление указателями навигации (current, next, prev)
 * - Подготовка мета-информации о сессии
 */
final readonly class StudySessionCache
{
    /**
     * Загружает список ID слов из текущей сессии.
     *
     * @return array<int>
     *
     * @throws \RuntimeException
     */
    public function getSessionWords(int $userId): array
    {
        /** @var array<int>|null $sessionWords */
        $sessionWords = cache()->get($this->key('start', $userId));

        if (! is_array($sessionWords)) {
            throw new \RuntimeException('Study session not found');
        }

        return $sessionWords;
    }

    /**
     * Получает ID текущего слова.
     */
    public function getCurrentId(int $userId): ?int
    {
        /** @var int|null $currentId */
        $currentId = cache()->get($this->key('current', $userId));

        return $currentId;
    }

    /**
     * Получает ID следующего слова.
     */
    public function getNextId(int $userId): ?int
    {
        /** @var int|null $nextId */
        $nextId = cache()->get($this->key('next', $userId));

        return $nextId;
    }

    /**
     * Получает ID предыдущего слова.
     */
    public function getPrevId(int $userId): ?int
    {
        /** @var int|null $prevId */
        $prevId = cache()->get($this->key('prev', $userId));

        return $prevId;
    }

    /**
     * Обновляет все указатели навигации на основе текущего индекса.
     *
     * @param  array<int>  $sessionWords
     */
    public function updateNavigation(int $userId, int $currentId, int $currentIndex, array $sessionWords): void
    {
        cache()->put($this->key('current', $userId), $currentId);

        $prevIndex = $currentIndex - 1;
        $prevId = ($prevIndex >= 0 && isset($sessionWords[$prevIndex]))
            ? $sessionWords[$prevIndex]
            : null;
        cache()->put($this->key('prev', $userId), $prevId);

        $nextIndex = $currentIndex + 1;
        $nextId = $sessionWords[$nextIndex] ?? null;
        cache()->put($this->key('next', $userId), $nextId);
    }

    /**
     * Находит индекс слова в сессии.
     *
     * @param  array<int>  $sessionWords
     *
     * @throws \RuntimeException
     */
    public function findWordIndex(int $wordId, array $sessionWords): int
    {
        $index = array_search($wordId, $sessionWords, true);

        if ($index === false) {
            throw new \RuntimeException('Word not found in session');
        }

        return (int) $index;
    }

    /**
     * Формирует мета-информацию о текущем состоянии сессии.
     *
     * @param  array<int>  $sessionWords
     * @return array{total: int, next_id: ?int, prev_id: ?int, current_index: int}
     */
    public function buildMeta(int $userId, int $currentIndex, array $sessionWords): array
    {
        return [
            'total' => count($sessionWords),
            'next_id' => $this->getNextId($userId),
            'prev_id' => $this->getPrevId($userId),
            'current_index' => $currentIndex + 1, // 1-based
        ];
    }

    /**
     * Сохраняет начальное состояние сессии.
     *
     * @param  array<int>  $wordIds
     */
    public function saveSession(int $userId, array $wordIds): void
    {
        cache()->put($this->key('start', $userId), $wordIds);
        cache()->put($this->key('current', $userId), $wordIds[0]);
        cache()->put($this->key('next', $userId), $wordIds[1] ?? null);
        cache()->put($this->key('prev', $userId), null);
    }

    /**
     * Подготавливает WordData с учётом режима reverse.
     */
    public function prepareWordData(WordData $word, bool $reverse): WordData
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

    private function key(string $type, int $userId): string
    {
        return "words.{$type}.{$userId}";
    }
}
