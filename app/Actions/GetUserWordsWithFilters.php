<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\WordData;
use App\Models\Word;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\LaravelData\PaginatedDataCollection;

/**
 * Получение списка слов пользователя с кастомными фильтрами.
 *
 * Этот Action инкапсулирует логику получения списка слов с фильтрацией по GET-параметрам:
 * - фильтрация по статусу изучения (done_at)
 * - фильтрация по общим словам (shared_by)
 * - фильтрация по источнику (from_sample)
 * - фильтрация по избранным (starred)
 * Игнорирует настройки пользователя из UserSettingsData.
 * Вынесен в отдельный класс для повторного использования в контроллерах и тестах.
 */
final readonly class GetUserWordsWithFilters
{
    use AsAction;

    /**
     * Возвращает пагинированный список слов пользователя с фильтрацией.
     *
     * @param  int  $userId  ID пользователя
     * @param  array<string, mixed>  $filters  Массив фильтров из GET-параметров
     * @return PaginatedDataCollection<int, WordData> Пагинированный список слов
     */
    public function handle(int $userId, array $filters = []): PaginatedDataCollection
    {
        $query = Word::where('user_id', $userId);

        // Фильтр по статусу изучения (done_at)
        if (isset($filters['done'])) {
            if ($filters['done'] === 'true') {
                $query->whereNotNull('done_at');
            } elseif ($filters['done'] === 'false') {
                $query->whereNull('done_at');
            }
        }

        // Фильтр по общим словам (shared_by)
        if (isset($filters['shared'])) {
            if ($filters['shared'] === 'true') {
                $query->whereNotNull('shared_by');
            } elseif ($filters['shared'] === 'false') {
                $query->whereNull('shared_by');
            }
        }

        // Фильтр по источнику (from_sample)
        if (isset($filters['from_sample'])) {
            if ($filters['from_sample'] === 'true') {
                $query->where('from_sample', true);
            } elseif ($filters['from_sample'] === 'false') {
                $query->where('from_sample', false);
            }
        }

        // Фильтр по избранным (starred)
        if (isset($filters['starred'])) {
            if ($filters['starred'] === 'true') {
                $query->where('starred', true);
            } elseif ($filters['starred'] === 'false') {
                $query->where('starred', false);
            }
        }

        return WordData::collect($query->paginate(), PaginatedDataCollection::class);
    }
}
