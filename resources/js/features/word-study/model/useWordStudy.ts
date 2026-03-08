import {
    deleteMethod,
    markLearned,
    next,
    prev,
    toggleStarred,
    unlearned,
} from '@/routes/words';
import { router } from '@inertiajs/react';
import { useCallback, useState } from 'react';

export function useWordStudy(wordId: number) {
    const [navigating, setNavigating] = useState(false);

    const handleMarkLearned = useCallback(() => {
        router.post(markLearned(wordId).url, {}, { preserveScroll: true });
    }, [wordId]);

    const handleMarkUnlearned = useCallback(() => {
        router.post(unlearned(wordId).url, {}, { preserveScroll: true });
    }, [wordId]);

    const handleDelete = useCallback(() => {
        if (confirm('Вы уверены, что хотите удалить это слово?')) {
            router.delete(deleteMethod(wordId));
        }
    }, [wordId]);

    const handleToggleStarred = useCallback(() => {
        router.post(toggleStarred(wordId).url, {}, { preserveScroll: true });
    }, [wordId]);

    const handleGoToPrev = useCallback(
        (language: string, reverse: boolean) => {
            if (navigating) return;
            setNavigating(true);
            router.get(
                prev({
                    query: {
                        language: language,
                        reverse: reverse,
                    },
                }).url,
                {},
                {
                    preserveScroll: true,
                    onFinish: () => setNavigating(false),
                },
            );
        },
        [navigating],
    );

    const handleGoToNext = useCallback(
        (language: string, reverse: boolean) => {
            if (navigating) return;
            setNavigating(true);
            router.get(
                next({
                    query: {
                        language: language,
                        reverse: reverse,
                    },
                }).url,
                {},
                {
                    preserveScroll: true,
                    onFinish: () => setNavigating(false),
                },
            );
        },
        [navigating],
    );

    return {
        handleMarkLearned,
        handleMarkUnlearned,
        handleDelete,
        handleToggleStarred,
        handleGoToPrev,
        handleGoToNext,
        navigating,
    };
}
