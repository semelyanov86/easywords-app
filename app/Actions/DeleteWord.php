<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Word;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Удаление слова с проверкой прав доступа.
 *
 * Этот Action инкапсулирует логику удаления слова с проверкой,
 * что пользователь может удалять только свои слова.
 * Вынесен в отдельный класс для повторного использования в контроллерах и тестах.
 */
final readonly class DeleteWord
{
    use AsAction;

    /**
     * Удаляет слово по ID с проверкой прав доступа.
     *
     * @param  int  $wordId  ID слова
     * @param  int  $userId  ID пользователя для проверки прав
     * @throws ModelNotFoundException если слово не найдено или не принадлежит пользователю
     */
    public function handle(int $wordId, int $userId): void
    {
        /** @var Word $word */
        $word = Word::query()
            ->where('id', $wordId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $word->delete();
    }
}
