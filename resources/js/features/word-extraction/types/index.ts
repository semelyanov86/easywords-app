export interface ExtractedWord {
    original: string;
    translation: string;
    language: string;
}

export interface ExtractFormData {
    image: File | null;
    language: string;
    target_language?: string;
}
