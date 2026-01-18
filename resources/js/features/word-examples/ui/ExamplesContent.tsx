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
        <div className="flex h-full min-h-[400px] flex-col space-y-4 overflow-hidden">
            {/* Header */}
            <div className="flex-shrink-0 space-y-2 text-center">
                <div
                    className={`inline-flex items-center gap-2 rounded-full px-4 py-1.5 ${
                        isOriginal
                            ? 'bg-primary-100 dark:bg-primary-900/40'
                            : 'bg-secondary-100 dark:bg-secondary-900/40'
                    } `}
                >
                    <span className="text-xl">{isOriginal ? '📖' : '🌐'}</span>
                    <p
                        className={`text-xs font-semibold tracking-wider uppercase ${
                            isOriginal
                                ? 'text-primary-700 dark:text-primary-300'
                                : 'text-secondary-700 dark:text-secondary-300'
                        } `}
                    >
                        {title}
                    </p>
                </div>

                <h2 className="text-primary-900 dark:text-primary-100 text-3xl font-bold tracking-tight">
                    {wordText}
                </h2>
                <p
                    className={`text-sm font-medium tracking-wide uppercase ${
                        isOriginal
                            ? 'text-primary-600 dark:text-primary-400'
                            : 'text-secondary-600 dark:text-secondary-400'
                    } `}
                >
                    {language}
                </p>
            </div>

            {/* Examples list - с возможностью прокрутки если не влезает */}
            <div className="custom-scrollbar flex-1 space-y-3 overflow-y-auto pr-2">
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
