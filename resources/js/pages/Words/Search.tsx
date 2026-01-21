import type { WordData } from '@/features/word-search';
import { EmptyState, SearchForm, WordSearchCard } from '@/features/word-search';
import { useTranslation } from '@/shared/i18n/useTranslation';
import { User } from '@/types';
import { AuthHeader } from '@/widgets/auth/AuthHeader';
import { Head } from '@inertiajs/react';
import { Search as SearchIcon } from 'lucide-react';

interface WordSearchPageProps {
    query: string;
    results: WordData[];
    user: User;
}

export default function WordSearchPage({
    query,
    results,
    user,
}: WordSearchPageProps) {
    const t = useTranslation();

    const renderContent = () => {
        if (!query) {
            return (
                <EmptyState
                    icon={SearchIcon}
                    title={
                        t.words.enter_search_query || 'Введите поисковый запрос'
                    }
                    description={
                        t.words.enter_search_query_hint ||
                        'Введите слово или фразу для поиска в вашем словаре'
                    }
                />
            );
        }

        if (results.length === 0) {
            return (
                <EmptyState
                    icon={SearchIcon}
                    title={t.words.no_results_title || 'Ничего не найдено'}
                    description={
                        t.words.no_results_message ||
                        'Попробуйте изменить поисковый запрос или добавить новые слова'
                    }
                />
            );
        }

        return (
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <p className="text-sm font-medium text-muted-foreground">
                        {t.words.search_results_count?.replace(
                            ':count',
                            results.length.toString(),
                        ) || `Найдено результатов: ${results.length}`}
                    </p>

                    <div className="ml-4 h-px flex-1 bg-gradient-to-r from-border to-transparent" />
                </div>

                <div className="grid gap-4 sm:gap-6">
                    {results.map((word) => (
                        <WordSearchCard
                            key={word.id}
                            word={word}
                            learnedText={t.words.learned || 'Выучено'}
                            viewsText={t.words.views || 'Просмотров'}
                        />
                    ))}
                </div>
            </div>
        );
    };

    return (
        <>
            <Head title={`${t.words.search_title} - ${query || ''}`} />

            <div className="min-h-screen bg-background">
                <AuthHeader userName={user.name} />

                <main className="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
                    {/* Header Section */}
                    <header className="mb-10 sm:mb-12">
                        <div className="space-y-3">
                            <h1 className="bg-gradient-to-r from-primary via-primary to-secondary bg-clip-text text-4xl font-extrabold text-transparent sm:text-5xl">
                                {t.words.search_title || 'Поиск слов'}
                            </h1>

                            <p className="max-w-2xl text-base text-muted-foreground sm:text-lg">
                                {t.words.search_subtitle ||
                                    'Введите слово или фразу для поиска в вашем словаре'}
                            </p>
                        </div>
                    </header>

                    {/* Search Form */}
                    <div className="mb-12">
                        <SearchForm
                            initialQuery={query}
                            placeholder={
                                t.words.search_placeholder ||
                                'Введите слово для поиска...'
                            }
                            buttonText={t.words.search_button || 'Найти'}
                        />
                    </div>

                    {/* Results Section */}
                    <section>{renderContent()}</section>
                </main>
            </div>
        </>
    );
}
