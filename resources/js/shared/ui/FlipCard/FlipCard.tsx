import { ReactNode } from 'react';

interface FlipCardProps {
    isFlipped: boolean;
    onFlip: () => void;
    frontContent: ReactNode;
    backContent: ReactNode;
    isLearned?: boolean;
    className?: string;
}

export function FlipCard({
    isFlipped,
    onFlip,
    frontContent,
    backContent,
    isLearned = false,
    className = '',
}: FlipCardProps) {
    return (
        <div className={`perspective-1000 relative ${className}`}>
            <div
                className={`flip-card-container relative min-h-[400px] cursor-pointer transition-transform duration-700 ease-in-out ${
                    isFlipped ? '[transform:rotateY(180deg)]' : ''
                }`}
                style={{ transformStyle: 'preserve-3d' }}
                onClick={onFlip}
                onKeyDown={(e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        onFlip();
                    }
                }}
                role="button"
                tabIndex={0}
                aria-label="Перевернуть карточку"
            >
                {/* Front side */}
                <div
                    className={`flip-card-face hover:shadow-3xl absolute inset-0 rounded-2xl p-8 shadow-2xl transition-all duration-300 ${
                        isLearned
                            ? 'bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20'
                            : 'bg-white dark:bg-neutral-800'
                    }`}
                    style={{
                        backfaceVisibility: 'hidden',
                        WebkitBackfaceVisibility: 'hidden',
                        transform: 'rotateY(0deg)',
                    }}
                >
                    {frontContent}
                </div>

                {/* Back side */}
                <div
                    className={`flip-card-face hover:shadow-3xl absolute inset-0 rounded-2xl p-8 shadow-2xl transition-all duration-300 ${
                        isLearned
                            ? 'bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20'
                            : 'bg-white dark:bg-neutral-800'
                    }`}
                    style={{
                        backfaceVisibility: 'hidden',
                        WebkitBackfaceVisibility: 'hidden',
                        transform: 'rotateY(180deg)',
                    }}
                >
                    {backContent}
                </div>
            </div>
        </div>
    );
}
