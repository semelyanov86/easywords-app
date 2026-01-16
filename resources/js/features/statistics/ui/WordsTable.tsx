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
            <Card className="p-12 text-center shadow-md">
                <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-neutral-100">
                    <Eye className="h-8 w-8 text-neutral-400" />
                </div>
                <p className="mt-4 text-neutral-500">{emptyMessage}</p>
            </Card>
        );
    }

    return (
        <Card className="overflow-hidden shadow-md">
            <div className="overflow-x-auto">
                <table className="w-full">
                    <thead>
                        <tr className="border-b border-neutral-200 bg-gradient-to-r from-neutral-50 to-neutral-100/50">
                            <th className="w-16 px-6 py-4 text-left text-xs font-semibold tracking-wider text-neutral-600 uppercase">
                                #
                            </th>
                            <th className="px-6 py-4 text-left text-xs font-semibold tracking-wider text-neutral-600 uppercase">
                                {translations.word}
                            </th>
                            <th className="px-6 py-4 text-left text-xs font-semibold tracking-wider text-neutral-600 uppercase">
                                {translations.translation}
                            </th>
                            <th className="w-32 px-6 py-4 text-left text-xs font-semibold tracking-wider text-neutral-600 uppercase">
                                {translations.views}
                            </th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-neutral-100">
                        {words.map((word: WordData, index: number) => (
                            <tr
                                key={word.id}
                                className="transition-colors duration-150 hover:bg-blue-50/50"
                            >
                                <td className="px-6 py-4 text-sm font-medium text-neutral-400">
                                    {index + 1}
                                </td>
                                <td className="px-6 py-4">
                                    <span className="text-base font-semibold text-neutral-900">
                                        {word.original}
                                    </span>
                                </td>
                                <td className="px-6 py-4 text-sm text-neutral-600">
                                    {word.translated}
                                </td>
                                <td className="px-6 py-4">
                                    <div className="flex items-center gap-2">
                                        <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100">
                                            <Eye className="h-4 w-4 text-[#1E5F8C]" />
                                        </div>
                                        <span className="text-sm font-medium text-neutral-700">
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
