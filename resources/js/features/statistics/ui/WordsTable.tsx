import { Card } from '@/components/ui/card';
import { WordData } from '@/shared/types/statistics';
import { Eye } from 'lucide-react';

interface WordsTableProps {
    words: WordData[];
    emptyMessage: string;
    translations: {
        word: string;
        translation: string;
        views: string;
    };
}

export function WordsTable({
    words,
    emptyMessage,
    translations,
}: WordsTableProps) {
    if (words.length === 0) {
        return (
            <Card className="border border-border bg-card p-12 text-center shadow-md">
                <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-muted">
                    <Eye className="h-8 w-8 text-muted-foreground" />
                </div>
                <p className="mt-4 text-muted-foreground">{emptyMessage}</p>
            </Card>
        );
    }

    return (
        <Card className="overflow-hidden border border-border bg-card shadow-md">
            <div className="overflow-x-auto">
                <table className="w-full">
                    <thead>
                        <tr className="border-b border-border bg-muted/50">
                            <th className="w-16 px-6 py-4 text-left text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                                #
                            </th>
                            <th className="px-6 py-4 text-left text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                                {translations.word}
                            </th>
                            <th className="px-6 py-4 text-left text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                                {translations.translation}
                            </th>
                            <th className="w-32 px-6 py-4 text-left text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                                {translations.views}
                            </th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-border">
                        {words.map((word: WordData, index: number) => (
                            <tr
                                key={word.id}
                                className="transition-colors duration-150 hover:bg-accent"
                            >
                                <td className="px-6 py-4 text-sm font-medium text-muted-foreground">
                                    {index + 1}
                                </td>
                                <td className="px-6 py-4">
                                    <span className="text-base font-semibold text-foreground">
                                        {word.original}
                                    </span>
                                </td>
                                <td className="px-6 py-4 text-sm text-card-foreground">
                                    {word.translated}
                                </td>
                                <td className="px-6 py-4">
                                    <div className="flex items-center gap-2">
                                        <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10">
                                            <Eye className="h-4 w-4 text-primary" />
                                        </div>
                                        <span className="text-sm font-medium text-foreground">
                                            {word.views}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </Card>
    );
}
