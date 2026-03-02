<?php

declare(strict_types=1);

namespace App\Contracts;

interface WordExampleGenerator
{
    /**
     * @param  string  $word  Слово для генерации примеров
     * @param  string  $language  Язык слова (например: "en", "de", "es")
     * @return array{example_original: array<int, string>, example_translated: array<int, string>}
     */
    public function generateWordExamples(string $word, string $language): array;
}
