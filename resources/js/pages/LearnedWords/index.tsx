import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { WordData } from '@/features/word-search';
import { dashboard } from '@/routes';
import { unlearned } from '@/routes/words';
import { useTranslation } from '@/shared/i18n/useTranslation';
import { useFlashMessages } from '@/shared/lib/useFlashMessages';
import { User } from '@/types';
import { AuthHeader } from '@/widgets/auth/AuthHeader';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { ArrowLeft, CheckCircle2 } from 'lucide-react';

interface PaginationMeta {
    current_page: number;
    from: number | null;
    last_page: number;
    path: string;
    per_page: number;
    to: number | null;
    total: number;
}

interface PaginationLink {
    active: boolean;
    label: string;
    page: string | null;
    url: string | null;
}

interface LearnedWordsPageProps {
    words: {
        data: WordData[];
        meta: PaginationMeta;
        links: PaginationLink[];
    };
    user: User;
}

export default function LearnedWordsPage({
    words,
    user,
}: LearnedWordsPageProps) {
    const t = useTranslation();
    const form = useForm({});
    useFlashMessages();

    const handleMarkUnlearned = (wordId: number) => {
        form.post(unlearned(wordId).url);
    };

    return (
        <>
            <Head title={t.words.learnedWords.title || 'Выученные слова'} />
            <div className="from-primary-50 to-secondary-50 min-h-screen bg-gradient-to-br via-white dark:from-neutral-900 dark:via-neutral-800 dark:to-neutral-900">
                <AuthHeader userName={user.name} />
                <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
                    {/* Header */}
                    <div className="mb-8">
                        <Link
                            href={dashboard().url}
                            className="dark:hover:text-primary-400 mb-4 inline-flex items-center gap-2 text-sm font-medium text-neutral-600 transition-colors hover:text-primary dark:text-neutral-400"
                        >
                            <ArrowLeft className="h-4 w-4" />
                            {t.words.learnedWords.backToDashboard ||
                                'Вернуться на дашборд'}
                        </Link>

                        <h1 className="text-primary-900 dark:text-primary-100 text-4xl font-bold tracking-tight sm:text-5xl">
                            {t.words.learnedWords.title || 'Выученные слова'}
                        </h1>
                        <p className="text-primary-700 dark:text-primary-300 mt-3 text-lg">
                            {t.words.learnedWords.subtitle ||
                                'Список всех слов, которые вы выучили. Снимите галочку, чтобы пометить слово как невыученное.'}
                        </p>
                    </div>

                    {/* Stats */}
                    <div className="mb-8 flex items-center gap-4 rounded-2xl bg-white/80 p-6 shadow-sm dark:bg-neutral-800/80">
                        <div className="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-secondary to-accent text-white">
                            <CheckCircle2 className="h-6 w-6" />
                        </div>
                        <div>
                            <p className="text-sm font-medium text-neutral-600 dark:text-neutral-400">
                                {t.words.learnedWords.totalWords ||
                                    'Всего выученных слов'}
                            </p>
                            <p className="text-primary-900 dark:text-primary-100 text-2xl font-bold">
                                {words.meta.total}
                            </p>
                        </div>
                    </div>

                    {/* Table */}
                    <div className="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-800">
                        <table className="w-full">
                            <thead>
                                <tr className="border-b border-neutral-200 bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-900">
                                    <th className="px-6 py-4 text-left text-sm font-semibold text-neutral-700 dark:text-neutral-300">
                                        {t.words.learnedWords.table.id || 'ID'}
                                    </th>
                                    <th className="px-6 py-4 text-left text-sm font-semibold text-neutral-700 dark:text-neutral-300">
                                        {t.words.learnedWords.table.original ||
                                            'Слово'}
                                    </th>
                                    <th className="px-6 py-4 text-left text-sm font-semibold text-neutral-700 dark:text-neutral-300">
                                        {t.words.learnedWords.table
                                            .translation || 'Перевод'}
                                    </th>
                                    <th className="px-6 py-4 text-center text-sm font-semibold text-neutral-700 dark:text-neutral-300">
                                        {t.words.learnedWords.table.status ||
                                            'Статус'}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {words.data.length === 0 ? (
                                    <tr>
                                        <td
                                            colSpan={4}
                                            className="px-6 py-12 text-center text-neutral-500 dark:text-neutral-400"
                                        >
                                            <p className="text-lg font-medium">
                                                {t.words.learnedWords.noWords ||
                                                    'Нет выученных слов'}
                                            </p>
                                            <p className="mt-2 text-sm">
                                                {t.words.learnedWords
                                                    .noWordsHint ||
                                                    'Начните учить слова, и они появятся здесь'}
                                            </p>
                                        </td>
                                    </tr>
                                ) : (
                                    words.data.map((word) => (
                                        <tr
                                            key={word.id}
                                            className="border-b border-neutral-100 transition-colors hover:bg-neutral-50 dark:border-neutral-700 dark:hover:bg-neutral-700/50"
                                        >
                                            <td className="px-6 py-4">
                                                <span className="font-mono text-sm text-neutral-600 dark:text-neutral-400">
                                                    #{word.id}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="flex flex-col">
                                                    <span className="font-semibold text-neutral-900 dark:text-white">
                                                        {word.original}
                                                    </span>
                                                    <span className="text-xs text-neutral-500 dark:text-neutral-400">
                                                        {word.language.toUpperCase()}
                                                    </span>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4">
                                                <span className="text-neutral-700 dark:text-neutral-300">
                                                    {word.translated}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 text-center">
                                                <div className="flex items-center justify-center">
                                                    <Checkbox
                                                        id={`word-${word.id}`}
                                                        checked={true}
                                                        onCheckedChange={() =>
                                                            handleMarkUnlearned(
                                                                word.id,
                                                            )
                                                        }
                                                        className="border-primary-300 h-5 w-5 data-[state=checked]:bg-primary data-[state=checked]:text-white"
                                                    />
                                                    <label
                                                        htmlFor={`word-${word.id}`}
                                                        className="sr-only"
                                                    >
                                                        {t.words.learnedWords
                                                            .table
                                                            .markUnlearned ||
                                                            'Пометить как невыученное'}
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    {words.meta.last_page > 1 && (
                        <div className="mt-6 flex items-center justify-center gap-2">
                            {words.links.map((link, index) => (
                                <Button
                                    key={index}
                                    variant={
                                        link.active ? 'default' : 'outline'
                                    }
                                    size="sm"
                                    disabled={link.url === null}
                                    className={
                                        link.active
                                            ? 'hover:bg-primary-600 bg-primary'
                                            : ''
                                    }
                                    onClick={() => {
                                        if (link.url) {
                                            router.visit(link.url);
                                        }
                                    }}
                                    dangerouslySetInnerHTML={{
                                        __html: link.label,
                                    }}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}
