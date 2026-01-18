<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\WordData;
use App\Models\User;
use App\Support\StudySessionCache;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Переход к предыдущему слову в обучающей сессии.
 *
 * Этот Action управляет навигацией назад по словам в сессии изучения:
 * - Берёт предыдущее слово из кэша
 * - Обновляет текущее слово и устанавливает новое следующее/предыдущее
 * - Инкрементирует счётчик просмотров
 * - Возвращает WordData с метаданными сессии
 */
final readonly class GoToPrevWord
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
    public function handle(User $user, string $language, bool $reverse = false): array
    {
        $prevId = $this->sessionCache->getPrevId($user->id, $language);

        if ($prevId === null) {
            $prevId = $this->sessionCache->getCurrentId($user->id, $language);
        }
        if (! $prevId) {
            throw new \DomainException('Нельзя найти следующий идентификатор слова. Начните сессию заново');
        }

        $sessionWords = $this->sessionCache->getSessionWords($user->id, $language);
        $currentIndex = $this->sessionCache->findWordIndex($prevId, $sessionWords);

        $this->sessionCache->updateNavigation($user->id, $prevId, $currentIndex, $sessionWords, $language);

        $word = $this->getWord->handle($prevId, $user->id);
        $this->incrementWordViews->handle($prevId, $user->id);

        return [
            'word' => $this->sessionCache->prepareWordData($word, $reverse),
            'meta' => $this->sessionCache->buildMeta($user->id, $currentIndex, $sessionWords, $language),
        ];
    }
}
