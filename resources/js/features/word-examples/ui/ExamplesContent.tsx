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
            {/* Header */}
            <div className="flex-shrink-0 space-y-1.5 text-center md:space-y-2">
                <div
                    className={`inline-flex items-center gap-1.5 rounded-full px-3 py-1 md:gap-2 md:px-4 md:py-1.5 ${
                        isOriginal
                            ? 'bg-primary/10 text-primary'
                            : 'bg-secondary/10 text-secondary'
                    }`}
                >
                    <span className="text-base md:text-xl">
                        {isOriginal ? '📖' : '🌐'}
                    </span>
                    <p className="text-[10px] font-semibold tracking-wider uppercase md:text-xs">
                        {title}
                    </p>
                </div>

                <h2 className="text-xl font-bold tracking-tight text-foreground md:text-3xl">
                    {wordText}
                </h2>
                <p
                    className={`text-xs font-medium tracking-wide uppercase md:text-sm ${
                        isOriginal ? 'text-primary' : 'text-secondary'
                    }`}
                >
                    {language}
                </p>
            </div>

            {/* Examples list */}
            <div className="max-h-[280px] flex-1 scrollbar-thin scrollbar-thumb-primary/30 scrollbar-track-transparent space-y-2 overflow-y-auto pr-1 hover:scrollbar-thumb-primary/50 md:max-h-[350px] md:space-y-3 md:pr-2">
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
