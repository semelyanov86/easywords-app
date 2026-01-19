import { useTranslation } from '@/shared/i18n/useTranslation';
import { CheckCircle2 } from 'lucide-react';

interface LearnedWordsStatsProps {
    total: number;
}

export function LearnedWordsStats({ total }: LearnedWordsStatsProps) {
    const t = useTranslation();

    return (
        <div className="group rounded-2xl bg-gradient-to-br from-secondary/10 via-white to-accent/10 p-6 shadow-sm transition-all hover:shadow-md dark:from-secondary/20 dark:via-neutral-800 dark:to-accent/20">
            <div className="flex items-center gap-4">
                <div className="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-secondary to-accent text-white shadow-lg transition-transform group-hover:scale-110">
                    <CheckCircle2 className="h-7 w-7" />
                </div>
                <div>
                    <p className="text-sm font-medium text-neutral-600 dark:text-neutral-400">
                        {t.words.learnedWords.totalWords ||
                            'Всего выученных слов'}
                    </p>
                    <p className="dark:text-primary-300 mt-1 text-3xl font-bold text-primary">
                        {total.toLocaleString()}
                    </p>
                </div>
            </div>
        </div>
    );
}
