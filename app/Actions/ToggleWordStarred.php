<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\WordData;
use App\Models\Word;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Переключение статуса избранного у слова.
 *
 * Этот Action инкапсулирует логику изменения поля starred на противоположное значение.
 * Проверяет, что слово принадлежит указанному пользователю для обеспечения безопасности.
 * Выделен в отдельный класс для повторного использования и тестирования.
 */
final class ToggleWordStarred
{
    use AsAction;

    /**
     * Переключает статус starred у слова.
     *
     * @param  int  $wordId  ID слова
     * @param  int  $userId  ID пользователя-владельца (для проверки прав)
     * @param  bool  $starred  Новое значение starred (true или false)
     * @return WordData Обновлённое слово в формате Data
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Если слово не найдено или не принадлежит пользователю
     */
    public function handle(int $wordId, int $userId, bool $starred): WordData
    {
        $word = Word::query()
            ->where('id', $wordId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $word->update(['starred' => $starred]);

        return WordData::from($word);
    }
}
