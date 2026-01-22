import { Button } from '@/components/ui/button';
import { AddWordForm } from '@/features/word/ui/AddWordForm';
import { extract } from '@/routes/words/extract-from-image';
import { useTranslation } from '@/shared/i18n/useTranslation';
import { useFlashMessages } from '@/shared/lib/useFlashMessages';
import { User } from '@/types';
import { AuthHeader } from '@/widgets/auth/AuthHeader';
import { Head, router } from '@inertiajs/react';
import { Image as ImageIcon } from 'lucide-react';

interface CreateWordPageProps {
    languages_list: string[];
    user: User;
    word?: {
        id: number;
    };
}

export default function CreateWordPage({
    languages_list,
    word,
    user,
}: CreateWordPageProps) {
    const t = useTranslation();
    useFlashMessages();

    return (
        <>
            <Head title={t.words.page_title} />
            <AuthHeader userName={user.name} />
            <div className="mx-auto max-w-3xl px-4 py-8 md:py-12">
                <div className="mb-8">
                    <h1 className="text-3xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100">
                        {t.words.add_new_word}
                    </h1>
                    <p className="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
                        {t.words.page_description}
                    </p>
                </div>
                <AddWordForm
                    languages={languages_list}
                    createdWordId={word?.id}
                />
                <Button
                    onClick={() => router.visit(extract().url)}
                    variant="outline"
                    className="mb-6 w-full"
                >
                    <ImageIcon className="mr-2 h-5 w-5" />
                    {t.words.extract_from_image_title}
                </Button>
            </div>
        </>
    );
}
