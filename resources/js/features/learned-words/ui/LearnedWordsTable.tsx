import { Checkbox } from '@/components/ui/checkbox';
import { WordData } from '@/features/word-search';
import { unlearned } from '@/routes/words';
import { useTranslation } from '@/shared/i18n/useTranslation';
import { useForm } from '@inertiajs/react';

interface LearnedWordsTableProps {
    words: WordData[];
}

export function LearnedWordsTable({ words }: LearnedWordsTableProps) {
    const t = useTranslation();
    const form = useForm({});

    const handleMarkUnlearned = (wordId: number): void => {
        form.post(unlearned(wordId).url);
    };

    if (words.length === 0) {
        return (
            <div className="rounded-2xl border border-neutral-200 bg-white px-6 py-16 text-center shadow-sm dark:border-neutral-700 dark:bg-neutral-800">
                <div className="mx-auto max-w-sm">
                    <div className="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-full bg-neutral-100 dark:bg-neutral-700">
                        <svg
                            className="h-8 w-8 text-neutral-400 dark:text-neutral-500"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth={2}
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                    </div>
                    <h3 className="text-lg font-semibold text-neutral-900 dark:text-white">
                        {t.words.learnedWords.noWords || 'Нет выученных слов'}
                    </h3>
                    <p className="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
                        {t.words.learnedWords.noWordsHint ||
                            'Начните учить слова, и они появятся здесь'}
                    </p>
                </div>
            </div>
        );
    }

    return (
        <div className="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-800">
            <div className="overflow-x-auto">
                <table className="w-full">
                    <thead>
                        <tr className="border-b border-neutral-200 bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-900">
                            <th className="px-6 py-4 text-left text-xs font-semibold tracking-wider text-neutral-600 uppercase dark:text-neutral-400">
                                {t.words.learnedWords.table.id || 'ID'}
                            </th>
                            <th className="px-6 py-4 text-left text-xs font-semibold tracking-wider text-neutral-600 uppercase dark:text-neutral-400">
                                {t.words.learnedWords.table.original || 'Слово'}
                            </th>
                            <th className="px-6 py-4 text-left text-xs font-semibold tracking-wider text-neutral-600 uppercase dark:text-neutral-400">
                                {t.words.learnedWords.table.translation ||
                                    'Перевод'}
                            </th>
                            <th className="px-6 py-4 text-center text-xs font-semibold tracking-wider text-neutral-600 uppercase dark:text-neutral-400">
                                {t.words.learnedWords.table.status || 'Статус'}
                            </th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-neutral-100 dark:divide-neutral-700">
                        {words.map((word) => (
                            <tr
                                key={word.id}
                                className="transition-colors hover:bg-neutral-50 dark:hover:bg-neutral-700/50"
                            >
                                <td className="px-6 py-4">
                                    <span className="inline-flex items-center rounded-md bg-neutral-100 px-2.5 py-0.5 font-mono text-xs font-medium text-neutral-700 dark:bg-neutral-700 dark:text-neutral-300">
                                        #{word.id}
                                    </span>
                                </td>
                                <td className="px-6 py-4">
                                    <div className="flex flex-col gap-1">
                                        <span className="text-base font-semibold text-neutral-900 dark:text-white">
                                            {word.original}
                                        </span>
                                        <span className="inline-flex w-fit items-center rounded-md bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary dark:bg-primary/20">
                                            {word.language.toUpperCase()}
                                        </span>
                                    </div>
                                </td>
                                <td className="px-6 py-4">
                                    <span className="text-sm text-neutral-700 dark:text-neutral-300">
                                        {word.translated}
                                    </span>
                                </td>
                                <td className="px-6 py-4">
                                    <div className="flex items-center justify-center">
                                        <Checkbox
                                            id={`word-${word.id}`}
                                            checked={true}
                                            disabled={form.processing}
                                            onCheckedChange={() =>
                                                handleMarkUnlearned(word.id)
                                            }
                                            className="border-primary-300 h-5 w-5 transition-all hover:scale-110 disabled:cursor-not-allowed disabled:opacity-50 data-[state=checked]:bg-primary data-[state=checked]:text-white"
                                        />
                                        <label
                                            htmlFor={`word-${word.id}`}
                                            className="sr-only"
                                        >
                                            {t.words.learnedWords.table
                                                .markUnlearned ||
                                                'Пометить как невыученное'}
                                        </label>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
