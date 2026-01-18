import { Button } from '@/components/ui/button';
import { examples } from '@/routes/words';
import { TranslationStructure } from '@/shared/i18n/types';
import { Link } from '@inertiajs/react';

interface WordStudyActionsProps {
    isLearned: boolean;
    canGoPrev: boolean;
    onMarkLearned: () => void;
    onMarkUnlearned: () => void;
    onFlip: () => void;
    onPrev: () => void;
    onNext: () => void;
    t: TranslationStructure;
    wordId: number;
    hasPremium: boolean;
}

export function WordStudyActions({
    isLearned,
    canGoPrev,
    onMarkLearned,
    onMarkUnlearned,
    onFlip,
    onPrev,
    onNext,
    t,
    wordId,
    hasPremium,
}: WordStudyActionsProps) {
    return (
        <div className="mt-8 flex flex-wrap justify-center gap-4">
            {!isLearned ? (
                <Button
                    size="lg"
                    className="hover:bg-secondary-600 bg-secondary text-white transition-all duration-200 hover:scale-105 hover:shadow-lg"
                    onClick={onMarkLearned}
                >
                    ✓ {t.words.mark_learned}
                </Button>
            ) : (
                <Button
                    size="lg"
                    className="bg-green-600 text-white transition-all duration-200 hover:scale-105 hover:bg-green-700 hover:shadow-lg"
                    onClick={onMarkUnlearned}
                >
                    ↺ Забыть слово
                </Button>
            )}

            <Button
                size="lg"
                variant="outline"
                className="hover:bg-primary-50 hover:text-primary-700 border-primary text-primary transition-all duration-200 hover:scale-105 hover:shadow-md"
                onClick={onFlip}
            >
                🔄 {t.words.flip}
            </Button>

            <Button
                size="lg"
                variant="outline"
                onClick={onPrev}
                disabled={!canGoPrev}
                className="transition-all duration-200 hover:scale-105 hover:shadow-md disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:scale-100"
            >
                ← {t.words.previous}
            </Button>

            <Button
                size="lg"
                variant="outline"
                onClick={onNext}
                className="transition-all duration-200 hover:scale-105 hover:shadow-md disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:scale-100"
            >
                {t.words.next} →
            </Button>

            {hasPremium ? (
                <Link
                    href={examples(wordId).url}
                    className="group inline-block"
                >
                    <Button
                        size="lg"
                        variant="outline"
                        className="border-secondary-300 text-secondary-700 dark:border-secondary-700 dark:text-secondary-300 hover:bg-secondary-50 hover:border-secondary-400 hover:text-secondary-800 dark:hover:bg-secondary-950/40 dark:hover:border-secondary-600 relative overflow-hidden transition-all duration-200 hover:scale-105 hover:shadow-lg"
                    >
                        {/* Animated shine effect */}
                        <div className="absolute inset-0 translate-x-[-100%] bg-gradient-to-r from-transparent via-white/30 to-transparent transition-transform duration-700 group-hover:translate-x-[100%]" />

                        <span className="relative z-10 inline-flex items-center gap-2">
                            <span className="text-xl transition-transform group-hover:scale-110">
                                💡
                            </span>
                            {t.words.show_example}
                        </span>
                    </Button>
                </Link>
            ) : (
                <div className="group relative">
                    <Button
                        size="lg"
                        variant="outline"
                        disabled
                        className="cursor-not-allowed opacity-50"
                    >
                        <span className="inline-flex items-center gap-2">
                            <span className="text-xl grayscale">💡</span>
                            {t.words.show_example}
                        </span>
                    </Button>

                    {/* Tooltip on hover */}
                    <div className="absolute bottom-full left-1/2 mb-2 hidden -translate-x-1/2 group-hover:block">
                        <div className="rounded-lg bg-neutral-900 px-3 py-2 text-sm whitespace-nowrap text-white shadow-lg dark:bg-neutral-700">
                            {t.words.premium_required}
                            <div className="absolute top-full left-1/2 -mt-1 -translate-x-1/2 border-4 border-transparent border-t-neutral-900 dark:border-t-neutral-700" />
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
