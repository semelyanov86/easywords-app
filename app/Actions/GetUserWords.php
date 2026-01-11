<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\WordData;
use App\Models\Word;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\LaravelData\PaginatedDataCollection;

/**
 * Получение списка слов пользователя с фильтрацией по настройкам.
 *
 * Этот Action инкапсулирует логику получения списка слов с учётом настроек пользователя:
 * - фильтрация по языку и статусу (изученное/неизученное)
 * - режим сортировки (fresh_first или по просмотрам)
 * - показ/скрытие импортированных и общих слов
 * Вынесен в отдельный класс для повторного использования в контроллерах и тестах.
 */
final readonly class GetUserWords
{
    use AsAction;

    public function __construct(
        protected GetUserSettings $settingsAction
    ) {}

    /**
     * Возвращает пагинированный список слов пользователя.
     *
     * @param  int  $userId  ID пользователя
     * @param  string  $language  Язык для фильтрации слов
     * @return PaginatedDataCollection<int, WordData> Пагинированный список слов
     */
    public function handle(int $userId, string $language): PaginatedDataCollection
    {
        $settings = $this->settingsAction->handle($userId);
        $query = Word::where('user_id', $userId)
            ->where('language', strtoupper($language));

        if ($settings->starred_enabled) {
            $query->where('starred', true);
        }

        if (! $settings->known_enabled) {
            $query->whereNull('done_at');
        }

        if ($settings->fresh_first) {
            if (! $settings->show_shared) {
                $query->whereNull('shared_by');
            }

            if (! $settings->show_imported) {
                $query->where('from_sample', false);
            }

            $query->orderBy('created_at', $settings->latest_first ? 'desc' : 'asc');
        } else {
            $query->orderBy('views');
        }

        return WordData::collect($query->paginate(perPage: $settings->paginate), PaginatedDataCollection::class);
    }
}
