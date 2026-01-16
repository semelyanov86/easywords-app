import { Button } from '@/components/ui/button';
import { StatsGrid, WordsTable } from '@/features/statistics';
import { statisticsTranslations } from '@/shared/i18n/statistics';
import { useStatisticsTranslation } from '@/shared/i18n/useStatisticsTranslation';
import { WordStatisticsData } from '@/shared/types/statistics';
import { User } from '@/types';
import { AuthHeader } from '@/widgets/auth/AuthHeader';
import { ArrowLeft, Award, Target } from 'lucide-react';

interface StatisticsPageProps {
    statistics: WordStatisticsData;
    user: User;
}

export default function Statistics({ user, statistics }: StatisticsPageProps) {
    const t = useStatisticsTranslation(statisticsTranslations);

    const mainStats = [
        {
            iconName: 'users' as const,
            colorScheme: 'primary' as const,
            title: t.totalUsers || 'Total Users',
            value: statistics.total_users,
            description: t.usersInSystem || 'in system',
        },
        {
            iconName: 'book-open' as const,
            colorScheme: 'secondary' as const,
            title: t.totalWords || 'Total Words',
            value: statistics.total_words,
            description: t.allWords || 'all words',
        },
        {
            iconName: 'book-open' as const,
            colorScheme: 'accent' as const,
            title: t.notDoneWords || 'Not Learned',
            value: statistics.not_done_words,
            description: t.wordsToStudy || 'words to study',
        },
        {
            iconName: 'book-open' as const,
            colorScheme: 'primary' as const,
            title: t.doneWords || 'Learned Words',
            value: statistics.done_words,
            description: t.wordsMastered || 'words mastered',
        },
    ];

    const viewsStats = [
        {
            iconName: 'eye' as const,
            colorScheme: 'secondary' as const,
            title: t.totalViews || 'Total Views',
            value: statistics.total_views,
            description: t.allViews || 'total views',
        },
        {
            iconName: 'eye' as const,
            colorScheme: 'accent' as const,
            title: t.viewsThisMonth || 'Views This Month',
            value: statistics.words_updated_this_month,
            description: t.monthlyViews || 'words viewed',
        },
        {
            iconName: 'eye' as const,
            colorScheme: 'primary' as const,
            title: t.viewsToday || 'Views Today',
            value: statistics.words_updated_today,
            description: t.dailyViews || 'words viewed today',
        },
    ];

    const progressStats = [
        {
            iconName: 'zap' as const,
            colorScheme: 'accent' as const,
            title: t.streak || 'Streak',
            value: statistics.streak_days,
            description: t.daysInRow || 'days in a row',
        },
        {
            iconName: 'trending-up' as const,
            colorScheme: 'primary' as const,
            title: t.todayProgress || 'Today Progress',
            value: `${statistics.progress_today_percent}%`,
            description: t.dailyGoal || 'of daily goal',
        },
    ];

    return (
        <div className="min-h-screen bg-gradient-to-br from-neutral-50 via-blue-50/30 to-green-50/30">
            <AuthHeader userName={user.name} />

            <main className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
                {/* Header */}
                <div className="mb-10">
                    <div className="flex items-center gap-3">
                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-[#1E5F8C] to-[#2B7DB8] shadow-lg shadow-blue-500/30">
                            <Award className="h-6 w-6 text-white" />
                        </div>
                        <div>
                            <h1 className="text-3xl font-bold text-neutral-900 sm:text-4xl">
                                {t.title || 'Personal Statistics'}
                            </h1>
                            <p className="mt-1 text-base text-neutral-600 sm:text-lg">
                                {t.subtitle ||
                                    'Track your learning progress and achievements'}
                            </p>
                        </div>
                    </div>
                </div>

                {/* Main Stats Grid */}
                <div className="mb-8">
                    <StatsGrid stats={mainStats} columns={4} />
                </div>

                {/* Views Stats Grid */}
                <div className="mb-8">
                    <StatsGrid stats={viewsStats} columns={3} />
                </div>

                {/* Progress Stats Grid */}
                <div className="mb-10">
                    <StatsGrid stats={progressStats} columns={2} />
                </div>

                {/* Most Viewed Words Section */}
                <div className="mb-8">
                    <div className="mb-5 flex items-center gap-3">
                        <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-[#7CB342] to-[#9CCC65] shadow-md shadow-green-500/20">
                            <Target className="h-5 w-5 text-white" />
                        </div>
                        <h2 className="text-2xl font-bold text-neutral-900">
                            {t.mostViewedWords || 'Most Viewed Words'}
                        </h2>
                    </div>
                    <WordsTable
                        words={statistics.top_viewed_words}
                        emptyMessage={t.noWordsViewed || 'No words viewed yet'}
                        translations={{
                            word: t.word || 'Word',
                            translation: t.translation || 'Translation',
                            views: t.views || 'Views',
                        }}
                    />
                </div>

                {/* Words Added Today Section */}
                <div className="mb-10">
                    <div className="mb-5 flex items-center gap-3">
                        <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-[#33691E] to-[#558B2F] shadow-md shadow-green-700/20">
                            <Award className="h-5 w-5 text-white" />
                        </div>
                        <h2 className="text-2xl font-bold text-neutral-900">
                            {t.wordsAddedToday || 'Words Added Today'}
                        </h2>
                    </div>
                    <WordsTable
                        words={statistics.words_added_today}
                        emptyMessage={
                            t.noWordsAddedToday || 'No words added today'
                        }
                        translations={{
                            word: t.word || 'Word',
                            translation: t.translation || 'Translation',
                            views: t.views || 'Views',
                        }}
                    />
                </div>

                {/* Back Button */}
                <div className="flex justify-center">
                    <Button
                        variant="outline"
                        onClick={() => window.history.back()}
                        className="gap-2 shadow-sm transition-all hover:shadow-md"
                    >
                        <ArrowLeft className="h-4 w-4" />
                        {t.back || 'Back'}
                    </Button>
                </div>
            </main>
        </div>
    );
}
