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

            <div className="min-h-screen bg-background">
                <AuthHeader userName={user.name} />

                {/* Page header */}
                <header className="border-b border-border bg-card/80 backdrop-blur-md">
                    <div className="mx-auto max-w-6xl px-3 py-3 md:px-4 md:py-4">
                        <div className="flex items-center gap-2 md:gap-4">
                            <Button
                                variant="outline"
                                onClick={handleBack}
                                size="sm"
                                className="group md:size-default transition-all duration-200 hover:-translate-x-1 hover:shadow-md"
                            >
                                <span className="inline-block transition-transform group-hover:-translate-x-1">
                                    ←
                                </span>
                                <span className="ml-1 hidden sm:inline md:ml-2">
                                    {t.words.back_to_word}
                                </span>
                            </Button>

                            <div className="flex items-center gap-1.5 md:gap-2">
                                <span className="text-lg md:text-2xl">💡</span>
                                <h1 className="text-base font-bold text-foreground md:text-xl">
                                    {t.words.examples_title}
                                </h1>
                            </div>
                        </div>
                    </div>
                </header>

                {/* Main content */}
                <main className="mx-auto max-w-6xl px-3 py-4 md:px-4 md:py-8">
                    <div className="mx-auto max-w-4xl space-y-4 md:space-y-8">
                        {/* Card */}
                        <div className="relative min-h-[420px] md:min-h-[550px]">
                            {/* Front side - Original */}
                            <div
                                className={`rounded-xl border border-border bg-card p-4 shadow-2xl transition-all duration-500 md:rounded-2xl md:p-8 ${isFlipped ? 'pointer-events-none absolute inset-0 scale-95 opacity-0' : 'scale-100 opacity-100'}`}
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
                                className={`rounded-xl border border-border bg-card p-4 shadow-2xl transition-all duration-500 md:rounded-2xl md:p-8 ${!isFlipped ? 'pointer-events-none absolute inset-0 scale-95 opacity-0' : 'scale-100 opacity-100'}`}
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

                        {/* Flip button */}
                        <div className="flex flex-col items-center gap-2 md:gap-3">
                            <FlipButton
                                onClick={() => setIsFlipped(!isFlipped)}
                                isFlipped={isFlipped}
                                label={t.words.flip}
                            />

                            <p className="text-xs font-medium text-muted-foreground md:text-sm">
                                {isFlipped
                                    ? '👆 Вернуться к оригиналу'
                                    : '👆 Показать перевод'}
                            </p>
                        </div>
                    </div>
                </main>
            </div>
        </>
    );
}
