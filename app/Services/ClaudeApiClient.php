<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

/**
 * Переиспользуемый HTTP-клиент для Claude API Proxy.
 *
 * Отправляет запросы на Claude API Proxy (POST /api/claude/json)
 * с multipart/form-data и Bearer-авторизацией.
 */
class ClaudeApiClient
{
    private readonly PendingRequest $client;

    private readonly int $timeout;

    public function __construct()
    {
        /** @var string $apiKey */
        $apiKey = config('services.claude.key');
        /** @var string $apiUrl */
        $apiUrl = config('services.claude.url');

        $this->client = Http::withToken($apiKey)->baseUrl($apiUrl);

        // @phpstan-ignore-next-line
        $this->timeout = (int) config('services.claude.timeout', 120);
    }

    /**
     * Отправляет промпт на Claude API Proxy и возвращает декодированный JSON-результат.
     *
     * @param  string  $prompt  Промпт для отправки
     * @return array<string, mixed> Декодированный JSON из поля `result`
     *
     * @throws \Illuminate\Http\Client\RequestException если запрос к API не удался
     * @throws \RuntimeException если ответ имеет некорректный формат
     */
    public function sendJsonPrompt(string $prompt): array
    {
        $response = $this->client->timeout($this->timeout)->asMultipart()->post('/api/claude/json', [
            ['name' => 'prompt', 'contents' => $prompt],
        ]);

        $response->throw();

        /** @var array<string, mixed> $data */
        $data = $response->json();

        if (! isset($data['result'])) {
            throw new \RuntimeException('Invalid response format from Claude API: missing "result" field');
        }

        /** @var array<string, mixed> $result */
        $result = $data['result'];

        return $result;
    }

    /**
     * Отправляет промпт с файлом на Claude API Proxy и возвращает декодированный JSON-результат.
     *
     * @param  string  $prompt  Промпт для отправки
     * @param  UploadedFile  $file  Файл для отправки
     * @return array<string, mixed> Декодированный JSON из поля `result`
     *
     * @throws \Illuminate\Http\Client\RequestException если запрос к API не удался
     * @throws \RuntimeException если ответ имеет некорректный формат
     */
    public function sendJsonPromptWithFile(string $prompt, UploadedFile $file): array
    {
        $response = $this->client->timeout($this->timeout)->asMultipart()->post('/api/claude/json', [
            ['name' => 'prompt', 'contents' => $prompt],
            ['name' => 'file', 'contents' => fopen($file->getPathname(), 'r'), 'filename' => $file->getClientOriginalName()],
        ]);

        $response->throw();

        /** @var array<string, mixed> $data */
        $data = $response->json();

        if (! isset($data['result'])) {
            throw new \RuntimeException('Invalid response format from Claude API: missing "result" field');
        }

        /** @var array<string, mixed> $result */
        $result = $data['result'];

        return $result;
    }
}
