import { translate as translateRoute } from '@/routes/words';
import { useState } from 'react';

interface TranslationResponse {
    data: {
        type: string;
        id: string;
        attributes: {
            translation: string;
        };
    };
}

/**
 * Хук для получения перевода слова через ИИ.
 *
 * Использует веб-эндпоинт с cookie-based авторизацией.
 * Получает ответ в формате JSON:API.
 * Управляет состояниями загрузки и ошибок.
 */
export function useWordTranslation() {
    const [isTranslating, setIsTranslating] = useState(false);
    const [error, setError] = useState<string | null>(null);

    /**
     * Запрашивает перевод слова.
     *
     * @param word - Слово для перевода
     * @param language - Язык перевода
     * @returns Перевод слова или null при ошибке
     */
    const translate = async (
        word: string,
        language: string,
    ): Promise<string | null> => {
        setIsTranslating(true);
        setError(null);

        try {
            const response = await fetch(
                translateRoute.url({
                    query: { word, language },
                }),
                {
                    method: 'GET',
                    headers: {
                        Accept: 'application/vnd.api+json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                },
            );

            if (!response.ok) {
                throw new Error('Translation failed');
            }

            const data = (await response.json()) as TranslationResponse;
            setIsTranslating(false);
            return data.data.attributes.translation;
        } catch {
            setError('Не удалось получить перевод. Попробуйте снова.');
            setIsTranslating(false);
            return null;
        }
    };

    return { translate, isTranslating, error };
}
