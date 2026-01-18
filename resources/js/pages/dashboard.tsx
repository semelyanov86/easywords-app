import { Button } from '@/components/ui/button';
import { DashboardSearchSection } from '@/features/word-search/ui/DashboardSearchSection';
import { start } from '@/routes/study';
import { dashboardTranslations } from '@/shared/i18n/dashboard';
import { useDashboardTranslation } from '@/shared/i18n/useDashboardTranslation';
import { useFlashMessages } from '@/shared/lib/useFlashMessages';
import { AuthHeader } from '@/widgets/auth/AuthHeader';
import { StatisticCard } from '@/widgets/dashboard/StatisticCard';
import { Head, Link } from '@inertiajs/react';
import { ArrowRight, BookOpen, TrendingUp, Zap } from 'lucide-react';

interface User {
    id: number;
    name: string;
    email: string;
}

interface UserSettings {
    main_language: string;
    default_language: string;
    languages_list: string[];
}

interface Statistics {
    done_words: number;
    progress_today_percent: number;
    streak_days: number;
}

interface DashboardPageProps {
    user: User;
    settings: UserSettings;
    statistics: Statistics;
}

export default function Dashboard({
    user,
    settings,
    statistics,
}: DashboardPageProps) {
    const t = useDashboardTranslation(dashboardTranslations);
    const { main_language, languages_list } = settings;
    useFlashMessages();

    return (
        <>
            <Head title={t.pageTitle || 'Easywords Dashboard'} />
            <div className="min-h-screen bg-gradient-to-br from-neutral-50 via-blue-50/30 to-green-50/20">
                <AuthHeader userName={user.name} />

                <main className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
                    <div className="mb-6">
                        <div className="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h1 className="text-3xl font-bold text-neutral-900 sm:text-4xl">
                                    {t.chooseDirection ||
                                        'Choose Learning Direction'}
                                </h1>
                                <p className="mt-2 text-base text-neutral-600 sm:text-lg">
                                    {t.selectPair ||
                                        'Select a language pair to start practicing'}
                                </p>
                            </div>
                            <div className="flex items-center gap-2 rounded-full bg-white px-4 py-2 shadow-sm">
                                <Zap className="h-5 w-5 text-primary" />
                                <span className="text-sm font-semibold text-neutral-700">
                                    {t.welcome}, {user.name}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div className="mb-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {languages_list.map((language) => (
                            <div
                                key={language}
                                className="group rounded-2xl border border-neutral-200 bg-white p-6 shadow-sm transition-all hover:shadow-xl"
                            >
                                <div className="mb-6 flex items-center justify-between">
                                    <div className="flex items-center gap-3">
                                        <div className="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-primary to-secondary text-xl font-bold text-white">
                                            {language.charAt(0).toUpperCase()}
                                        </div>
                                        <div>
                                            <h3 className="text-lg font-bold text-neutral-900">
                                                {language}
                                            </h3>
                                            <p className="text-sm text-neutral-500">
                                                {t.practice || 'Practice'}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div className="space-y-3">
                                    <Link
                                        href={start({
                                            query: {
                                                language: language,
                                                reverse: 0,
                                            },
                                        })}
                                    >
                                        <Button
                                            type="button"
                                            variant="outline"
                                            className="group/btn relative h-auto w-full overflow-hidden border-2 border-neutral-200 p-4 text-left transition-all hover:border-primary hover:bg-primary hover:text-white"
                                        >
                                            <div className="flex items-center justify-between">
                                                <div className="flex items-center gap-3">
                                                    <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-neutral-100 text-sm font-bold text-neutral-700 transition-colors group-hover/btn:bg-white/20 group-hover/btn:text-white">
                                                        {main_language
                                                            .substring(0, 2)
                                                            .toUpperCase()}
                                                    </div>
                                                    <ArrowRight className="h-5 w-5 text-neutral-400 transition-colors group-hover/btn:text-white" />
                                                    <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-neutral-100 text-sm font-bold text-neutral-700 transition-colors group-hover/btn:bg-white/20 group-hover/btn:text-white">
                                                        {language
                                                            .substring(0, 2)
                                                            .toUpperCase()}
                                                    </div>
                                                </div>
                                                <ArrowRight className="h-5 w-5 translate-x-0 transition-transform group-hover/btn:translate-x-1" />
                                            </div>
                                            <p className="mt-2 text-xs font-medium text-neutral-600 transition-colors group-hover/btn:text-blue-100">
                                                {main_language} → {language}
                                            </p>
                                        </Button>
                                    </Link>

                                    <Link
                                        href={start({
                                            query: {
                                                language: language,
                                                reverse: 1,
                                            },
                                        })}
                                    >
                                        <Button
                                            type="button"
                                            variant="outline"
                                            className="group/btn relative h-auto w-full overflow-hidden border-2 border-neutral-200 p-4 text-left transition-all hover:border-secondary hover:bg-secondary hover:text-white"
                                        >
                                            <div className="flex items-center justify-between">
                                                <div className="flex items-center gap-3">
                                                    <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-neutral-100 text-sm font-bold text-neutral-700 transition-colors group-hover/btn:bg-white/20 group-hover/btn:text-white">
                                                        {language
                                                            .substring(0, 2)
                                                            .toUpperCase()}
                                                    </div>
                                                    <ArrowRight className="h-5 w-5 text-neutral-400 transition-colors group-hover/btn:text-white" />
                                                    <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-neutral-100 text-sm font-bold text-neutral-700 transition-colors group-hover/btn:bg-white/20 group-hover/btn:text-white">
                                                        {main_language
                                                            .substring(0, 2)
                                                            .toUpperCase()}
                                                    </div>
                                                </div>
                                                <ArrowRight className="h-5 w-5 translate-x-0 transition-transform group-hover/btn:translate-x-1" />
                                            </div>
                                            <p className="mt-2 text-xs font-medium text-neutral-600 transition-colors group-hover/btn:text-green-100">
                                                {language} → {main_language}
                                            </p>
                                        </Button>
                                    </Link>
                                </div>
                            </div>
                        ))}
                    </div>

                    <DashboardSearchSection
                        title={t.searchWords || 'Поиск слов'}
                        placeholder={
                            t.searchPlaceholder || 'Введите слово для поиска...'
                        }
                        buttonText={t.searchButton || 'Найти'}
                    />

                    <div className="mb-6">
                        <h2 className="mb-4 text-xl font-bold text-neutral-900 sm:text-2xl">
                            {t.yourProgress || 'Your Progress'}
                        </h2>
                    </div>

                    <div className="grid gap-4 sm:grid-cols-3 sm:gap-6">
                        <StatisticCard
                            icon={BookOpen}
                            iconBgColor="secondary"
                            title={t.totalWords || 'Total Words'}
                            value={statistics.done_words}
                            description={t.wordsLearned || 'words learned'}
                        />

                        <StatisticCard
                            icon={TrendingUp}
                            iconBgColor="primary"
                            title={t.todayProgress || 'Today Progress'}
                            value={`${statistics.progress_today_percent}%`}
                            description={t.dailyGoal || 'of daily goal'}
                        />

                        <StatisticCard
                            icon={Zap}
                            iconBgColor="accent"
                            title={t.streak || 'Streak'}
                            value={statistics.streak_days}
                            description={t.daysInRow || 'days in a row'}
                        />
                    </div>
                </main>
            </div>
        </>
    );
}
