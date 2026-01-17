import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    deleteMethod,
    markLearned,
    next,
    prev,
    share,
    toggleStarred,
} from '@/routes/words';
import { useTranslation } from '@/shared/i18n/useTranslation';
import { User } from '@/types';
import { AuthHeader } from '@/widgets/auth/AuthHeader';
import { Head, router, useForm } from '@inertiajs/react';
import { useCallback, useEffect, useState } from 'react';

interface WordData {
    id: number;
    original: string;
    translated: string;
    language: string;
    done_at: string | null;
    starred: boolean;
    views: number;
    from_sample: boolean;
    user_id: number;
    created_at: string;
    updated_at: string;
}

interface StudyMeta {
    total: number | null;
    next_id: number | null;
    prev_id: number | null;
    current_index: number | null;
}

interface WordStudyPageProps {
    word: WordData;
    user: User;
    meta: StudyMeta | null;
}

export default function WordStudyPage({
    word,
    user,
    meta,
}: WordStudyPageProps) {
    const t = useTranslation();
    const [isFlipped, setIsFlipped] = useState(false);
    const [isShareModalOpen, setIsShareModalOpen] = useState(false);
    const [selectedUserId, setSelectedUserId] = useState<string>('');

    const shareForm = useForm({
        user_id: '',
    });

    const handleMarkLearned = useCallback(() => {
        router.post(markLearned(word.id).url);
    }, [word.id]);

    const handleDelete = useCallback(() => {
        router.delete(deleteMethod(word.id));
    }, [word.id]);

    const handleToggleStarred = useCallback(() => {
        router.post(toggleStarred(word.id).url);
    }, [word.id]);

    const handleGoToPrev = useCallback(() => {
        if (meta?.prev_id) {
            router.get(prev().url);
        }
    }, [meta]);

    const handleGoToNext = useCallback(() => {
        if (meta?.next_id) {
            router.get(next().url);
        }
    }, [meta]);

    const handleShare = useCallback(() => {
        shareForm.setData('user_id', selectedUserId);
        shareForm.post(share(word.id).url, {
            onSuccess: () => {
                setIsShareModalOpen(false);
                setSelectedUserId('');
                shareForm.reset();
            },
        });
    }, [word.id, selectedUserId, shareForm]);

    // Keyboard shortcuts
    useEffect(() => {
        const handleKeyDown = (e: KeyboardEvent) => {
            // Don't trigger shortcuts when modal is open
            if (isShareModalOpen) return;

            switch (e.key) {
                case 'Enter':
                    e.preventDefault();
                    handleMarkLearned();
                    break;
                case ' ':
                    e.preventDefault();
                    setIsFlipped((prev) => !prev);
                    break;
                case 'Backspace':
                    e.preventDefault();
                    if (meta?.prev_id) {
                        handleGoToPrev();
                    }
                    break;
                case 'Delete':
                    e.preventDefault();
                    handleDelete();
                    break;
                case 'ArrowLeft':
                    e.preventDefault();
                    if (meta?.prev_id) {
                        handleGoToPrev();
                    }
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    if (meta?.next_id) {
                        handleGoToNext();
                    }
                    break;
            }
        };

        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [
        isShareModalOpen,
        meta,
        handleMarkLearned,
        handleDelete,
        handleGoToPrev,
        handleGoToNext,
    ]);

    const canGoPrev = meta?.prev_id !== null;
    const canGoNext = meta?.next_id !== null;

    return (
        <>
            <Head title={`${t.words.study_title} - ${word.original}`} />
            <div className="from-primary-50 to-secondary-50 min-h-screen bg-gradient-to-br via-white dark:from-neutral-900 dark:via-neutral-800 dark:to-neutral-900">
                <AuthHeader userName={user.name} />
                <div className="mx-auto max-w-5xl px-4 py-8 md:py-12">
                    {/* Header */}
                    <div className="mb-8 text-center">
                        <h1 className="text-primary-900 dark:text-primary-100 text-4xl font-bold tracking-tight">
                            {t.words.study_title}
                        </h1>
                        <p className="text-primary-700 dark:text-primary-300 mt-3 text-lg">
                            {t.words.study_subtitle}
                        </p>
                    </div>

                    {/* Flashcard */}
                    <div className="mx-auto max-w-2xl">
                        <div className="perspective-1000 relative">
                            <div
                                className={`relative min-h-[400px] cursor-pointer rounded-2xl bg-white p-8 shadow-2xl transition-all duration-500 ease-in-out dark:bg-neutral-800 ${
                                    isFlipped ? 'rotate-y-180' : ''
                                }`}
                                onClick={() => setIsFlipped(!isFlipped)}
                                onKeyDown={(e) => {
                                    if (e.key === 'Enter' || e.key === ' ') {
                                        setIsFlipped(!isFlipped);
                                    }
                                }}
                                role="button"
                                tabIndex={0}
                                aria-label={t.words.flip}
                            >
                                {/* Word ID (small, top) */}
                                <div className="absolute top-4 left-4 font-mono text-xs text-neutral-400">
                                    #{word.id}
                                </div>

                                {/* Progress indicator */}
                                {meta && meta.current_index && meta.total && (
                                    <div className="absolute top-4 right-4 text-sm font-semibold text-neutral-600 dark:text-neutral-400">
                                        {meta.current_index} / {meta.total}
                                    </div>
                                )}

                                {/* Word content */}
                                <div className="flex min-h-[300px] items-center justify-center text-center">
                                    <div className="space-y-4">
                                        <p className="text-primary-900 dark:text-primary-100 text-5xl font-bold">
                                            {isFlipped
                                                ? word.translated
                                                : word.original}
                                        </p>
                                        {!isFlipped && (
                                            <p className="text-primary-600 dark:text-primary-400 text-xl">
                                                {word.language}
                                            </p>
                                        )}
                                    </div>
                                </div>

                                {/* Card actions */}
                                <div className="absolute right-4 bottom-4 flex gap-2">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            setIsShareModalOpen(true);
                                        }}
                                        aria-label={t.words.share_word}
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="20"
                                            height="20"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            strokeWidth="2"
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            className="text-neutral-600 dark:text-neutral-400"
                                        >
                                            <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8" />
                                            <polyline points="16 6 12 2 8 6" />
                                            <line
                                                x1="12"
                                                x2="12"
                                                y1="2"
                                                y2="15"
                                            />
                                        </svg>
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            handleToggleStarred();
                                        }}
                                        aria-label={
                                            word.starred
                                                ? t.words.remove_from_favorites
                                                : t.words.add_to_favorites
                                        }
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="20"
                                            height="20"
                                            viewBox="0 0 24 24"
                                            fill={
                                                word.starred
                                                    ? 'currentColor'
                                                    : 'none'
                                            }
                                            stroke="currentColor"
                                            strokeWidth="2"
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            className={
                                                word.starred
                                                    ? 'text-secondary-500'
                                                    : 'text-neutral-600 dark:text-neutral-400'
                                            }
                                        >
                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                        </svg>
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            handleDelete();
                                        }}
                                        aria-label={t.words.delete}
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="20"
                                            height="20"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            strokeWidth="2"
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            className="text-red-600 dark:text-red-400"
                                        >
                                            <polyline points="3 6 5 6 21 6" />
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                        </svg>
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Action buttons */}
                    <div className="mt-8 flex flex-wrap justify-center gap-4">
                        <Button
                            size="lg"
                            className="hover:bg-secondary-600 bg-secondary text-white"
                            onClick={handleMarkLearned}
                        >
                            {t.words.mark_learned}
                        </Button>
                        <Button
                            size="lg"
                            variant="outline"
                            className="hover:bg-primary-50 hover:text-primary-700 border-primary text-primary"
                            onClick={() => setIsFlipped(!isFlipped)}
                        >
                            {t.words.flip}
                        </Button>
                        <Button
                            size="lg"
                            variant="outline"
                            onClick={handleGoToPrev}
                            disabled={!canGoPrev}
                        >
                            {t.words.previous}
                        </Button>
                        <Button
                            size="lg"
                            variant="outline"
                            onClick={handleGoToNext}
                            disabled={!canGoNext}
                        >
                            {t.words.next}
                        </Button>
                        <Button
                            size="lg"
                            variant="outline"
                            disabled
                            title={t.words.show_example}
                        >
                            {t.words.show_example}
                        </Button>
                    </div>

                    {/* Keyboard shortcuts */}
                    <div className="mt-12 rounded-lg bg-white/50 p-6 backdrop-blur-sm dark:bg-neutral-800/50">
                        <h3 className="text-primary-900 dark:text-primary-100 mb-4 text-lg font-semibold">
                            {t.words.keyboard_shortcuts}
                        </h3>
                        <p className="text-primary-700 dark:text-primary-300 mb-4 text-sm">
                            {t.words.shortcuts_description}
                        </p>
                        <div className="text-primary-600 dark:text-primary-400 grid gap-2 text-sm md:grid-cols-2">
                            <div className="flex items-center gap-2">
                                <kbd className="rounded border bg-neutral-100 px-2 py-1 font-mono text-xs dark:border-neutral-700 dark:bg-neutral-900">
                                    {t.words.shortcut_enter.split(' - ')[0]}
                                </kbd>
                                <span>
                                    {t.words.shortcut_enter.split(' - ')[1]}
                                </span>
                            </div>
                            <div className="flex items-center gap-2">
                                <kbd className="rounded border bg-neutral-100 px-2 py-1 font-mono text-xs dark:border-neutral-700 dark:bg-neutral-900">
                                    {t.words.shortcut_space.split(' - ')[0]}
                                </kbd>
                                <span>
                                    {t.words.shortcut_space.split(' - ')[1]}
                                </span>
                            </div>
                            <div className="flex items-center gap-2">
                                <kbd className="rounded border bg-neutral-100 px-2 py-1 font-mono text-xs dark:border-neutral-700 dark:bg-neutral-900">
                                    ←
                                </kbd>
                                <span>
                                    {
                                        t.words.shortcut_arrow_left.split(
                                            ' - ',
                                        )[1]
                                    }
                                </span>
                            </div>
                            <div className="flex items-center gap-2">
                                <kbd className="rounded border bg-neutral-100 px-2 py-1 font-mono text-xs dark:border-neutral-700 dark:bg-neutral-900">
                                    →
                                </kbd>
                                <span>
                                    {
                                        t.words.shortcut_arrow_right.split(
                                            ' - ',
                                        )[1]
                                    }
                                </span>
                            </div>
                            <div className="flex items-center gap-2">
                                <kbd className="rounded border bg-neutral-100 px-2 py-1 font-mono text-xs dark:border-neutral-700 dark:bg-neutral-900">
                                    Backspace
                                </kbd>
                                <span>
                                    {t.words.shortcut_backspace.split(' - ')[1]}
                                </span>
                            </div>
                            <div className="flex items-center gap-2">
                                <kbd className="rounded border bg-neutral-100 px-2 py-1 font-mono text-xs dark:border-neutral-700 dark:bg-neutral-900">
                                    Delete
                                </kbd>
                                <span>
                                    {t.words.shortcut_delete.split(' - ')[1]}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Share Modal */}
                <Dialog
                    open={isShareModalOpen}
                    onOpenChange={setIsShareModalOpen}
                >
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>
                                {t.words.share_modal_title}
                            </DialogTitle>
                            <DialogDescription>
                                {t.words.share_modal_subtitle}
                            </DialogDescription>
                        </DialogHeader>
                        <div className="space-y-4 py-4">
                            <div className="space-y-2">
                                <label
                                    htmlFor="user-select"
                                    className="text-primary-900 dark:text-primary-100 text-sm font-medium"
                                >
                                    Пользователь
                                </label>
                                <Select
                                    value={selectedUserId}
                                    onValueChange={setSelectedUserId}
                                >
                                    <SelectTrigger id="user-select">
                                        <SelectValue placeholder="Выберите пользователя" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {user.id === 1 && (
                                            <SelectItem value="2">
                                                Пользователь 2
                                            </SelectItem>
                                        )}
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <DialogFooter>
                            <Button
                                variant="outline"
                                onClick={() => setIsShareModalOpen(false)}
                            >
                                {t.words.close}
                            </Button>
                            <Button
                                onClick={handleShare}
                                disabled={
                                    !selectedUserId || shareForm.processing
                                }
                                className="hover:bg-secondary-600 bg-secondary text-white"
                            >
                                {shareForm.processing
                                    ? t.words.share
                                    : t.words.share}
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>
        </>
    );
}
