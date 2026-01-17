<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\WordData;
use App\Support\StudySessionCache;
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
        private StudySessionCache $sessionCache,
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
     * @return array{word: WordData, total: int, next_id: ?int, prev_id: ?int, current_index: int}
     */
    public function handle(int $userId, int $limit = 20, bool $reverse = false): array
    {
        $currentId = $this->sessionCache->getCurrentId($userId);
        $nextId = $this->sessionCache->getNextId($userId);

        if ($currentId !== null && $nextId !== null) {
            return $this->resumeSession($userId, $currentId, $reverse);
        }

        return $this->startNewSession($userId, $limit, $reverse);
    }

    /**
     * @return array{word: WordData, total: int, next_id: ?int, prev_id: ?int, current_index: int}
     */
    private function resumeSession(int $userId, int $currentId, bool $reverse): array
    {
        $sessionWords = $this->sessionCache->getSessionWords($userId);
        $currentIndex = $this->sessionCache->findWordIndex($currentId, $sessionWords);

        $word = $this->getWord->handle($currentId, $userId);
        $this->incrementWordViews->handle($currentId, $userId);

        return [
            'word' => $this->sessionCache->prepareWordData($word, $reverse),
            'total' => count($sessionWords),
            'next_id' => $this->sessionCache->getNextId($userId),
            'prev_id' => $this->sessionCache->getPrevId($userId),
            'current_index' => $currentIndex + 1,
        ];
    }

    /**
     * @return array{word: WordData, total: int, next_id: ?int, prev_id: ?int, current_index: int}
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

        $this->sessionCache->saveSession($userId, $wordIds);

        $word = $this->getWord->handle($wordIds[0], $userId);
        $this->incrementWordViews->handle($wordIds[0], $userId);

        return [
            'word' => $this->sessionCache->prepareWordData($word, $reverse),
            'total' => count($wordIds),
            'next_id' => $wordIds[1] ?? null,
            'prev_id' => null,
            'current_index' => 1,
        ];
    }
}
