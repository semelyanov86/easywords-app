import { useLanguage } from './LanguageContext';
import { getTranslation } from './translations';

export function useTranslation() {
    const { language } = useLanguage();
    return getTranslation(language);
}
