import {
    deleteMethod,
    markLearned,
    next,
    prev,
    toggleStarred,
    unlearned,
} from '@/routes/words';
import { router } from '@inertiajs/react';
import { useCallback } from 'react';

export function useWordStudy(wordId: number) {
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

    const handleGoToPrev = useCallback(() => {
        router.get(prev().url);
    }, []);

    const handleGoToNext = useCallback(() => {
        router.get(next().url);
    }, []);

    return {
        handleMarkLearned,
        handleMarkUnlearned,
        handleDelete,
        handleToggleStarred,
        handleGoToPrev,
        handleGoToNext,
    };
}
