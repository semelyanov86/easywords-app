<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\WordData;
use App\Models\User;
use App\Support\StudySessionCache;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Переход к следующему слову в обучающей сессии.
 *
 * Этот Action управляет навигацией вперёд по словам в сессии изучения:
 * - Берёт следующее слово из кэша
 * - Обновляет текущее слово и устанавливает новое следующее/предыдущее
 * - Инкрементирует счётчик просмотров
 * - Возвращает WordData с метаданными сессии
 */
final readonly class GoToNextWord
{
    use AsAction;

    public function __construct(
        private StudySessionCache $sessionCache,
        private GetWord $getWord,
        private IncrementWordViews $incrementWordViews,
    ) {}

    /**
     * @return array{word: WordData, meta: array{total: int, next_id: ?int, prev_id: ?int, current_index: int}}
     *
     * @throws \RuntimeException
     */
    public function handle(User $user, bool $reverse = false): array
    {
        $nextId = $this->sessionCache->getNextId($user->id);

        if ($nextId === null) {
            throw new \RuntimeException('No next word available');
        }

        $sessionWords = $this->sessionCache->getSessionWords($user->id);
        $currentIndex = $this->sessionCache->findWordIndex($nextId, $sessionWords);

        $this->sessionCache->updateNavigation($user->id, $nextId, $currentIndex, $sessionWords);

        $word = $this->getWord->handle($nextId, $user->id);
        $this->incrementWordViews->handle($nextId, $user->id);

        return [
            'word' => $this->sessionCache->prepareWordData($word, $reverse),
            'meta' => $this->sessionCache->buildMeta($user->id, $currentIndex, $sessionWords),
        ];
    }
}
