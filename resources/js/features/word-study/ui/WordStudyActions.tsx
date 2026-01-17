import { Button } from '@/components/ui/button';
import { TranslationStructure } from '@/shared/i18n/types';

interface WordStudyActionsProps {
    isLearned: boolean;
    canGoPrev: boolean;
    canGoNext: boolean;
    onMarkLearned: () => void;
    onMarkUnlearned: () => void;
    onFlip: () => void;
    onPrev: () => void;
    onNext: () => void;
    t: TranslationStructure;
}

export function WordStudyActions({
    isLearned,
    canGoPrev,
    canGoNext,
    onMarkLearned,
    onMarkUnlearned,
    onFlip,
    onPrev,
    onNext,
    t,
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
                disabled={!canGoNext}
                className="transition-all duration-200 hover:scale-105 hover:shadow-md disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:scale-100"
            >
                {t.words.next} →
            </Button>

            <Button
                size="lg"
                variant="outline"
                disabled
                title={t.words.show_example}
                className="cursor-not-allowed opacity-40"
            >
                💡 {t.words.show_example}
            </Button>
        </div>
    );
}
