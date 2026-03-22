<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Word;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
     * @return bool Новое значение starred (true или false)
     *
     * @throws ModelNotFoundException Если слово не найдено или не принадлежит пользователю
     */
    public function handle(int $wordId, int $userId): bool
    {
        $word = Word::query()
            ->where('id', $wordId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $newStatus = ! $word->starred;
        $word->update(['starred' => $newStatus]);

        return $newStatus;
    }
}
