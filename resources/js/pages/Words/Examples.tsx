import { Button } from '@/components/ui/button';
import { ExamplesContent } from '@/features/word-examples/ui/ExamplesContent';
import { FlipButton } from '@/features/word-examples/ui/FlipButton';
import { useTranslation } from '@/shared/i18n/useTranslation';
import { FlipCard } from '@/shared/ui/FlipCard/FlipCard';
import { User } from '@/types';
import { AuthHeader } from '@/widgets/auth/AuthHeader';
import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';

interface Translations {
    words: {
        examples_title: string;
        examples_original: string;
        examples_translated: string;
        back_to_word: string;
        flip: string;
    };
}

interface ExampleData {
    original: string[];
    translated: string[];
}

interface WordData {
    id: number;
    original: string;
    translated: string;
    language: string;
    targetLanguage?: string;
}

interface WordExamplesPageProps {
    word: WordData;
    examples: ExampleData;
    user: User;
}

export default function WordExamplesPage({
    word,
    examples,
    user,
}: WordExamplesPageProps) {
    const t = useTranslation() as Translations;
    const [isFlipped, setIsFlipped] = useState(false);

    const frontContent = (
        <ExamplesContent
            examples={examples.original}
            title={t.words.examples_original}
            wordText={word.original}
            language={word.language}
            variant="original"
        />
    );

    const backContent = (
        <ExamplesContent
            examples={examples.translated}
            title={t.words.examples_translated}
            wordText={word.translated}
            language={word.targetLanguage ?? 'RU'}
            variant="translated"
        />
    );

    return (
        <>
            <Head title={`${t.words.examples_title} - ${word.original}`} />

            <div className="from-primary-50 to-secondary-50 min-h-screen bg-gradient-to-br via-white dark:from-neutral-900 dark:via-neutral-800 dark:to-neutral-900">
                <AuthHeader userName={user.name} />

                {/* Page header with back button */}
                <header className="border-primary-200/50 dark:border-primary-800/50 sticky top-0 z-10 border-b bg-white/80 backdrop-blur-md dark:bg-neutral-900/80">
                    <div className="mx-auto max-w-5xl px-4 py-6">
                        <div className="flex items-center justify-between">
                            <div className="flex items-center gap-4">
                                <Link
                                    href={`/words/${word.id}`}
                                    className="group"
                                >
                                    <Button
                                        variant="outline"
                                        className="border-primary-300 text-primary-700 dark:border-primary-700 dark:text-primary-300 hover:bg-primary-50 hover:border-primary-400 hover:text-primary-800 dark:hover:bg-primary-950/40 dark:hover:border-primary-600 transition-all duration-200 hover:-translate-x-1 hover:shadow-md"
                                    >
                                        <span className="inline-block transition-transform group-hover:-translate-x-1">
                                            ←
                                        </span>
                                        <span className="ml-2">
                                            {t.words.back_to_word}
                                        </span>
                                    </Button>
                                </Link>

                                <div className="flex items-center gap-3">
                                    <span className="text-3xl">💡</span>
                                    <h1 className="text-primary-900 dark:text-primary-100 text-2xl font-bold">
                                        {t.words.examples_title}
                                    </h1>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                {/* Main content */}
                <main className="mx-auto max-w-5xl px-4 py-12">
                    {/* Flashcard with examples */}
                    <div className="mx-auto max-w-3xl space-y-8">
                        <FlipCard
                            isFlipped={isFlipped}
                            onFlip={() => setIsFlipped(!isFlipped)}
                            frontContent={frontContent}
                            backContent={backContent}
                            isLearned={false}
                        />

                        {/* Flip button with hint */}
                        <div className="flex flex-col items-center gap-4">
                            <FlipButton
                                onClick={() => setIsFlipped(!isFlipped)}
                                isFlipped={isFlipped}
                                label={t.words.flip}
                            />

                            <p className="text-primary-600 dark:text-primary-400 animate-pulse text-sm">
                                {isFlipped
                                    ? '👆 Вернуться к оригиналу'
                                    : '👆 Показать перевод'}
                            </p>
                        </div>
                    </div>
                </main>
            </div>

            {/* Custom scrollbar styles */}
            <style>{`
                .custom-scrollbar::-webkit-scrollbar {
                    width: 8px;
                }
                .custom-scrollbar::-webkit-scrollbar-track {
                    background: transparent;
                }
                .custom-scrollbar::-webkit-scrollbar-thumb {
                    background: rgb(30 95 140 / 0.3);
                    border-radius: 4px;
                }
                .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                    background: rgb(30 95 140 / 0.5);
                }
                .dark .custom-scrollbar::-webkit-scrollbar-thumb {
                    background: rgb(30 95 140 / 0.5);
                }
                .dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                    background: rgb(30 95 140 / 0.7);
                }
            `}</style>
        </>
    );
}
