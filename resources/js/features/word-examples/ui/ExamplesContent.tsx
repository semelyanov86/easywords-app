import { ExampleCard } from './ExampleCard';

interface ExamplesContentProps {
    examples: string[];
    title: string;
    wordText: string;
    language: string;
    variant: 'original' | 'translated';
}

export function ExamplesContent({
    examples,
    title,
    wordText,
    language,
    variant,
}: ExamplesContentProps) {
    const isOriginal = variant === 'original';

    return (
        <div className="flex h-full flex-col justify-center space-y-8 p-8">
            {/* Header */}
            <div className="space-y-3 text-center">
                <div
                    className={`inline-flex items-center gap-2 rounded-full px-4 py-2 ${
                        isOriginal
                            ? 'bg-primary-100 dark:bg-primary-900/40'
                            : 'bg-secondary-100 dark:bg-secondary-900/40'
                    } `}
                >
                    <span className="text-2xl">{isOriginal ? '📖' : '🌐'}</span>
                    <p
                        className={`text-sm font-semibold tracking-wider uppercase ${
                            isOriginal
                                ? 'text-primary-700 dark:text-primary-300'
                                : 'text-secondary-700 dark:text-secondary-300'
                        } `}
                    >
                        {title}
                    </p>
                </div>

                <h2 className="text-primary-900 dark:text-primary-100 text-4xl font-bold tracking-tight">
                    {wordText}
                </h2>
                <p
                    className={`text-base font-medium tracking-wide uppercase ${
                        isOriginal
                            ? 'text-primary-600 dark:text-primary-400'
                            : 'text-secondary-600 dark:text-secondary-400'
                    } `}
                >
                    {language}
                </p>
            </div>

            {/* Examples list */}
            <div className="custom-scrollbar max-h-[500px] space-y-4 overflow-y-auto pr-2">
                {examples.map((example, index) => (
                    <ExampleCard
                        key={index}
                        text={example}
                        variant={variant}
                        index={index}
                    />
                ))}
            </div>
        </div>
    );
}
