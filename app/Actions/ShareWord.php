<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use App\Models\Word;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Шаринг слова с другим пользователем.
 *
 * Этот Action создает копию слова для указанного пользователя на основе оригинального слова.
 * Копия сохраняет оригинальное слово, перевод и язык, но получает новый ID пользователя.
 * Вынесен в отдельный класс для повторного использования в контроллерах и тестах.
 */
final readonly class ShareWord
{
    use AsAction;

    /**
     * Создает копию слова для указанного пользователя.
     *
     * @param  Word  $word  Оригинальное слово для шаринга
     * @param  User  $targetUser  Пользователь, которому делим слово
     * @param  User  $author  Пользователь, который делится словом
     * @return Word Созданное слово для целевого пользователя
     */
    public function handle(Word $word, User $targetUser, User $author): Word
    {
        return Word::query()->create([
            'original' => $word->original,
            'translated' => $word->translated,
            'user_id' => $targetUser->id,
            'starred' => false,
            'language' => $word->language,
            'views' => 0,
            'from_sample' => false,
            'shared_by' => $author->id,
            'example_original' => $word->example_original,
            'example_translated' => $word->example_translated,
        ]);
    }
}
