import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { show } from '@/routes/words';
import { useTranslation } from '@/shared/i18n/useTranslation';
import { User } from '@/types';
import { AuthHeader } from '@/widgets/auth/AuthHeader';
import { Head, router, useForm } from '@inertiajs/react';
import { Search as SearchIcon } from 'lucide-react';

interface WordData {
    id: number;
    original: string;
    translated: string;
    language: string;
    done_at: string | null;
    starred: boolean;
    views: number;
    from_sample: boolean;
    user_id: number;
    created_at: string;
    updated_at: string;
}

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

    const searchForm = useForm({
        query: query,
    });

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(
            '/words/search',
            { q: searchForm.data.query },
            {
                preserveState: true,
            },
        );
    };

    const handleWordClick = (wordId: number | string) => {
        if (typeof wordId === 'string') {
            wordId = parseInt(wordId);
        }
        router.visit(show(wordId).url);
    };

    return (
        <>
            <Head title={`${t.words.search_title} - ${query}`} />
            <div className="min-h-screen bg-gradient-to-br from-neutral-50 via-blue-50/30 to-green-50/20">
                <AuthHeader userName={user.name} />

                <main className="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
                    {/* Header */}
                    <div className="mb-8">
                        <h1 className="text-3xl font-bold text-neutral-900 sm:text-4xl">
                            {t.words.search_title || 'Поиск слов'}
                        </h1>
                        <p className="mt-2 text-base text-neutral-600 sm:text-lg">
                            {t.words.search_subtitle ||
                                'Введите слово или фразу для поиска в вашем словаре'}
                        </p>
                    </div>

                    {/* Search Form */}
                    <form onSubmit={handleSearch} className="mb-8">
                        <div className="relative">
                            <SearchIcon className="absolute top-1/2 left-4 h-5 w-5 -translate-y-1/2 text-neutral-400" />
                            <Input
                                type="text"
                                placeholder={
                                    t.words.search_placeholder ||
                                    'Введите слово для поиска...'
                                }
                                value={searchForm.data.query}
                                onChange={(e) =>
                                    searchForm.setData('query', e.target.value)
                                }
                                className="h-14 pr-4 pl-12 text-base shadow-sm focus:ring-2 focus:ring-primary"
                            />
                            <Button
                                type="submit"
                                disabled={searchForm.processing}
                                className="hover:bg-primary-600 absolute top-1/2 right-2 h-10 -translate-y-1/2 bg-primary"
                            >
                                {t.words.search_button || 'Найти'}
                            </Button>
                        </div>
                    </form>

                    {/* Results */}
                    {query && results.length > 0 && (
                        <div>
                            <div className="mb-4 text-sm text-neutral-600">
                                {t.words.search_results_count?.replace(
                                    ':count',
                                    results.length.toString(),
                                ) || `Найдено результатов: ${results.length}`}
                            </div>

                            <div className="space-y-3">
                                {results.map((word) => (
                                    <button
                                        key={word.id}
                                        type="button"
                                        onClick={() => handleWordClick(word.id)}
                                        className="group w-full rounded-xl border border-neutral-200 bg-white p-6 text-left shadow-sm transition-all hover:border-primary hover:shadow-md"
                                    >
                                        <div className="flex items-start justify-between">
                                            <div className="flex-1">
                                                <div className="mb-2 flex items-center gap-3">
                                                    <span className="text-2xl font-bold text-neutral-900 transition-colors group-hover:text-primary">
                                                        {word.original}
                                                    </span>
                                                    <span className="inline-flex items-center rounded-full bg-neutral-100 px-2.5 py-0.5 text-xs font-semibold text-neutral-600">
                                                        {word.language}
                                                    </span>
                                                    {word.starred && (
                                                        <span className="text-yellow-500">
                                                            ★
                                                        </span>
                                                    )}
                                                    {word.done_at && (
                                                        <span className="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-700">
                                                            {t.words.learned ||
                                                                'Выучено'}
                                                        </span>
                                                    )}
                                                </div>
                                                <div className="text-lg text-neutral-700">
                                                    {word.translated}
                                                </div>
                                            </div>
                                            <div className="ml-4 flex flex-col items-end gap-2 text-sm text-neutral-500">
                                                <span>
                                                    {t.words.views ||
                                                        'Просмотров'}
                                                    : {word.views}
                                                </span>
                                                <span className="text-xs text-neutral-400">
                                                    {new Date(
                                                        word.created_at,
                                                    ).toLocaleDateString()}
                                                </span>
                                            </div>
                                        </div>
                                    </button>
                                ))}
                            </div>
                        </div>
                    )}

                    {query && results.length === 0 && (
                        <div className="rounded-xl border-2 border-dashed border-neutral-300 bg-white p-12 text-center">
                            <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-neutral-100">
                                <SearchIcon className="h-8 w-8 text-neutral-400" />
                            </div>
                            <h3 className="mb-2 text-lg font-semibold text-neutral-900">
                                {t.words.no_results_title ||
                                    'Ничего не найдено'}
                            </h3>
                            <p className="text-neutral-600">
                                {t.words.no_results_message ||
                                    'Попробуйте изменить поисковый запрос или добавить новые слова'}
                            </p>
                        </div>
                    )}

                    {!query && (
                        <div className="rounded-xl border-2 border-dashed border-neutral-300 bg-white p-12 text-center">
                            <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-neutral-100">
                                <SearchIcon className="h-8 w-8 text-neutral-400" />
                            </div>
                            <h3 className="mb-2 text-lg font-semibold text-neutral-900">
                                {t.words.enter_search_query ||
                                    'Введите поисковый запрос'}
                            </h3>
                            <p className="text-neutral-600">
                                {t.words.enter_search_query_hint ||
                                    'Введите слово или фразу для поиска в вашем словаре'}
                            </p>
                        </div>
                    )}
                </main>
            </div>
        </>
    );
}
