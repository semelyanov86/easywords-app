import { Button } from '@/components/ui/button';
import { ExamplesContent } from '@/features/word-examples/ui/ExamplesContent';
import { FlipButton } from '@/features/word-examples/ui/FlipButton';
import { show } from '@/routes/words';
import { useTranslation } from '@/shared/i18n/useTranslation';
import { User } from '@/types';
import { AuthHeader } from '@/widgets/auth/AuthHeader';
import { Head, router } from '@inertiajs/react';
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

    const handleBack = () => {
        if (window.history.length > 1) {
            window.history.back();
        } else {
            router.visit(show(word.id));
        }
    };

    return (
        <>
            <Head title={`${t.words.examples_title} - ${word.original}`} />

            <div className="from-primary-50 to-secondary-50 min-h-screen bg-gradient-to-br via-white dark:from-neutral-900 dark:via-neutral-800 dark:to-neutral-900">
                <AuthHeader userName={user.name} />

                {/* Page header */}
                <header className="border-primary-200/50 dark:border-primary-800/50 border-b bg-white/80 backdrop-blur-md dark:bg-neutral-900/80">
                    <div className="mx-auto max-w-6xl px-4 py-4">
                        <div className="flex items-center gap-4">
                            <Button
                                variant="outline"
                                onClick={handleBack}
                                className="group border-primary-300 text-primary-700 dark:border-primary-700 dark:text-primary-300 hover:bg-primary-50 hover:border-primary-400 hover:text-primary-800 dark:hover:bg-primary-950/40 dark:hover:border-primary-600 transition-all duration-200 hover:-translate-x-1 hover:shadow-md"
                            >
                                <span className="inline-block transition-transform group-hover:-translate-x-1">
                                    ←
                                </span>
                                <span className="ml-2">
                                    {t.words.back_to_word}
                                </span>
                            </Button>

                            <div className="flex items-center gap-2">
                                <span className="text-2xl">💡</span>
                                <h1 className="text-primary-900 dark:text-primary-100 text-xl font-bold">
                                    {t.words.examples_title}
                                </h1>
                            </div>
                        </div>
                    </div>
                </header>

                {/* Main content */}
                <main className="mx-auto max-w-6xl px-4 py-8">
                    <div className="mx-auto max-w-4xl space-y-6">
                        {/* Card with flip animation */}
                        <div className="perspective-1000">
                            <div
                                className="relative min-h-[500px] transition-transform duration-700"
                                style={{
                                    transformStyle: 'preserve-3d',
                                    transform: isFlipped
                                        ? 'rotateY(180deg)'
                                        : 'rotateY(0deg)',
                                }}
                            >
                                {/* Front side - Original */}
                                <div
                                    className="absolute inset-0 rounded-2xl bg-white p-8 shadow-2xl dark:bg-neutral-800"
                                    style={{
                                        backfaceVisibility: 'hidden',
                                        WebkitBackfaceVisibility: 'hidden',
                                    }}
                                >
                                    <ExamplesContent
                                        examples={examples.original}
                                        title={t.words.examples_original}
                                        wordText={word.original}
                                        language={word.language}
                                        variant="original"
                                    />
                                </div>

                                {/* Back side - Translated */}
                                <div
                                    className="absolute inset-0 rounded-2xl bg-white p-8 shadow-2xl dark:bg-neutral-800"
                                    style={{
                                        backfaceVisibility: 'hidden',
                                        WebkitBackfaceVisibility: 'hidden',
                                        transform: 'rotateY(180deg)',
                                    }}
                                >
                                    <ExamplesContent
                                        examples={examples.translated}
                                        title={t.words.examples_translated}
                                        wordText={word.translated}
                                        language={word.targetLanguage ?? 'RU'}
                                        variant="translated"
                                    />
                                </div>
                            </div>
                        </div>

                        {/* Flip button - always visible */}
                        <div className="flex flex-col items-center gap-3 pt-4">
                            <FlipButton
                                onClick={() => setIsFlipped(!isFlipped)}
                                isFlipped={isFlipped}
                                label={t.words.flip}
                            />

                            <p className="text-primary-600 dark:text-primary-400 text-sm font-medium">
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
                .perspective-1000 {
                    perspective: 1000px;
                }

                .custom-scrollbar::-webkit-scrollbar {
                    width: 6px;
                }
                .custom-scrollbar::-webkit-scrollbar-track {
                    background: transparent;
                }
                .custom-scrollbar::-webkit-scrollbar-thumb {
                    background: rgb(30 95 140 / 0.3);
                    border-radius: 3px;
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
