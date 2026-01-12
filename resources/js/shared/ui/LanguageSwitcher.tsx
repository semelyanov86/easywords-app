import { useLanguage, type Language } from '@/shared/i18n/LanguageContext';
import { colors } from '@/shared/ui/colors';

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
                    className={`inline-flex items-center gap-1.5 rounded-sm px-3 py-1.5 text-sm font-medium transition-colors ${
                        language === lang.code
                            ? `bg-[${colors.primary.DEFAULT}] text-white hover:bg-[${colors.primary.hover}] dark:bg-[${colors.primary.dark}] dark:hover:bg-[${colors.primary.darkHover}]`
                            : `border border-[${colors.border.DEFAULT_LOW_OPACITY}] text-[${colors.text.DEFAULT}] hover:border-[${colors.border.darkHover}] dark:border-[${colors.border.dark}] dark:text-[${colors.text.dark}] dark:hover:border-[${colors.border.darkLowOpacity}]`
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
