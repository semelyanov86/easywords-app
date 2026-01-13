<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Spatie\LaravelData\Data;

final class WordStatisticsData extends Data
{
    /**
     * @param  int  $total_words  Количество всех слов пользователя
     * @param  int  $starred_words  Количество избранных слов пользователя
     * @param  int  $not_done_words  Количество слов с done_at = null
     * @param  int  $done_words  Количество слов с не пустым done_at
     * @param  int  $total_views  Количество просмотров всего (просуммировать views по всем словам пользователя)
     * @param  int  $total_users  Количество пользователей в системе всего
     * @param  WordData[]  $top_viewed_words  Список самых просматриваемых слов у пользователя (топ 10)
     * @param  WordData[]  $words_added_today  Список добавленных сегодня слов
     * @param  int  $words_updated_today  Количество обновлённых сегодня слов (updated_at)
     * @param  int  $words_updated_this_month  Количество обновлённых слов в этом месяце
     * @param  int  $progress_today_percent  Процент прогресса сегодня от дневной цели (50 слов)
     * @param  int  $streak_days  Серия: количество дней подряд с обновлениями
     */
    public function __construct(
        public int $total_words,
        public int $starred_words,
        public int $not_done_words,
        public int $done_words,
        public int $total_views,
        public int $total_users,
        public array $top_viewed_words,
        public array $words_added_today,
        public int $words_updated_today,
        public int $words_updated_this_month,
        public int $progress_today_percent,
        public int $streak_days,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toJsonArray(): array
    {
        return [
            'type' => 'statistics',
            'attributes' => [
                'total_words' => $this->total_words,
                'starred_words' => $this->starred_words,
                'not_done_words' => $this->not_done_words,
                'done_words' => $this->done_words,
                'total_views' => $this->total_views,
                'total_users' => $this->total_users,
                'top_viewed_words' => $this->top_viewed_words,
                'words_added_today' => $this->words_added_today,
                'words_updated_today' => $this->words_updated_today,
                'words_updated_this_month' => $this->words_updated_this_month,
                'progress_today_percent' => $this->progress_today_percent,
                'streak_days' => $this->streak_days,
            ],
        ];
    }

    public function toResponse($request = null): JsonResponse
    {
        return response()->json(['data' => $this->toJsonArray()], Response::HTTP_OK, ['Content-Type' => 'application/vnd.api+json']);
    }
}
