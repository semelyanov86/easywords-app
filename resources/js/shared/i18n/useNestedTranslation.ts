import { useLanguage } from './LanguageContext';
import { getTranslation } from './translations';

/**
 * Получает переведенные строки для вложенного объекта переводов
 *
 * @param translationPaths - Объект с путями к переводам (например: { title: 'profile.title', ... })
 * @returns Объект с фактическими переведенными строками
 */
export function useNestedTranslation<T extends Record<string, string>>(
    translationPaths: T,
): Record<string, string> {
    const { language } = useLanguage();
    const translations = getTranslation(language);

    const result: Record<string, string> = {};

    for (const [key, path] of Object.entries(translationPaths)) {
        // Путь может быть разделен точкой, например: 'common.done' или 'profile.title'
        const segments = path.split('.');
        let value: unknown = translations;

        for (const segment of segments) {
            value = (value as Record<string, unknown>)?.[segment];
        }

        result[key] = (value as string) || path; // Fallback к пути перевода
    }

    return result as Record<string, string>;
}
