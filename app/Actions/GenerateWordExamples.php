<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\WordExampleGenerator;
use App\Data\ExampleData;
use App\Models\Word;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Генерация примеров использования слова через AI.
 *
 * Этот Action проверяет, есть ли у слова примеры. Если примеры отсутствуют,
 * генерирует их через AI API и сохраняет в базу данных.
 * Примеры включают: классику, афоризм и разговорный пример.
 */
class GenerateWordExamples
{
    use AsAction;

    public function __construct(private readonly WordExampleGenerator $wordExampleGenerator) {}

    /**
     * Генерирует или возвращает существующие примеры для слова.
     *
     * @param  int  $wordId  ID слова
     * @param  int  $userId  ID пользователя для проверки прав
     * @return ExampleData Data-объект с примерами
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException если слово не найдено или не принадлежит пользователю
     * @throws \RuntimeException если генерация примеров не удалась
     */
    public function handle(int $wordId, int $userId): ExampleData
    {
        /** @var Word $word */
        $word = Word::query()
            ->where('id', $wordId)
            ->where('user_id', $userId)
            ->firstOrFail();

        // Если примеры уже есть, возвращаем их
        if ($this->hasExamples($word)) {
            return $this->buildExampleData($word);
        }

        // Генерируем примеры через AI
        $examples = $this->wordExampleGenerator->generateWordExamples(
            word: $word->original,
            language: $word->language
        );

        // Валидация структуры ответа
        $this->validateExamples($examples);

        // Сохраняем примеры в базу данных
        $word->update([
            'example_original' => $examples['example_original'],
            'example_translated' => $examples['example_translated'],
        ]);

        return $this->buildExampleData($word);
    }

    /**
     * Проверяет, есть ли у слова примеры.
     *
     * @param  Word  $word  Слово для проверки
     * @return bool true, если есть хотя бы один пример
     */
    private function hasExamples(Word $word): bool
    {
        $original = $word->example_original;
        $translated = $word->example_translated;

        return is_array($original) && count($original) > 0
            && is_array($translated) && count($translated) > 0;
    }

    /**
     * Создаёт ExampleData из модели Word.
     *
     * @param  Word  $word  Слово с примерами
     * @return ExampleData Data-объект с примерами
     */
    private function buildExampleData(Word $word): ExampleData
    {
        $original = $word->example_original;
        $translated = $word->example_translated;

        // Приводим к нужному типу для PHPStan
        /** @var array<int, string> $originalArray */
        $originalArray = is_array($original) ? array_values($original) : [];
        /** @var array<int, string> $translatedArray */
        $translatedArray = is_array($translated) ? array_values($translated) : [];

        return new ExampleData(
            id: (string) $word->id,
            example_original: $originalArray,
            example_translated: $translatedArray
        );
    }

    /**
     * Валидирует структуру массива примеров.
     *
     * @param  array<mixed>  $examples  Массив примеров для валидации
     * @throws \RuntimeException если структура неверна
     */
    private function validateExamples(array $examples): void
    {
        if (! isset($examples['example_original'], $examples['example_translated'])) {
            throw new \RuntimeException('Invalid examples structure: missing required fields');
        }

        if (! is_array($examples['example_original']) || ! is_array($examples['example_translated'])) {
            throw new \RuntimeException('Invalid examples structure: fields must be arrays');
        }

        if (count($examples['example_original']) !== 3 || count($examples['example_translated']) !== 3) {
            throw new \RuntimeException('Invalid examples structure: each field must contain exactly 3 examples');
        }

        foreach ($examples['example_original'] as $index => $example) {
            if (! is_string($example) || trim($example) === '') {
                throw new \RuntimeException("Invalid example_original[{$index}]: must be non-empty string");
            }
        }

        foreach ($examples['example_translated'] as $index => $example) {
            if (! is_string($example) || trim($example) === '') {
                throw new \RuntimeException("Invalid example_translated[{$index}]: must be non-empty string");
            }
        }
    }
}
