import { useLanguage, type Language } from '@/shared/i18n/LanguageContext';

const languages: { code: Language; label: string; flag: string }[] = [
    { code: 'en', label: 'English', flag: '🇬🇧' },
    { code: 'ru', label: 'Русский', flag: '🇷🇺' },
    { code: 'de', label: 'Deutsch', flag: '🇩🇪' },
];

export function LanguageSwitcher() {
    const { language, setLanguage } = useLanguage();

    return (
        <div className="flex items-center gap-2">
            {languages.map((lang) => (
                <button
                    key={lang.code}
                    type="button"
                    onClick={() => setLanguage(lang.code)}
                    className={`inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium transition-colors ${
                        language === lang.code
                            ? 'bg-primary text-primary-foreground hover:bg-primary/90'
                            : 'border border-border bg-background text-foreground hover:bg-accent hover:text-accent-foreground'
                    }`}
                    aria-label={`Switch to ${lang.label}`}
                >
                    <span className="text-base" aria-hidden="true">
                        {lang.flag}
                    </span>
                    <span className="hidden sm:inline">{lang.label}</span>
                </button>
            ))}
        </div>
    );
}
