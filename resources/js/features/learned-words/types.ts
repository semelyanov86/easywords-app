import { WordData } from '@/features/word-search';
import { PaginatedResponse } from '@/shared/types/pagination';

export type LearnedWordsResponse = PaginatedResponse<WordData>;
