<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\WordData;
use App\Data\WordStatisticsData;
use App\Models\User;
use App\Models\Word;
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
 * Вынесен в отдельный класс для повторного использования в контроллерах и тестах.
 */
final readonly class GetUserStatistics
{
    use AsAction;

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

        // Общее количество слов пользователя
        $totalWords = Word::where('user_id', $user->id)->count();

        // Количество избранных слов пользователя
        $starredWords = Word::where('user_id', $user->id)->where('starred', true)->count();

        // Количество слов с done_at = null
        $notDoneWords = Word::where('user_id', $user->id)->whereNull('done_at')->count();

        // Количество слов с не пустым done_at
        $doneWords = Word::where('user_id', $user->id)->whereNotNull('done_at')->count();

        // Общее количество просмотров (сумма views по всем словам пользователя)
        $totalViews = (int) Word::where('user_id', $user->id)->sum('views');

        // Количество пользователей в системе всего
        $totalUsers = User::count();

        // Топ-10 самых просматриваемых слов у пользователя
        $topViewedWords = Word::where('user_id', $user->id)
            ->orderBy('views', 'desc')
            ->limit(10)
            ->get();

        // Список добавленных сегодня слов
        $wordsAddedToday = Word::where('user_id', $user->id)
            ->where('created_at', '>=', $today)
            ->get();

        // Количество обновлённых сегодня слов (updated_at)
        $wordsUpdatedToday = Word::where('user_id', $user->id)
            ->where('updated_at', '>=', $today)
            ->count();

        // Количество обновлённых слов в этом месяце
        $wordsUpdatedThisMonth = Word::where('user_id', $user->id)
            ->where('updated_at', '>=', $thisMonth)
            ->count();

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
        );
    }
}
