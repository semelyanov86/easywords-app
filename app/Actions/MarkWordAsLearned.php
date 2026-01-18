<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\WordData;
use App\Models\Word;
use App\Support\StudySessionCache;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Пометка слова как выученного.
 *
 * Этот Action инкапсулирует логику установки поля done_at в текущее время.
 * Проверяет, что слово принадлежит указанному пользователю для обеспечения безопасности.
 * Выделен в отдельный класс для повторного использования и тестирования.
 */
final class MarkWordAsLearned
{
    use AsAction;

    /**
     * Помечает слово как выученное (устанавливает done_at).
     *
     * @param  int  $wordId  ID слова
     * @param  int  $userId  ID пользователя-владельца (для проверки прав)
     * @param  CarbonImmutable|null  $doneAt  Время, когда слово было выучено (по умолчанию текущее время)
     * @return WordData Обновлённое слово в формате Data
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Если слово не найдено или не принадлежит пользователю
     */
    public function handle(int $wordId, int $userId, ?CarbonImmutable $doneAt = null): WordData
    {
        $word = Word::query()
            ->where('id', $wordId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $language = $word->language;

        // Удаляем слово из кэша сессии изучения
        $cache = new StudySessionCache();

        try {
            $sessionWords = $cache->getSessionWords($userId, $language);
        } catch (\RuntimeException) {
            $sessionWords = [];
        }

        // Удаляем слово из массива
        $updatedWords = array_filter($sessionWords, fn (int $id) => $id !== $wordId);
        $updatedWords = array_values($updatedWords); // Переиндексируем массив

        // Сохраняем обновлённый массив обратно в кэш
        cache()->put("words.start.{$language}.{$userId}", $updatedWords);

        // Если это было текущее слово, обновляем навигацию
        if ($cache->getCurrentId($userId, $language) === $wordId) {
            $newCurrentId = $updatedWords[0] ?? null;
            cache()->put("words.current.{$language}.{$userId}", $newCurrentId);
        }

        $word->update([
            'done_at' => $doneAt ?? CarbonImmutable::now(),
        ]);

        return WordData::from($word);
    }
}
