import { WordData } from '@/features/word-search/types';
import { show } from '@/routes/words';
import { router } from '@inertiajs/react';
import { BookOpen, Calendar, Eye, Star } from 'lucide-react';

interface WordSearchCardProps {
    word: WordData;
    learnedText: string;
    viewsText: string;
}

export function WordSearchCard({ word, learnedText }: WordSearchCardProps) {
    const handleClick = (): void => {
        router.visit(show(word.id));
    };

    const formatDate = (dateString: string): string => {
        return new Date(dateString).toLocaleDateString('ru-RU', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
        });
    };

    return (
        <button
            type="button"
            onClick={handleClick}
            className="group relative w-full overflow-hidden rounded-2xl border-2 border-border bg-card p-6 text-left shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-primary hover:shadow-2xl"
        >
            {/* Animated background gradient */}
            <div className="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-secondary/5 opacity-0 transition-opacity duration-500 group-hover:opacity-100" />

            <div className="relative">
                <div className="flex items-start justify-between gap-4">
                    <div className="min-w-0 flex-1">
                        {/* Header with badges */}
                        <div className="mb-3 flex flex-wrap items-center gap-2">
                            <h3 className="text-2xl font-bold text-foreground transition-colors duration-300 group-hover:text-primary sm:text-3xl">
                                {word.original}
                            </h3>

                            <span className="inline-flex items-center gap-1 rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold tracking-wide text-primary uppercase">
                                <BookOpen className="h-3 w-3" />
                                {word.language}
                            </span>

                            {word.starred && (
                                <Star className="h-5 w-5 fill-yellow-400 text-yellow-500 drop-shadow" />
                            )}

                            {word.done_at && (
                                <span className="inline-flex items-center gap-1 rounded-full bg-gradient-to-r from-secondary/90 to-secondary px-3 py-1 text-xs font-semibold text-secondary-foreground shadow-sm">
                                    ✓ {learnedText}
                                </span>
                            )}
                        </div>

                        {/* Translation */}
                        <p className="text-lg font-medium text-card-foreground sm:text-xl">
                            {word.translated}
                        </p>
                    </div>

                    {/* Stats */}
                    <div className="flex flex-col items-end gap-3 text-sm">
                        <div className="flex items-center gap-1.5 rounded-full bg-muted px-3 py-1.5 text-muted-foreground transition-colors group-hover:bg-primary/10 group-hover:text-primary">
                            <Eye className="h-4 w-4" />
                            <span className="font-semibold">{word.views}</span>
                        </div>

                        <div className="flex items-center gap-1.5 text-xs text-muted-foreground">
                            <Calendar className="h-3.5 w-3.5" />
                            <span>{formatDate(word.created_at)}</span>
                        </div>
                    </div>
                </div>
            </div>

            {/* Hover indicator */}
            <div className="absolute bottom-0 left-0 h-1 w-0 bg-gradient-to-r from-primary to-secondary transition-all duration-500 group-hover:w-full" />
        </button>
    );
}
