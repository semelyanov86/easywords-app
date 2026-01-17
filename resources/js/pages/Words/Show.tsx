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
import { useWordStudy } from '@/features/word-study/model/useWordStudy';
import { WordStudyActions } from '@/features/word-study/ui/WordStudyActions';
import { WordStudyCard } from '@/features/word-study/ui/WordStudyCard';
import { WordStudyKeyboardShortcuts } from '@/features/word-study/ui/WordStudyKeyboardShortcuts';
import { share } from '@/routes/words';
import { useTranslation } from '@/shared/i18n/useTranslation';
import { FlipCard } from '@/shared/ui/FlipCard/FlipCard';
import { User } from '@/types';
import { AuthHeader } from '@/widgets/auth/AuthHeader';
import { Head, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';

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

    const {
        handleMarkLearned,
        handleMarkUnlearned,
        handleDelete,
        handleToggleStarred,
        handleGoToPrev,
        handleGoToNext,
    } = useWordStudy(word.id);

    const isLearned = word.done_at !== null;
    const canGoPrev = meta?.prev_id !== null && meta?.prev_id !== undefined;
    const canGoNext = meta?.next_id !== null && meta?.prev_id !== undefined;

    const handleShare = () => {
        shareForm.setData('user_id', selectedUserId);
        shareForm.post(share(word.id).url, {
            onSuccess: () => {
                setIsShareModalOpen(false);
                setSelectedUserId('');
                shareForm.reset();
            },
        });
    };

    // Keyboard shortcuts
    useEffect(() => {
        const handleKeyDown = (e: KeyboardEvent) => {
            if (isShareModalOpen) return;

            // Prevent shortcuts in input fields
            if (
                e.target instanceof HTMLInputElement ||
                e.target instanceof HTMLTextAreaElement
            ) {
                return;
            }

            switch (e.key) {
                case 'Enter':
                    e.preventDefault();
                    if (!isLearned) {
                        handleMarkLearned();
                    } else {
                        handleMarkUnlearned();
                    }
                    break;
                case ' ':
                    e.preventDefault();
                    setIsFlipped((prev) => !prev);
                    break;
                case 'Backspace':
                    e.preventDefault();
                    if (canGoPrev) {
                        handleGoToPrev();
                    }
                    break;
                case 'Delete':
                    e.preventDefault();
                    handleDelete();
                    break;
                case 'ArrowLeft':
                    e.preventDefault();
                    if (canGoPrev) {
                        handleGoToPrev();
                    }
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    if (canGoNext) {
                        handleGoToNext();
                    }
                    break;
            }
        };

        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [
        isShareModalOpen,
        isLearned,
        canGoPrev,
        canGoNext,
        handleMarkLearned,
        handleMarkUnlearned,
        handleDelete,
        handleGoToPrev,
        handleGoToNext,
    ]);

    // Word content components
    const frontContent = (
        <div className="flex min-h-[300px] items-center justify-center text-center">
            <div className="space-y-4">
                <p className="text-primary-900 dark:text-primary-100 text-6xl leading-tight font-bold">
                    {word.original}
                </p>
                <p className="text-primary-600 dark:text-primary-400 text-2xl font-medium">
                    {word.language.toUpperCase()}
                </p>
                {isLearned && (
                    <div className="mt-4 inline-flex items-center gap-2 rounded-full bg-green-100 px-4 py-2 text-sm font-semibold text-green-700 dark:bg-green-900/30 dark:text-green-400">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="16"
                            height="16"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            strokeWidth="3"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                        >
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                        Выучено
                    </div>
                )}
            </div>
        </div>
    );

    const backContent = (
        <div className="flex min-h-[300px] items-center justify-center text-center">
            <div className="space-y-4">
                <p className="text-primary-900 dark:text-primary-100 text-6xl leading-tight font-bold">
                    {word.translated}
                </p>
                <p className="text-primary-600 dark:text-primary-400 text-2xl font-medium">
                    RU
                </p>
                {isLearned && (
                    <div className="mt-4 inline-flex items-center gap-2 rounded-full bg-green-100 px-4 py-2 text-sm font-semibold text-green-700 dark:bg-green-900/30 dark:text-green-400">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="16"
                            height="16"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            strokeWidth="3"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                        >
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                        Выучено
                    </div>
                )}
            </div>
        </div>
    );

    return (
        <>
            <Head title={`${t.words.study_title} - ${word.original}`} />
            <div className="from-primary-50 to-secondary-50 min-h-screen bg-gradient-to-br via-white dark:from-neutral-900 dark:via-neutral-800 dark:to-neutral-900">
                <AuthHeader userName={user.name} />
                <div className="mx-auto max-w-5xl px-4 py-8 md:py-12">
                    {/* Header */}
                    <div className="mb-8 text-center">
                        <h1 className="text-primary-900 dark:text-primary-100 text-4xl font-bold tracking-tight">
                            📚 {t.words.study_title}
                        </h1>
                        <p className="text-primary-700 dark:text-primary-300 mt-3 text-lg">
                            {t.words.study_subtitle}
                        </p>
                    </div>

                    {/* Flashcard */}
                    <div className="mx-auto max-w-2xl">
                        <WordStudyCard
                            wordId={word.id}
                            currentIndex={meta?.current_index ?? null}
                            total={meta?.total ?? null}
                            onShare={() => setIsShareModalOpen(true)}
                            onToggleStar={handleToggleStarred}
                            onDelete={handleDelete}
                            isStarred={word.starred}
                        >
                            <FlipCard
                                isFlipped={isFlipped}
                                onFlip={() => setIsFlipped(!isFlipped)}
                                frontContent={frontContent}
                                backContent={backContent}
                                isLearned={isLearned}
                            />
                        </WordStudyCard>
                    </div>

                    {/* Action buttons */}
                    <WordStudyActions
                        isLearned={isLearned}
                        canGoPrev={canGoPrev}
                        canGoNext={canGoNext}
                        onMarkLearned={handleMarkLearned}
                        onMarkUnlearned={handleMarkUnlearned}
                        onFlip={() => setIsFlipped(!isFlipped)}
                        onPrev={handleGoToPrev}
                        onNext={handleGoToNext}
                        t={t}
                    />

                    {/* Keyboard shortcuts */}
                    <WordStudyKeyboardShortcuts t={t} />
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
                                    ? 'Отправка...'
                                    : t.words.share}
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>
        </>
    );
}
