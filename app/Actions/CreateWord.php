<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\WordData;
use App\Models\Word;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Создание нового слова для пользователя.
 *
 * Этот Action инкапсулирует логику создания слова с привязкой к авторизованному пользователю.
 * Выделен в отдельный класс для повторного использования в контроллерах, тестах и потенциальных Jobs.
 * Гарантирует, что слово всегда создаётся с привязкой к конкретному пользователю.
 */
final class CreateWord
{
    use AsAction;

    /**
     * Создаёт новое слово.
     *
     * @param  int  $userId  ID пользователя-владельца
     * @param  string  $original  Оригинальное слово
     * @param  string  $translated  Перевод слова
     * @param  string  $language  Язык слова (например, 'en', 'de')
     * @return WordData Созданное слово в формате Data
     */
    public function handle(int $userId, string $original, string $translated, string $language): WordData
    {
        $word = Word::create([
            'user_id' => $userId,
            'original' => $original,
            'translated' => $translated,
            'language' => strtoupper($language),
            'views' => 0,
            'starred' => false,
            'from_sample' => false,
        ]);

        return WordData::from($word);
    }
}
