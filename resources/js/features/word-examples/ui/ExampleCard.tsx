interface ExampleCardProps {
    text: string;
    variant: 'original' | 'translated';
    index: number;
}

export function ExampleCard({ text, variant, index }: ExampleCardProps) {
    const isOriginal = variant === 'original';

    return (
        <div
            className={`group relative overflow-hidden rounded-xl border p-5 transition-all duration-300 hover:scale-[1.02] hover:shadow-lg ${
                isOriginal
                    ? 'border-primary-200 dark:border-primary-700 from-primary-50 dark:from-primary-950/40 dark:to-primary-900/20 bg-gradient-to-br to-white'
                    : 'border-secondary-200 dark:border-secondary-700 from-secondary-50 dark:from-secondary-950/40 dark:to-secondary-900/20 bg-gradient-to-br to-white'
            } `}
        >
            {/* Decorative number */}
            <div
                className={`absolute -top-4 -right-4 h-20 w-20 rounded-full opacity-10 ${isOriginal ? 'bg-primary-600' : 'bg-secondary-600'} `}
            >
                <span
                    className={`absolute bottom-2 left-4 text-4xl font-bold ${isOriginal ? 'text-primary-700 dark:text-primary-300' : 'text-secondary-700 dark:text-secondary-300'} `}
                >
                    {index + 1}
                </span>
            </div>

            {/* Text content */}
            <p
                className={`relative z-10 text-lg leading-relaxed ${
                    isOriginal
                        ? 'text-primary-900 dark:text-primary-100'
                        : 'text-secondary-900 dark:text-secondary-100'
                } `}
            >
                {text}
            </p>

            {/* Hover indicator */}
            <div
                className={`absolute bottom-0 left-0 h-1 w-0 transition-all duration-300 group-hover:w-full ${isOriginal ? 'bg-primary-500' : 'bg-secondary-500'} `}
            />
        </div>
    );
}
