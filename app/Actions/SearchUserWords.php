<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\WordData;
use App\Models\Word;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Поиск слов пользователя по строке запроса.
 *
 * Этот Action инкапсулирует логику поиска слов пользователя по полям original и translated.
 * Поиск выполняется с использованием оператора LIKE с обёрткой в проценты (%query%),
 * что позволяет находить частичные совпадения в любом месте строки.
 * Вынесен в отдельный класс для повторного использования в контроллерах и тестах.
 */
final readonly class SearchUserWords
{
    use AsAction;

    /**
     * Выполняет поиск слов пользователя.
     *
     * Ищет слова по полям original и translated с учётом принадлежности пользователю.
     * Поиск нечувствителен к регистру и находит частичные совпадения.
     * Если query пустой или null, возвращает пустой массив.
     *
     * @param  int  $userId  ID пользователя
     * @param  string|null  $query  Строка для поиска (будет обёрнута в %%)
     * @return array<int, WordData> Массив найденных слов
     */
    public function handle(int $userId, ?string $query): array
    {
        if ($query === null || $query === '') {
            return [];
        }

        $words = Word::where('user_id', $userId)
            ->where(function ($q) use ($query): void {
                $q->where('original', 'like', "%{$query}%")
                    ->orWhere('translated', 'like', "%{$query}%");
            })
            ->orderBy('original')
            ->get();

        return $words->map(fn (Word $word) => WordData::from($word))->all();
    }
}
