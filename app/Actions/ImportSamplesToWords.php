<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\WordData;
use App\Models\Sample;
use App\Models\User;
use App\Models\Word;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Импортирует samples из глобальной коллекции в личный словарь пользователя.
 *
 * Этот Action нужен для массового добавления готовых примеров слов в словарь пользователя.
 * Он проверяет, существует ли уже слово у пользователя, чтобы избежать дубликатов.
 * Каждое созданное слово помечается как from_sample = true.
 *
 * Контракт для вызывающего кода:
 * - Возвращает коллекцию созданных Word моделей
 * - Пропускает слова, которые уже есть у пользователя
 * - Ассоциирует все созданные слова с переданным пользователем
 */
final readonly class ImportSamplesToWords
{
    use AsAction;

    /**
     * @return \Illuminate\Support\Collection<int, WordData>
     */
    public function handle(User $user, string $language): \Illuminate\Support\Collection
    {
        $exists = Sample::where('language', $language)->exists();
        abort_if(! $exists, Response::HTTP_NOT_FOUND, 'Language Not Found');
        $samples = Sample::where('language', $language)->get();
        /** @var array<int, \App\Models\Word> $wordsArray */
        $wordsArray = [];

        foreach ($samples as $sample) {
            $existingWord = Word::where('user_id', $user->id)
                ->where('original', $sample->original)
                ->where('language', $language)
                ->first();

            if ($existingWord !== null) {
                continue;
            }

            $word = Word::create([
                'original' => $sample->original,
                'translated' => $sample->translated,
                'language' => $sample->language,
                'user_id' => $user->id,
                'from_sample' => true,
                'starred' => false,
                'views' => 0,
            ]);

            $wordsArray[] = $word;
        }

        return WordData::collect($wordsArray, Collection::class);
    }
}
