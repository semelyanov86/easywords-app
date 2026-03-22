<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\WordData;
use App\Models\Word;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Получение слова по ID с проверкой прав доступа.
 *
 * Этот Action инкапсулирует логику получения слова с проверкой,
 * что пользователь может просматривать только свои слова.
 * Вынесен в отдельный класс для повторного использования в контроллерах и тестах.
 */
final readonly class GetWord
{
    use AsAction;

    /**
     * Возвращает слово по ID с проверкой прав доступа.
     *
     * @param  int  $wordId  ID слова
     * @param  int  $userId  ID пользователя для проверки прав
     * @return WordData Data-объект слова
     * @throws ModelNotFoundException если слово не найдено или не принадлежит пользователю
     */
    public function handle(int $wordId, int $userId): WordData
    {
        /** @var Word $word */
        $word = Word::query()
            ->where('id', $wordId)
            ->where('user_id', $userId)
            ->firstOrFail();

        return WordData::from($word);
    }
}
