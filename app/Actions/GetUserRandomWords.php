<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\WordData;
use App\Models\Word;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Получение случайных слов для авторизованного пользователя.
 *
 * Этот Action инкапсулирует логику получения случайных слов
 * с учётом настроек пользователя: фильтрация по языку,
 * исключение изученных слов (если known_enabled === false)
 * и ограничение количества слов.
 * Выделен в отдельный класс для повторного использования и тестирования.
 */
final class GetUserRandomWords
{
    use AsAction;

    public function __construct(
        protected GetUserSettings $settingsAction,
    ) {}

    /**
     * Возвращает коллекцию случайных слов пользователя.
     *
     * @param  int  $userId  ID пользователя
     * @param  int  $limit  Количество слов для получения
     * @return Collection<int, WordData> Коллекция случайных слов
     */
    public function handle(int $userId, int $limit = 20, ?string $language = null): Collection
    {
        $settings = $this->settingsAction->handle($userId);
        if (! $language) {
            $language = $settings->default_language;
        }
        $query = Word::query()
            ->where('user_id', $userId)
            ->where('language', $language);

        if (! $settings->known_enabled) {
            $query->whereNull('done_at');
        }

        $words = $query->get();

        if ($words->count() <= $limit) {
            return WordData::collect($words->shuffle(), Collection::class);
        }

        return WordData::collect($words->random($limit), Collection::class);
    }
}
