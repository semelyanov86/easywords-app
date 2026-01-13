<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\WordTranslationData;
use App\Models\Word;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Получение перевода слова.
 *
 * Этот Action инкапсулирует логику автоматического перевода слова:
 * 1. Сначала ищет слово во внутренней базе данных (среди сохраненных слов)
 * 2. Если не найдено, запрашивает перевод через OpenAI API
 * 3. Возвращает краткий перевод (до 100 символов)
 *
 * Вынесен в отдельный класс для повторного использования и тестируемости.
 */
final readonly class GetWordTranslation
{
    use AsAction;

    /**
     * Возвращает перевод слова.
     *
     * Сначала ищет слово в базе данных. Если не найдено, запрашивает перевод через OpenAI.
     *
     * @param  string  $word  Слово для перевода
     * @param  string  $language  Язык слова (например: "en", "de", "es")
     * @return WordTranslationData Data-объект с переводом
     * @throws \Illuminate\Http\Client\RequestException если запрос к API не удался
     * @throws \JsonException если не удалось декодировать JSON ответ
     */
    public function handle(string $word, string $language): WordTranslationData
    {
        // Сначала ищем слово в базе данных
        $existingWord = Word::query()
            ->where('original', $word)
            ->where('language', $language)
            ->first();

        if ($existingWord !== null) {
            return new WordTranslationData($existingWord->translated);
        }

        // Если не найдено, запрашиваем перевод через OpenAI
        $translation = $this->getTranslationFromOpenAI($word, $language);

        return new WordTranslationData($translation);
    }

    /**
     * Запрашивает перевод слова через OpenAI API.
     *
     * @param  string  $word  Слово для перевода
     * @param  string  $language  Язык слова
     * @return string Перевод (краткий, до 100 символов)
     * @throws \Illuminate\Http\Client\RequestException если запрос к API не удался
     * @throws \JsonException если не удалось декодировать JSON ответ
     */
    private function getTranslationFromOpenAI(string $word, string $language): string
    {
        /** @var string $apiKey */
        $apiKey = config('services.openai.key');
        /** @var string $apiUrl */
        $apiUrl = config('services.openai.url');

        $client = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->baseUrl($apiUrl);

        /** @var string $model */
        $model = config('services.openai.model', 'glm-4.7');
        /** @var float|int|string $temperatureValue */
        $temperatureValue = config('services.openai.temperature', 0.3);
        $temperature = (float) $temperatureValue;

        // @phpstan-ignore-next-line
        $timeout = (int) config('services.openai.timeout', 120);

        $systemPrompt = $this->buildSystemPrompt($language);
        $userPrompt = $this->buildUserPrompt($word, $language);

        $response = $client->timeout($timeout)->post('/chat/completions', [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => $temperature,
        ]);

        $response->throw();

        /** @var array{choices: array<int, array{message: array{content: string}}>} $data */
        $data = $response->json();

        if (! isset($data['choices'][0]['message']['content'])) {
            throw new \RuntimeException('Invalid response format from OpenAI API');
        }

        $translation = trim($data['choices'][0]['message']['content']);

        // Убеждаемся, что перевод не длиннее 100 символов
        if (mb_strlen($translation) > 100) {
            $translation = mb_substr($translation, 0, 97) . '...';
        }

        return $translation;
    }

    /**
     * Формирует системный промпт для перевода.
     *
     * @param  string  $language  Язык слова
     * @return string Системный промпт
     */
    private function buildSystemPrompt(string $language): string
    {
        return <<<PROMPT
            Ты — помощник для изучения языков. Твоя задача — переводить слова с {$language} языка на русский.

            Правила:
            1. Перевод должен быть кратким и точным
            2. Максимальная длина перевода — 100 символов
            3. Выводи ТОЛЬКО перевод, без каких-либо дополнительных слов или объяснений
            4. Если слово имеет несколько значений, выбери основное, самое распространенное
            PROMPT;
    }

    /**
     * Формирует пользовательский промпт с конкретным словом.
     *
     * @param  string  $word  Слово для перевода
     * @param  string  $language  Язык слова
     * @return string Пользовательский промпт
     */
    private function buildUserPrompt(string $word, string $language): string
    {
        return "Переведи слово \"{$word}\" с {$language} языка на русский. Выведи только перевод, без дополнительных слов.";
    }
}
