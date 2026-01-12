import { createContext, useContext, useState, type ReactNode } from 'react';

export type Language = 'en' | 'ru' | 'de';

interface LanguageContextType {
    language: Language;
    setLanguage: (lang: Language) => void;
}

const LanguageContext = createContext<LanguageContextType | undefined>(
    undefined,
);

interface LanguageProviderProps {
    children: ReactNode;
}

export function LanguageProvider({ children }: LanguageProviderProps) {
    const [language, setLanguage] = useState<Language>(() => {
        // Try to get language from localStorage or use browser language
        if (typeof window !== 'undefined') {
            const saved = localStorage.getItem('language') as Language;
            if (saved && ['en', 'ru', 'de'].includes(saved)) {
                return saved;
            }
            const browserLang = navigator.language.slice(0, 2);
            return ['en', 'ru', 'de'].includes(browserLang)
                ? (browserLang as Language)
                : 'en';
        }
        return 'en';
    });

    const handleSetLanguage = (lang: Language) => {
        setLanguage(lang);
        if (typeof window !== 'undefined') {
            localStorage.setItem('language', lang);
        }
    };

    return (
        <LanguageContext.Provider
            value={{ language, setLanguage: handleSetLanguage }}
        >
            {children}
        </LanguageContext.Provider>
    );
}

export function useLanguage() {
    const context = useContext(LanguageContext);
    if (context === undefined) {
        throw new Error('useLanguage must be used within a LanguageProvider');
    }
    return context;
}
