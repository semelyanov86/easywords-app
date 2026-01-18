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
    public function handle(User $user, string $language, bool $reverse = false): array
    {
        $nextId = $this->sessionCache->getNextId($user->id, $language);

        if ($nextId === null) {
            // Достигнут конец сессии - перемешиваем слова и начинаем сначала
            return $this->restartSession($user, $language, $reverse);
        }

        $sessionWords = $this->sessionCache->getSessionWords($user->id, $language);
        $currentIndex = $this->sessionCache->findWordIndex($nextId, $sessionWords);

        $this->sessionCache->updateNavigation($user->id, $nextId, $currentIndex, $sessionWords, $language);

        $word = $this->getWord->handle($nextId, $user->id);
        $this->incrementWordViews->handle($nextId, $user->id);

        return [
            'word' => $this->sessionCache->prepareWordData($word, $reverse),
            'meta' => $this->sessionCache->buildMeta($user->id, $currentIndex, $sessionWords, $language),
        ];
    }

    /**
     * Перезапускает сессию с перемешанным порядком слов.
     *
     * Когда достигнут конец сессии, перемешиваем все слова кроме текущего,
     * ставим текущее слово первым и возвращаем его с обновлённой навигацией.
     *
     * @return array{word: WordData, meta: array{total: int, next_id: ?int, prev_id: ?int, current_index: int}}
     *
     * @throws \RuntimeException
     */
    private function restartSession(User $user, string $language, bool $reverse): array
    {
        $currentId = $this->sessionCache->getCurrentId($user->id, $language);

        if ($currentId === null) {
            throw new \DomainException('Сессия закончена, начните новую');
        }

        $sessionWords = $this->sessionCache->getSessionWords($user->id, $language);

        // Перемешиваем слова с текущим словом на первой позиции
        $shuffledWords = $this->sessionCache->shuffleSessionWords($sessionWords, $currentId);

        // Сохраняем перемешанный массив в кэш
        cache()->put($this->sessionCache->key('start', $user->id, $language), $shuffledWords);

        // Обновляем навигацию: текущее слово - первый элемент (index 0)
        cache()->put($this->sessionCache->key('current', $user->id, $language), $shuffledWords[0]);
        cache()->put($this->sessionCache->key('prev', $user->id, $language), null);
        cache()->put($this->sessionCache->key('next', $user->id, $language), $shuffledWords[1] ?? null);

        $word = $this->getWord->handle($currentId, $user->id);
        $this->incrementWordViews->handle($currentId, $user->id);

        return [
            'word' => $this->sessionCache->prepareWordData($word, $reverse),
            'meta' => [
                'total' => count($shuffledWords),
                'next_id' => $shuffledWords[1] ?? null,
                'prev_id' => null,
                'current_index' => 1, // 1-based
            ],
        ];
    }
}
