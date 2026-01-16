export interface WordData {
    id: number;
    original: string;
    translated: string;
    views: number;
}

export interface WordStatisticsData {
    total_words: number;
    starred_words: number;
    not_done_words: number;
    done_words: number;
    total_views: number;
    total_users: number;
    top_viewed_words: WordData[];
    words_added_today: WordData[];
    words_updated_today: number;
    words_updated_this_month: number;
    progress_today_percent: number;
    streak_days: number;
}

export interface StatCardData {
    iconName: 'users' | 'book-open' | 'eye' | 'zap' | 'trending-up';
    colorScheme: 'primary' | 'secondary' | 'accent';
    title: string;
    value: number | string;
    description: string;
}
