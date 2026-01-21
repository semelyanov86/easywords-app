interface ExampleCardProps {
    text: string;
    variant: 'original' | 'translated';
    index: number;
}

export function ExampleCard({ text, variant, index }: ExampleCardProps) {
    const isOriginal = variant === 'original';

    return (
        <div className="group relative overflow-hidden rounded-lg border border-border bg-card p-3 transition-all duration-300 hover:scale-[1.01] hover:shadow-md md:rounded-xl md:p-5">
            {/* Decorative number */}
            <div
                className={`absolute -top-1.5 -right-1.5 flex h-8 w-8 items-center justify-center rounded-full md:-top-2 md:-right-2 md:h-12 md:w-12 ${
                    isOriginal ? 'bg-primary/10' : 'bg-secondary/10'
                }`}
            >
                <span
                    className={`text-sm font-bold md:text-lg ${
                        isOriginal ? 'text-primary' : 'text-secondary'
                    }`}
                >
                    {index + 1}
                </span>
            </div>

            {/* Text content */}
            <p className="relative z-10 pr-6 text-sm leading-relaxed text-foreground md:pr-8 md:text-base">
                {text}
            </p>

            {/* Hover indicator */}
            <div
                className={`absolute bottom-0 left-0 h-0.5 w-0 transition-all duration-300 group-hover:w-full ${
                    isOriginal ? 'bg-primary' : 'bg-secondary'
                }`}
            />
        </div>
    );
}
