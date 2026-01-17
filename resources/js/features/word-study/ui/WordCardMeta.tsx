interface WordCardMetaProps {
    wordId: number;
    currentIndex: number | null;
    total: number | null;
}

export function WordCardMeta({
    wordId,
    currentIndex,
    total,
}: WordCardMetaProps) {
    return (
        <>
            {/* Word ID (top left) */}
            <div className="absolute top-4 left-4 font-mono text-xs text-neutral-500 dark:text-neutral-400">
                #{wordId}
            </div>

            {/* Progress indicator (top right) */}
            {currentIndex && total && (
                <div className="absolute top-4 right-4 text-sm font-semibold text-neutral-600 dark:text-neutral-300">
                    {currentIndex} / {total}
                </div>
            )}
        </>
    );
}
