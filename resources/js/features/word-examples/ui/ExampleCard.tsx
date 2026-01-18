interface ExampleCardProps {
    text: string;
    variant: 'original' | 'translated';
    index: number;
}

export function ExampleCard({ text, variant, index }: ExampleCardProps) {
    const isOriginal = variant === 'original';

    return (
        <div
            className={`group relative overflow-hidden rounded-xl border p-5 transition-all duration-300 hover:scale-[1.01] hover:shadow-md ${
                isOriginal
                    ? 'border-primary-200 dark:border-primary-700 from-primary-50 dark:from-primary-950/40 dark:to-primary-900/20 bg-gradient-to-br to-white'
                    : 'border-secondary-200 dark:border-secondary-700 from-secondary-50 dark:from-secondary-950/40 dark:to-secondary-900/20 bg-gradient-to-br to-white'
            } `}
        >
            {/* Decorative number */}
            <div
                className={`absolute -top-2 -right-2 flex h-12 w-12 items-center justify-center rounded-full ${isOriginal ? 'bg-primary-100 dark:bg-primary-900/30' : 'bg-secondary-100 dark:bg-secondary-900/30'} `}
            >
                <span
                    className={`text-lg font-bold ${isOriginal ? 'text-primary-600 dark:text-primary-400' : 'text-secondary-600 dark:text-secondary-400'} `}
                >
                    {index + 1}
                </span>
            </div>

            {/* Text content */}
            <p
                className={`relative z-10 pr-8 text-base leading-relaxed ${
                    isOriginal
                        ? 'text-primary-900 dark:text-primary-100'
                        : 'text-secondary-900 dark:text-secondary-100'
                } `}
            >
                {text}
            </p>

            {/* Hover indicator */}
            <div
                className={`absolute bottom-0 left-0 h-0.5 w-0 transition-all duration-300 group-hover:w-full ${isOriginal ? 'bg-primary-500' : 'bg-secondary-500'} `}
            />
        </div>
    );
}
