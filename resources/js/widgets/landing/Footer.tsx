import { useTranslation } from '@/shared/i18n/useTranslation';
import { colors } from '@/shared/ui/colors';

export function Footer() {
    const t = useTranslation();
    const currentYear = new Date().getFullYear();

    return (
        <footer
            className={`border-t border-[${colors.border.DEFAULT}] bg-[${colors.accent.gradientStart}] py-12 dark:border-[${colors.border.dark}] dark:bg-[${colors.accent.cardDark}]`}
        >
            <div className="container mx-auto px-4 lg:px-8">
                <div className="mx-auto max-w-6xl text-center">
                    <div className="mb-6 flex items-center justify-center">
                        <img
                            src="/images/easywords-s.png"
                            alt="EasyWords Logo"
                            className="h-8 w-auto"
                        />
                    </div>
                    <p
                        className={`mb-6 text-[${colors.text.muted}] dark:text-[${colors.text.darkMuted}]`}
                    >
                        {t.footer.tagline}
                    </p>
                    <p
                        className={`text-sm text-[${colors.text.muted}] dark:text-[${colors.text.darkMuted}]`}
                    >
                        &copy; {currentYear} EasyWords. {t.footer.rights}
                    </p>
                </div>
            </div>
        </footer>
    );
}
