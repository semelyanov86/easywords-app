<?php

declare(strict_types=1);

namespace App\Contracts;

interface WordTranslator
{
    /**
     * @param  string  $word  Слово для перевода
     * @param  string  $language  Язык слова (например: "en", "de", "es")
     * @return string Перевод слова
     */
    public function translate(string $word, string $language): string;
}
