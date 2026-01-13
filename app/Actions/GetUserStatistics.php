<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\WordData;
use App\Data\WordStatisticsData;
use App\Models\User;
use App\Models\Word;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Получение статистики пользователя.
 *
 * Этот Action собирает и возвращает статистику по словам пользователя:
 * - общее количество слов
 * - количество избранных слов
 * - количество изученных и неизученных слов
 * - общее количество просмотров
 * - топ-10 просматриваемых слов
 * - слова добавленные сегодня
 * - количество обновлённых слов сегодня и в этом месяце
 * - процент прогресса от дневной цели
 * - серия дней подряд с обновлениями
 * Вынесен в отдельный класс для повторного использования в контроллерах и тестах.
 */
final readonly class GetUserStatistics
{
    use AsAction;

    private const int DAILY_GOAL = 50;

    /**
     * Возвращает статистику пользователя.
     *
     * @param  User  $user  Пользователь
     * @return WordStatisticsData Статистика пользователя
     */
    public function handle(User $user): WordStatisticsData
    {
        $today = today();
        $thisMonth = now()->startOfMonth();

        $totalWords = Word::where('user_id', $user->id)->count();
        $starredWords = Word::where('user_id', $user->id)->where('starred', true)->count();
        $notDoneWords = Word::where('user_id', $user->id)->whereNull('done_at')->count();
        $doneWords = Word::where('user_id', $user->id)->whereNotNull('done_at')->count();
        $totalViews = (int) Word::where('user_id', $user->id)->sum('views');
        $totalUsers = User::count();

        $topViewedWords = Word::where('user_id', $user->id)
            ->orderBy('views', 'desc')
            ->limit(10)
            ->get();

        $wordsAddedToday = Word::where('user_id', $user->id)
            ->where('created_at', '>=', $today)
            ->get();

        $wordsUpdatedToday = Word::where('user_id', $user->id)
            ->where('updated_at', '>=', $today)
            ->count();

        $wordsUpdatedThisMonth = Word::where('user_id', $user->id)
            ->where('updated_at', '>=', $thisMonth)
            ->count();

        $progressTodayPercent = (int) min(100, round(($wordsUpdatedToday / self::DAILY_GOAL) * 100));

        $streakDays = $this->calculateStreakDays($user->id);

        return new WordStatisticsData(
            total_words: $totalWords,
            starred_words: $starredWords,
            not_done_words: $notDoneWords,
            done_words: $doneWords,
            total_views: $totalViews,
            total_users: $totalUsers,
            top_viewed_words: WordData::collect($topViewedWords, 'array'),
            words_added_today: WordData::collect($wordsAddedToday, 'array'),
            words_updated_today: $wordsUpdatedToday,
            words_updated_this_month: $wordsUpdatedThisMonth,
            progress_today_percent: $progressTodayPercent,
            streak_days: $streakDays,
        );
    }

    /**
     * Рассчитывает серию дней подряд с обновлениями слов.
     */
    protected function calculateStreakDays(int $userId): int
    {
        /** @var string[] $distinctDates */
        $distinctDates = Word::where('user_id', $userId)
            ->select(DB::raw('DATE(updated_at) as date'))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->pluck('date')
            ->toArray();

        if (empty($distinctDates)) {
            return 0;
        }

        $streak = 0;
        $currentDate = today();

        foreach ($distinctDates as $dateString) {
            $date = \Illuminate\Support\Facades\Date::parse($dateString);

            if ($date->isSameDay($currentDate)) {
                $streak++;
                $currentDate = $currentDate->subDay();
            } else {
                break;
            }
        }

        return $streak;
    }
}
