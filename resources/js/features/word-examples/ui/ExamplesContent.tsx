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
        <div className="flex h-full min-h-[380px] flex-col space-y-3 md:min-h-[500px] md:space-y-5">
            {/* Header - компактнее на мобильных */}
            <div className="flex-shrink-0 space-y-1.5 text-center md:space-y-2">
                <div
                    className={`inline-flex items-center gap-1.5 rounded-full px-3 py-1 md:gap-2 md:px-4 md:py-1.5 ${
                        isOriginal
                            ? 'bg-primary-100 dark:bg-primary-900/40'
                            : 'bg-secondary-100 dark:bg-secondary-900/40'
                    } `}
                >
                    <span className="text-base md:text-xl">
                        {isOriginal ? '📖' : '🌐'}
                    </span>
                    <p
                        className={`text-[10px] font-semibold tracking-wider uppercase md:text-xs ${
                            isOriginal
                                ? 'text-primary-700 dark:text-primary-300'
                                : 'text-secondary-700 dark:text-secondary-300'
                        } `}
                    >
                        {title}
                    </p>
                </div>

                <h2 className="text-primary-900 dark:text-primary-100 text-xl font-bold tracking-tight md:text-3xl">
                    {wordText}
                </h2>
                <p
                    className={`text-xs font-medium tracking-wide uppercase md:text-sm ${
                        isOriginal
                            ? 'text-primary-600 dark:text-primary-400'
                            : 'text-secondary-600 dark:text-secondary-400'
                    } `}
                >
                    {language}
                </p>
            </div>

            {/* Examples list - меньше на мобильных */}
            <div className="custom-scrollbar max-h-[280px] flex-1 space-y-2 overflow-y-auto pr-1 md:max-h-[350px] md:space-y-3 md:pr-2">
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
