import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { statisticsTranslations } from '@/shared/i18n/statistics';
import { useStatisticsTranslation } from '@/shared/i18n/useStatisticsTranslation';
import { User } from '@/types';
import { AuthHeader } from '@/widgets/auth/AuthHeader';
import {
    ArrowRight,
    BookOpen,
    Eye,
    LucideIcon,
    TrendingUp,
    Users,
    Zap,
} from 'lucide-react';

interface WordData {
    id: number;
    original: string;
    translated: string;
    views: number;
}

interface WordStatisticsData {
    total_words: number;
    starred_words: number;
    not_done_words: number;
    done_words: number;
    total_views: number;
    total_users: number;
    top_viewed_words: WordData[];
    words_added_today: WordData[];
    words_updated_today: number;
    words_updated_this_month: number;
    progress_today_percent: number;
    streak_days: number;
}

interface StatisticsPageProps {
    statistics: WordStatisticsData;
    user: User;
}

interface StatCardProps {
    icon: LucideIcon;
    iconBgColor: string;
    title: string;
    value: number | string;
    description: string;
}

function StatCard({
    icon: Icon,
    iconBgColor,
    title,
    value,
    description,
}: StatCardProps) {
    return (
        <Card className="p-6 shadow-sm">
            <div className="flex items-start justify-between">
                <div className="flex items-center gap-4">
                    <div
                        className={`flex h-12 w-12 items-center justify-center rounded-full bg-${iconBgColor}`}
                    >
                        <Icon className="h-6 w-6 text-white" />
                    </div>
                    <div>
                        <p className="text-sm font-medium text-neutral-600">
                            {title}
                        </p>
                        <p className="mt-1 text-3xl font-bold text-neutral-900">
                            {value}
                        </p>
                        <p className="text-sm text-neutral-500">
                            {description}
                        </p>
                    </div>
                </div>
            </div>
        </Card>
    );
}

export default function Statistics({ user, statistics }: StatisticsPageProps) {
    const t = useStatisticsTranslation(statisticsTranslations);

    return (
        <div className="min-h-screen bg-gradient-to-br from-neutral-50 via-blue-50/30 to-green-50/20">
            <AuthHeader userName={user.name} />

            <main className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
                <div className="mb-8">
                    <h1 className="text-3xl font-bold text-neutral-900 sm:text-4xl">
                        {t.title || 'Personal Statistics'}
                    </h1>
                    <p className="mt-2 text-base text-neutral-600 sm:text-lg">
                        {t.subtitle ||
                            'Track your learning progress and achievements'}
                    </p>
                </div>

                <div className="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <StatCard
                        icon={Users}
                        iconBgColor="primary"
                        title={t.totalUsers || 'Total Users'}
                        value={statistics.total_users}
                        description={t.usersInSystem || 'in system'}
                    />

                    <StatCard
                        icon={BookOpen}
                        iconBgColor="secondary"
                        title={t.totalWords || 'Total Words'}
                        value={statistics.total_words}
                        description={t.allWords || 'all words'}
                    />

                    <StatCard
                        icon={BookOpen}
                        iconBgColor="accent"
                        title={t.notDoneWords || 'Not Learned'}
                        value={statistics.not_done_words}
                        description={t.wordsToStudy || 'words to study'}
                    />

                    <StatCard
                        icon={BookOpen}
                        iconBgColor="primary"
                        title={t.doneWords || 'Learned Words'}
                        value={statistics.done_words}
                        description={t.wordsMastered || 'words mastered'}
                    />
                </div>

                <div className="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <StatCard
                        icon={Eye}
                        iconBgColor="secondary"
                        title={t.totalViews || 'Total Views'}
                        value={statistics.total_views}
                        description={t.allViews || 'total views'}
                    />

                    <StatCard
                        icon={Eye}
                        iconBgColor="accent"
                        title={t.viewsThisMonth || 'Views This Month'}
                        value={statistics.words_updated_this_month}
                        description={t.monthlyViews || 'words viewed'}
                    />

                    <StatCard
                        icon={Eye}
                        iconBgColor="primary"
                        title={t.viewsToday || 'Views Today'}
                        value={statistics.words_updated_today}
                        description={t.dailyViews || 'words viewed today'}
                    />
                </div>

                <div className="mb-8 grid gap-4 sm:grid-cols-2">
                    <StatCard
                        icon={Zap}
                        iconBgColor="accent"
                        title={t.streak || 'Streak'}
                        value={statistics.streak_days}
                        description={t.daysInRow || 'days in a row'}
                    />

                    <StatCard
                        icon={TrendingUp}
                        iconBgColor="primary"
                        title={t.todayProgress || 'Today Progress'}
                        value={`${statistics.progress_today_percent}%`}
                        description={t.dailyGoal || 'of daily goal'}
                    />
                </div>

                <div className="mb-8">
                    <h2 className="mb-4 text-xl font-bold text-neutral-900 sm:text-2xl">
                        {t.mostViewedWords || 'Most Viewed Words'}
                    </h2>
                    {statistics.top_viewed_words.length > 0 ? (
                        <Card className="overflow-hidden shadow-sm">
                            <table className="w-full">
                                <thead>
                                    <tr className="border-b border-neutral-200 bg-neutral-50">
                                        <th className="px-6 py-3 text-left text-sm font-semibold text-neutral-900">
                                            #
                                        </th>
                                        <th className="px-6 py-3 text-left text-sm font-semibold text-neutral-900">
                                            {t.word || 'Word'}
                                        </th>
                                        <th className="px-6 py-3 text-left text-sm font-semibold text-neutral-900">
                                            {t.translation || 'Translation'}
                                        </th>
                                        <th className="px-6 py-3 text-left text-sm font-semibold text-neutral-900">
                                            {t.views || 'Views'}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {statistics.top_viewed_words.map(
                                        (word: WordData, index: number) => (
                                            <tr
                                                key={word.id}
                                                className="border-b border-neutral-100 last:border-0 hover:bg-neutral-50"
                                            >
                                                <td className="px-6 py-4 text-sm text-neutral-600">
                                                    {index + 1}
                                                </td>
                                                <td className="px-6 py-4 text-sm font-semibold text-neutral-900">
                                                    {word.original}
                                                </td>
                                                <td className="px-6 py-4 text-sm text-neutral-600">
                                                    {word.translated}
                                                </td>
                                                <td className="px-6 py-4 text-sm text-neutral-600">
                                                    <div className="flex items-center gap-2">
                                                        <Eye className="h-4 w-4 text-primary" />
                                                        {word.views}
                                                    </div>
                                                </td>
                                            </tr>
                                        ),
                                    )}
                                </tbody>
                            </table>
                        </Card>
                    ) : (
                        <Card className="p-8 text-center shadow-sm">
                            <p className="text-neutral-500">
                                {t.noWordsViewed || 'No words viewed yet'}
                            </p>
                        </Card>
                    )}
                </div>

                <div className="mb-8">
                    <h2 className="mb-4 text-xl font-bold text-neutral-900 sm:text-2xl">
                        {t.wordsAddedToday || 'Words Added Today'}
                    </h2>
                    {statistics.words_added_today.length > 0 ? (
                        <Card className="overflow-hidden shadow-sm">
                            <table className="w-full">
                                <thead>
                                    <tr className="border-b border-neutral-200 bg-neutral-50">
                                        <th className="px-6 py-3 text-left text-sm font-semibold text-neutral-900">
                                            #
                                        </th>
                                        <th className="px-6 py-3 text-left text-sm font-semibold text-neutral-900">
                                            {t.word || 'Word'}
                                        </th>
                                        <th className="px-6 py-3 text-left text-sm font-semibold text-neutral-900">
                                            {t.translation || 'Translation'}
                                        </th>
                                        <th className="px-6 py-3 text-left text-sm font-semibold text-neutral-900">
                                            {t.views || 'Views'}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {statistics.words_added_today.map(
                                        (word: WordData, index: number) => (
                                            <tr
                                                key={word.id}
                                                className="border-b border-neutral-100 last:border-0 hover:bg-neutral-50"
                                            >
                                                <td className="px-6 py-4 text-sm text-neutral-600">
                                                    {index + 1}
                                                </td>
                                                <td className="px-6 py-4 text-sm font-semibold text-neutral-900">
                                                    {word.original}
                                                </td>
                                                <td className="px-6 py-4 text-sm text-neutral-600">
                                                    {word.translated}
                                                </td>
                                                <td className="px-6 py-4 text-sm text-neutral-600">
                                                    <div className="flex items-center gap-2">
                                                        <Eye className="h-4 w-4 text-primary" />
                                                        {word.views}
                                                    </div>
                                                </td>
                                            </tr>
                                        ),
                                    )}
                                </tbody>
                            </table>
                        </Card>
                    ) : (
                        <Card className="p-8 text-center shadow-sm">
                            <p className="text-neutral-500">
                                {t.noWordsAddedToday || 'No words added today'}
                            </p>
                        </Card>
                    )}
                </div>

                <div className="flex justify-center">
                    <Button
                        variant="outline"
                        onClick={() => window.history.back()}
                        className="gap-2"
                    >
                        <ArrowRight className="h-4 w-4 rotate-180" />
                        {t.back || 'Back'}
                    </Button>
                </div>
            </main>
        </div>
    );
}
