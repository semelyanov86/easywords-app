<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\WordData;
use App\Models\Word;
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

        $word->update([
            'done_at' => $doneAt ?? CarbonImmutable::now(),
        ]);

        return WordData::from($word);
    }
}
