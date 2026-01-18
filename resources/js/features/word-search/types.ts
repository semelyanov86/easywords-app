export interface WordData {
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
