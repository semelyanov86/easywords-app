<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Spatie\LaravelData\Data;

/**
 * Data-объект для настроек пользователя.
 *
 * Предназначен для сериализации настроек пользователя в JSON:API формате.
 * Используется в API ответах для работы с настройками пользователя через пакет glorand/laravel-model-settings.
 */
final class UserSettingsData extends Data
{
    public function __construct(
        public int $paginate,
        public bool $fresh_first,
        public bool $show_starred,
        public bool $latest_first,
        public bool $known_enabled,
        public string $main_language,
        public bool $show_imported,
        /** @var array<int, string> */
        public array $languages_list,
        public bool $starred_enabled,
        public string $default_language,
        public bool $show_shared,
    ) {}

    /**
     * Преобразует данные в массив, соответствующий JSON:API спецификации.
     *
     * @return array<string, mixed>
     */
    public function toJsonArray(): array
    {
        return [
            'type' => 'user-settings',
            'id' => '1', // Настройки пользователя не имеют отдельного ID, поэтому используется фиксированное значение
            'attributes' => [
                'paginate' => $this->paginate,
                'fresh_first' => $this->fresh_first,
                'show_starred' => $this->show_starred,
                'latest_first' => $this->latest_first,
                'known_enabled' => $this->known_enabled,
                'main_language' => $this->main_language,
                'show_imported' => $this->show_imported,
                'languages_list' => $this->languages_list,
                'starred_enabled' => $this->starred_enabled,
                'default_language' => $this->default_language,
                'show_shared' => $this->show_shared,
            ],
        ];
    }

    /**
     * Преобразует данные в HTTP-ответ с правильным Content-Type для JSON:API.
     */
    #[\Override]
    public function toResponse($request = null): JsonResponse
    {
        return response()->json(['data' => $this->toJsonArray()], Response::HTTP_OK, ['Content-Type' => 'application/vnd.api+json']);
    }
}
