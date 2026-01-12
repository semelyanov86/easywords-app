import { dashboard, login } from '@/routes';
import { useTranslation } from '@/shared/i18n/useTranslation';
import { LanguageSwitcher } from '@/shared/ui/LanguageSwitcher';
import { colors } from '@/shared/ui/colors';
import type { SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';

interface NavigationProps {
    canRegister?: boolean;
}

export function Navigation({ canRegister = true }: NavigationProps) {
    const t = useTranslation();
    const { auth } = usePage<SharedData>().props;

    return (
        <header
            className={`sticky top-0 z-50 border-b border-[${colors.border.DEFAULT}] bg-[${colors.background.DEFAULT}]/95 backdrop-blur-sm dark:border-[${colors.border.dark}] dark:bg-[${colors.background.dark}]/95`}
        >
            <nav className="container mx-auto flex items-center justify-between px-4 py-4 lg:px-8">
                {/* Logo */}
                <Link href="/" className="flex items-center gap-2">
                    <img
                        src="/images/easywords-s.png"
                        alt="EasyWords"
                        className="h-8 w-auto"
                    />
                </Link>

                {/* Right side: Language + Auth Links */}
                <div className="flex items-center gap-4">
                    <LanguageSwitcher />

                    <div className="flex items-center gap-2">
                        {auth.user ? (
                            <Link
                                href={dashboard()}
                                className={`inline-flex items-center justify-center rounded-sm border border-transparent bg-[${colors.primary.DEFAULT}] px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-[${colors.primary.hover}] focus:ring-2 focus:outline-none focus:ring-[${colors.primary.DEFAULT}] focus:ring-offset-2 dark:bg-[${colors.primary.dark}] dark:hover:bg-[${colors.primary.darkHover}] dark:focus:ring-offset-[${colors.background.dark}]`}
                            >
                                {t.nav.dashboard}
                            </Link>
                        ) : (
                            <>
                                <Link
                                    href={login()}
                                    className={`inline-flex items-center justify-center rounded-sm border border-[${colors.border.DEFAULT_LOW_OPACITY}] px-4 py-2 text-sm font-medium text-[${colors.text.DEFAULT}] transition-colors hover:border-[${colors.border.darkHover}] focus:ring-2 focus:outline-none focus:ring-[${colors.text.DEFAULT}] focus:ring-offset-2 dark:border-[${colors.border.dark}] dark:text-[${colors.text.dark}] dark:hover:border-[${colors.border.darkLowOpacity}]`}
                                >
                                    {t.nav.login}
                                </Link>
                                {canRegister && (
                                    <Link
                                        href="/register"
                                        className={`inline-flex items-center justify-center rounded-sm bg-[${colors.primary.DEFAULT}] px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-[${colors.primary.hover}] focus:ring-2 focus:outline-none focus:ring-[${colors.primary.DEFAULT}] focus:ring-offset-2 dark:bg-[${colors.primary.dark}] dark:hover:bg-[${colors.primary.darkHover}] dark:focus:ring-offset-[${colors.background.dark}]`}
                                    >
                                        {t.nav.register}
                                    </Link>
                                )}
                            </>
                        )}
                    </div>
                </div>
            </nav>
        </header>
    );
}
