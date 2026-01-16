import { useLanguage } from './LanguageContext';

export function useStatisticsTranslation<T extends Record<string, string>>(
    translationsMap: Record<string, T>,
): T {
    const { language } = useLanguage();

    const translations = translationsMap[language];

    if (!translations) {
        console.warn(`Translation not found for language: ${language}`);
        return translationsMap['en'] as T; // Fallback to English
    }

    return translations;
}
