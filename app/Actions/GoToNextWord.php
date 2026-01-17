<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use App\Models\Word;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Переход к следующему слову в обучающей сессии.
 *
 * Этот Action управляет навигацией по словам в сессии изучения:
 * - Берёт следующее слово из кэша
 * - Обновляет текущее слово и устанавливает новое следующее
 * - Инкрементирует счётчик просмотров
 * - Возвращает слово с метаданными сессии с учётом параметра reverse
 *
 * Логика кэша:
 * - words.next.{user_id} → words.current.{user_id}
 * - words.current.{user_id} → words.prev.{user_id}
 * - Находит следующее слово в массиве words.start.{user_id}
 * - Сохраняет новый идентификатор в words.next.{user_id}
 */
final readonly class GoToNextWord
{
    use AsAction;

    public function __construct(
        private IncrementWordViews $incrementWordViews,
    ) {}

    /**
     * @param  bool  $reverse  Менять местами original и translated
     * @return array{word: Word, meta: array{total: int, next_id: ?int, prev_id: ?int, current_index: int}}
     *
     * @throws \RuntimeException
     */
    public function handle(User $user, bool $reverse = false): array
    {
        /** @var ?int $nextId */
        $nextId = cache()->get("words.next.{$user->id}");

        if ($nextId === null) {
            throw new \RuntimeException('No next word available');
        }

        /** @var int[]|null $sessionWords */
        $sessionWords = cache()->get("words.start.{$user->id}");

        if (! is_array($sessionWords)) {
            throw new \RuntimeException('Study session not found');
        }

        /** @var int|null $currentId */
        $currentId = cache()->get("words.current.{$user->id}");

        if ($currentId !== null) {
            cache()->put("words.prev.{$user->id}", $currentId);
        } else {
            cache()->put("words.prev.{$user->id}", null);
        }

        $currentIndex = array_search($nextId, $sessionWords, true);

        if ($currentIndex === false) {
            throw new \RuntimeException('Next word not found in session');
        }

        assert(is_int($currentIndex));

        $nextIndex = $currentIndex + 1;

        if (isset($sessionWords[$nextIndex])) {
            cache()->put("words.next.{$user->id}", $sessionWords[$nextIndex]);
        } else {
            cache()->put("words.next.{$user->id}", null);
        }

        cache()->put("words.current.{$user->id}", $nextId);

        /** @var Word $word */
        $word = Word::where('id', $nextId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $this->incrementWordViews->handle($nextId, $user->id);

        if ($reverse) {
            $tempOriginal = $word->original;
            $word->original = $word->translated;
            $word->translated = $tempOriginal;
        }

        /** @var ?int $finalNextId */
        $finalNextId = cache()->get("words.next.{$user->id}");
        /** @var ?int $finalPrevId */
        $finalPrevId = cache()->get("words.prev.{$user->id}");

        return [
            'word' => $word,
            'meta' => [
                'total' => count($sessionWords),
                'next_id' => $finalNextId,
                'prev_id' => $finalPrevId,
                'current_index' => $currentIndex + 1, // 1-based index
            ],
        ];
    }
}
