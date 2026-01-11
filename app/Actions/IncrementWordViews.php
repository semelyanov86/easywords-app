<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\WordData;
use App\Models\Word;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Увеличение счётчика просмотров слова.
 *
 * Этот Action инкапсулирует логику увеличения поля views на 1.
 * Проверяет, что слово принадлежит указанному пользователю для обеспечения безопасности.
 * Выделен в отдельный класс для повторного использования и тестирования.
 */
final class IncrementWordViews
{
    use AsAction;

    /**
     * Увеличивает счётчик просмотров слова на 1.
     *
     * @param  int  $wordId  ID слова
     * @param  int  $userId  ID пользователя-владельца (для проверки прав)
     * @return WordData Обновлённое слово в формате Data
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Если слово не найдено или не принадлежит пользователю
     */
    public function handle(int $wordId, int $userId): WordData
    {
        $word = Word::query()
            ->where('id', $wordId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $word->increment('views');

        $word->refresh();

        return WordData::from($word);
    }
}
