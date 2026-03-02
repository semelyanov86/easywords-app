<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\WordTranslator;
use App\Data\WordTranslationData;
use App\Models\Word;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Получение перевода слова.
 *
 * Этот Action инкапсулирует логику автоматического перевода слова:
 * 1. Сначала ищет слово во внутренней базе данных (среди сохраненных слов)
 * 2. Если не найдено, запрашивает перевод через WordTranslator
 * 3. Очищает перевод от ссылок-цитат вида [1][2][3]
 * 4. Возвращает краткий перевод (до 100 символов)
 *
 * Вынесен в отдельный класс для повторного использования и тестируемости.
 */
final readonly class GetWordTranslation
{
    use AsAction;

    public function __construct(private WordTranslator $translator) {}

    /**
     * Возвращает перевод слова.
     *
     * Сначала ищет слово в базе данных. Если не найдено, запрашивает перевод через WordTranslator.
     *
     * @param  string  $word  Слово для перевода
     * @param  string  $language  Язык слова (например: "en", "de", "es")
     * @return WordTranslationData Data-объект с переводом
     */
    public function handle(string $word, string $language): WordTranslationData
    {
        $existingWord = Word::query()
            ->where('original', $word)
            ->where('language', $language)
            ->first();

        if ($existingWord !== null) {
            return new WordTranslationData($existingWord->translated);
        }

        $translation = $this->translator->translate($word, $language);
        $translation = $this->removeCitationMarks($translation);

        if (mb_strlen($translation) > 100) {
            $translation = mb_substr($translation, 0, 97) . '...';
        }

        return new WordTranslationData($translation);
    }

    /**
     * Удаляет ссылки-цитаты из текста.
     *
     * Некоторые AI (например, Perplexity) добавляют ссылки на источники в формате [1][2][3].
     * Этот метод удаляет все такие вхождения из текста.
     *
     * @param  string  $text  Текст для очистки
     * @return string Очищенный текст
     */
    private function removeCitationMarks(string $text): string
    {
        return (string) preg_replace('/\[\d+\]/', '', $text);
    }
}
