import { LearnedWordsStats, LearnedWordsTable } from '@/features/learned-words';
import { WordData } from '@/features/word-search';
import { dashboard } from '@/routes';
import { useTranslation } from '@/shared/i18n/useTranslation';
import { useFlashMessages } from '@/shared/lib/useFlashMessages';
import { PaginatedResponse } from '@/shared/types/pagination';
import { Pagination } from '@/shared/ui/Pagination';
import { User } from '@/types';
import { AuthHeader } from '@/widgets/auth/AuthHeader';
import { PageHeader } from '@/widgets/PageHeader';
import { Head } from '@inertiajs/react';
import { BookCheck } from 'lucide-react';

interface LearnedWordsPageProps {
    words: PaginatedResponse<WordData>;
    user: User;
}

export default function LearnedWordsPage({
    words,
    user,
}: LearnedWordsPageProps) {
    const t = useTranslation();
    useFlashMessages();

    return (
        <>
            <Head title={t.words.learnedWords.title || 'Выученные слова'} />
            <div className="from-primary-50 to-secondary-50 dark:via-neutral-850 min-h-screen bg-gradient-to-br via-white dark:from-neutral-900 dark:to-neutral-900">
                <AuthHeader userName={user.name} />

                <main className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
                    <PageHeader
                        title={t.words.learnedWords.title || 'Выученные слова'}
                        subtitle={
                            t.words.learnedWords.subtitle ||
                            'Список всех слов, которые вы выучили. Снимите галочку, чтобы пометить слово как невыученное.'
                        }
                        backLink={{
                            url: dashboard().url,
                            label:
                                t.words.learnedWords.backToDashboard ||
                                'Вернуться на дашборд',
                        }}
                        icon={BookCheck}
                    />

                    <div className="space-y-6">
                        <LearnedWordsStats total={words.meta.total} />
                        <LearnedWordsTable words={words.data} />
                        <Pagination
                            links={words.links}
                            lastPage={words.meta.last_page}
                        />
                    </div>
                </main>
            </div>
        </>
    );
}
