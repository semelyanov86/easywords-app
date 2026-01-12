import { home } from '@/routes';
import { LanguageSwitcher } from '@/shared/ui/LanguageSwitcher';
import { colors } from '@/shared/ui/colors';
import { Link } from '@inertiajs/react';

/**
 * Navigation component for authentication pages.
 * Provides consistent header with logo, language switcher, and navigation links.
 */
export function AuthNavigation() {
    return (
        <header
            className={`sticky top-0 z-50 border-b border-[${colors.border.DEFAULT}] bg-[${colors.background.DEFAULT}]/95 backdrop-blur-sm dark:border-[${colors.border.dark}] dark:bg-[${colors.background.dark}]/95`}
        >
            <nav className="container mx-auto flex items-center justify-between px-4 py-4 lg:px-8">
                {/* Logo with EasyWords text */}
                <Link href={home()} className="flex items-center gap-3">
                    <img
                        src="/images/easywords-s.png"
                        alt="EasyWords"
                        className="h-8 w-auto"
                    />
                    <span className="text-xl font-semibold text-[var(--foreground)] dark:text-white">
                        EasyWords
                    </span>
                </Link>

                {/* Right side: Language Switcher */}
                <div className="flex items-center gap-4">
                    <LanguageSwitcher />
                </div>
            </nav>
        </header>
    );
}
