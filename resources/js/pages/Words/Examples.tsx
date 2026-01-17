import { Button } from '@/components/ui/button';
import { useTranslation } from '@/shared/i18n/useTranslation';
import { FlipCard } from '@/shared/ui/FlipCard/FlipCard';
import { User } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { AuthHeader } from '@/widgets/auth/AuthHeader';

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
    const { url } = usePage();

    // Back to word page
    const backUrl = url.split('/').slice(0, -1).join('/');

    // Front content - original examples
    const frontContent = (
        <div className="flex h-full flex-col items-center justify-center space-y-6 p-6">
            <div className="text-center">
                <p className="text-primary-600 dark:text-primary-400 text-sm font-medium tracking-wider uppercase">
                    {t.words.examples_original}
                </p>
                <h2 className="text-primary-900 dark:text-primary-100 mt-2 text-3xl font-bold">
                    {word.original}
                </h2>
                <p className="text-primary-700 dark:text-primary-300 mt-1 text-lg">
                    {word.language}
                </p>
            </div>

            <div className="w-full space-y-4">
                {examples.original.map((example, index) => (
                    <div
                        key={index}
                        className="border-primary-200 dark:border-primary-800 bg-primary-50/50 dark:bg-primary-950/30 rounded-lg border p-4"
                    >
                        <p className="text-primary-900 dark:text-primary-100 text-lg leading-relaxed">
                            {example}
                        </p>
                    </div>
                ))}
            </div>
        </div>
    );

    // Back content - translated examples
    const backContent = (
        <div className="flex h-full flex-col items-center justify-center space-y-6 p-6">
            <div className="text-center">
                <p className="text-secondary-600 dark:text-secondary-400 text-sm font-medium tracking-wider uppercase">
                    {t.words.examples_translated}
                </p>
                <h2 className="text-primary-900 dark:text-primary-100 mt-2 text-3xl font-bold">
                    {word.translated}
                </h2>
                <p className="text-primary-700 dark:text-primary-300 mt-1 text-lg">
                    RU
                </p>
            </div>

            <div className="w-full space-y-4">
                {examples.translated.map((example, index) => (
                    <div
                        key={index}
                        className="border-secondary-200 dark:border-secondary-800 bg-secondary-50/50 dark:bg-secondary-950/30 rounded-lg border p-4"
                    >
                        <p className="text-secondary-900 dark:text-secondary-100 text-lg leading-relaxed">
                            {example}
                        </p>
                    </div>
                ))}
            </div>
        </div>
    );

    return (
        <>
            <Head title={`${t.words.examples_title} - ${word.original}`} />
            <div className="from-primary-50 to-secondary-50 min-h-screen bg-gradient-to-br via-white dark:from-neutral-900 dark:via-neutral-800 dark:to-neutral-900">
                <AuthHeader userName={user.name} />
                {/* Header */}
                <header className="border-primary-200 dark:border-primary-800 border-b bg-white/80 backdrop-blur-sm dark:bg-neutral-900/80">
                    <div className="mx-auto max-w-5xl px-4 py-4">
                        <div className="flex items-center justify-between">
                            <h1 className="text-primary-900 dark:text-primary-100 text-2xl font-bold">
                                💡 {t.words.examples_title}
                            </h1>
                            <div className="text-primary-700 dark:text-primary-300 text-sm">
                                {user.name}
                            </div>
                        </div>
                    </div>
                </header>

                {/* Main content */}
                <main className="mx-auto max-w-5xl px-4 py-8 md:py-12">
                    {/* Back button */}
                    <div className="mb-8">
                        <Button
                            variant="outline"
                            onClick={() => (window.location.href = backUrl)}
                            className="hover:bg-primary-50 hover:text-primary-700 border-primary text-primary"
                        >
                            ← {t.words.back_to_word}
                        </Button>
                    </div>

                    {/* Flashcard with examples */}
                    <div className="mx-auto max-w-3xl">
                        <FlipCard
                            isFlipped={isFlipped}
                            onFlip={() => setIsFlipped(!isFlipped)}
                            frontContent={frontContent}
                            backContent={backContent}
                            isLearned={false}
                        />
                    </div>

                    {/* Flip button */}
                    <div className="mt-8 flex justify-center">
                        <Button
                            size="lg"
                            onClick={() => setIsFlipped(!isFlipped)}
                            className="hover:bg-primary-600 bg-primary text-white transition-all duration-200 hover:scale-105 hover:shadow-lg"
                        >
                            🔄 {t.words.flip}
                        </Button>
                    </div>
                </main>
            </div>
        </>
    );
}
