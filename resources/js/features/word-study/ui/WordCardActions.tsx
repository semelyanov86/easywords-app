import { Button } from '@/components/ui/button';

interface WordCardActionsProps {
    onShare: (e: React.MouseEvent) => void;
    onToggleStar: (e: React.MouseEvent) => void;
    onDelete: (e: React.MouseEvent) => void;
    isStarred: boolean;
}

export function WordCardActions({
    onShare,
    onToggleStar,
    onDelete,
    isStarred,
}: WordCardActionsProps) {
    return (
        <div className="absolute right-4 bottom-4 flex gap-2">
            <Button
                variant="ghost"
                size="icon"
                onClick={onShare}
                className="bg-white/80 transition-all duration-200 hover:scale-110 hover:bg-white dark:bg-neutral-700/80 dark:hover:bg-neutral-700"
                aria-label="Поделиться словом"
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
                    className="text-neutral-600 dark:text-neutral-300"
                >
                    <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8" />
                    <polyline points="16 6 12 2 8 6" />
                    <line x1="12" x2="12" y1="2" y2="15" />
                </svg>
            </Button>
            <Button
                variant="ghost"
                size="icon"
                onClick={onToggleStar}
                className="bg-white/80 transition-all duration-200 hover:scale-110 hover:bg-white dark:bg-neutral-700/80 dark:hover:bg-neutral-700"
                aria-label={
                    isStarred ? 'Убрать из избранного' : 'Добавить в избранное'
                }
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill={isStarred ? 'currentColor' : 'none'}
                    stroke="currentColor"
                    strokeWidth="2"
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    className={`transition-colors duration-200 ${
                        isStarred
                            ? 'text-yellow-500'
                            : 'text-neutral-600 dark:text-neutral-300'
                    }`}
                >
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                </svg>
            </Button>
            <Button
                variant="ghost"
                size="icon"
                onClick={onDelete}
                className="bg-white/80 transition-all duration-200 hover:scale-110 hover:bg-red-50 dark:bg-neutral-700/80 dark:hover:bg-red-900/30"
                aria-label="Удалить"
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
    );
}
