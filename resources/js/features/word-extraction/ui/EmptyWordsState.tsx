import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { useTranslation } from '@/shared/i18n/useTranslation';
import { FileQuestion } from 'lucide-react';

interface EmptyWordsStateProps {
    onBackToCreate: () => void;
}

export function EmptyWordsState({ onBackToCreate }: EmptyWordsStateProps) {
    const t = useTranslation();

    return (
        <Card className="border-neutral-200 bg-white shadow-lg dark:border-neutral-800 dark:bg-neutral-900">
            <CardContent className="flex flex-col items-center justify-center py-16">
                <div className="mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-neutral-100 dark:bg-neutral-800">
                    <FileQuestion className="h-10 w-10 text-neutral-400 dark:text-neutral-600" />
                </div>
                <p className="mb-6 text-center text-lg text-neutral-600 dark:text-neutral-400">
                    {t.words.no_words_extracted}
                </p>
                <Button
                    onClick={onBackToCreate}
                    className="rounded-xl bg-gradient-to-r from-primary to-primary/90 px-6 py-2.5 font-semibold shadow-md hover:shadow-lg"
                >
                    {t.words.back_to_create}
                </Button>
            </CardContent>
        </Card>
    );
}
