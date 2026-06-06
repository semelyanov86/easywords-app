<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

/**
 * Переиспользуемый HTTP-клиент для polza.ai (OpenAI-совместимый API).
 *
 * Отправляет запросы на POST {url}/chat/completions с Bearer-авторизацией.
 * Модель и температура задаются вызывающим кодом, что позволяет использовать
 * разные модели для разных функций (перевод, примеры, распознавание картинок).
 * Температура отправляется только если она задана — некоторые модели
 * (например, семейство gpt-5) принимают лишь значение по умолчанию.
 */
class PolzaApiClient
{
    private readonly PendingRequest $client;

    private readonly int $timeout;

    public function __construct()
    {
        /** @var string $apiKey */
        $apiKey = config('services.polza.key');
        /** @var string $apiUrl */
        $apiUrl = config('services.polza.url');

        $this->client = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->baseUrl($apiUrl);

        // @phpstan-ignore-next-line
        $this->timeout = (int) config('services.polza.timeout', 120);
    }

    /**
     * Отправляет chat-completions запрос и возвращает содержимое ответа модели.
     *
     * @param  string  $model  Идентификатор модели (например: "google/gemini-2.5-flash-lite")
     * @param  array<int, array<string, mixed>>  $messages  Сообщения в формате OpenAI Chat API
     * @param  array<string, mixed>|null  $responseFormat  Значение поля response_format (например, json_schema)
     * @param  float|int|string|null  $temperature  Температура; не отправляется, если null или пустая строка
     * @return string Текстовое содержимое ответа (choices[0].message.content)
     *
     * @throws RequestException если запрос к API не удался
     * @throws \RuntimeException если ответ имеет некорректный формат
     */
    public function chat(
        string $model,
        array $messages,
        ?array $responseFormat = null,
        float|int|string|null $temperature = null
    ): string {
        $payload = [
            'model' => $model,
            'messages' => $messages,
        ];

        if ($temperature !== null && $temperature !== '') {
            $payload['temperature'] = (float) $temperature;
        }

        if ($responseFormat !== null) {
            $payload['response_format'] = $responseFormat;
        }

        $response = $this->client->timeout($this->timeout)->post('/chat/completions', $payload);

        $response->throw();

        /** @var array{choices?: array<int, array{message?: array{content?: string}}>} $data */
        $data = $response->json();

        if (! isset($data['choices'][0]['message']['content'])) {
            throw new \RuntimeException('Invalid response format from Polza API');
        }

        return $data['choices'][0]['message']['content'];
    }
}
