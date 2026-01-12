import type { Language } from './LanguageContext';
import { translations as de } from './de';
import { translations as en } from './en';
import { translations as ru } from './ru';
import type { Translations } from './types';

const translations = {
    en,
    ru,
    de,
} as const;

export function getTranslation(language: Language): Translations {
    const translation = translations[language];
    // The translations object already enforces type safety
    return translation;
}
