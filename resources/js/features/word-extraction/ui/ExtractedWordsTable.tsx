import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { ExtractedWord } from '@/features/word-extraction/types';
import { useTranslation } from '@/shared/i18n/useTranslation';
import { CheckCircle2, Languages, Plus } from 'lucide-react';

interface ExtractedWordsTableProps {
    words: ExtractedWord[];
    onAddWord: (
        original: string,
        translation: string,
        language: string,
    ) => void;
    isAddingWord?: boolean;
}

export function ExtractedWordsTable({
    words,
    onAddWord,
    isAddingWord,
}: ExtractedWordsTableProps) {
    const t = useTranslation();

    return (
        <Card className="border-neutral-200 bg-gradient-to-br from-white via-green-50/30 to-white shadow-lg dark:border-neutral-800 dark:from-neutral-900 dark:via-green-950/10 dark:to-neutral-900">
            <CardHeader className="space-y-3">
                <div className="flex items-center gap-3">
                    <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-secondary to-secondary/80 shadow-md">
                        <CheckCircle2 className="h-6 w-6 text-white" />
                    </div>
                    <div>
                        <CardTitle className="text-2xl font-bold text-neutral-900 dark:text-neutral-100">
                            {t.words.extracted_words_title}
                        </CardTitle>
                        <CardDescription className="text-base">
                            {t.words.extracted_words_description}
                        </CardDescription>
                    </div>
                </div>
            </CardHeader>
            <CardContent>
                <div className="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead>
                                <tr className="border-b-2 border-neutral-200 bg-neutral-50/50 dark:border-neutral-800 dark:bg-neutral-800/50">
                                    <th className="px-6 py-4 text-left text-sm font-bold text-neutral-900 dark:text-neutral-100">
                                        {t.words.original}
                                    </th>
                                    <th className="px-6 py-4 text-left text-sm font-bold text-neutral-900 dark:text-neutral-100">
                                        {t.words.translation}
                                    </th>
                                    <th className="px-6 py-4 text-left text-sm font-bold text-neutral-900 dark:text-neutral-100">
                                        <div className="flex items-center gap-2">
                                            <Languages className="h-4 w-4" />
                                            {t.words.language}
                                        </div>
                                    </th>
                                    <th className="px-6 py-4 text-right text-sm font-bold text-neutral-900 dark:text-neutral-100">
                                        {t.words.actions}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {words.map((word, index) => (
                                    <tr
                                        key={`${word.original}-${index}`}
                                        className="group border-b border-neutral-100 transition-colors hover:bg-green-50/50 dark:border-neutral-800/50 dark:hover:bg-green-950/20"
                                    >
                                        <td className="px-6 py-4 text-sm font-semibold text-neutral-900 dark:text-neutral-100">
                                            {word.original}
                                        </td>
                                        <td className="px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                            {word.translation}
                                        </td>
                                        <td className="px-6 py-4">
                                            <span className="inline-flex items-center rounded-lg bg-primary/10 px-3 py-1 text-xs font-bold text-primary dark:bg-primary/20 dark:text-primary/90">
                                                {word.language.toUpperCase()}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            <Button
                                                size="sm"
                                                onClick={() =>
                                                    onAddWord(
                                                        word.original,
                                                        word.translation,
                                                        word.language,
                                                    )
                                                }
                                                disabled={isAddingWord}
                                                className="rounded-lg bg-gradient-to-r from-secondary to-secondary/90 px-4 py-2 text-sm font-semibold shadow-sm transition-all hover:shadow-md disabled:opacity-50"
                                            >
                                                <Plus className="mr-1.5 h-4 w-4" />
                                                {t.words.add_word}
                                            </Button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}
