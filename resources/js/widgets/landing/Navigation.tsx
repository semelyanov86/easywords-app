import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useInitials } from '@/hooks/use-initials';
import { dashboard, login } from '@/routes';
import { useLanguage, type Language } from '@/shared/i18n/LanguageContext';
import { useTranslation } from '@/shared/i18n/useTranslation';
import { LanguageSwitcher } from '@/shared/ui/LanguageSwitcher';
import { colors } from '@/shared/ui/colors';
import type { SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { ChevronDown, Languages, LogOut, User } from 'lucide-react';

interface NavigationProps {
    canRegister?: boolean;
}

export function Navigation({ canRegister = true }: NavigationProps) {
    const t = useTranslation();
    const { language, setLanguage } = useLanguage();
    const getInitials = useInitials();
    const { auth } = usePage<SharedData>().props;

    const languages: { code: Language; label: string; flag: string }[] = [
        { code: 'en', label: 'English', flag: '🇬🇧' },
        { code: 'ru', label: 'Русский', flag: '🇷🇺' },
        { code: 'de', label: 'Deutsch', flag: '🇩🇪' },
    ];

    return (
        <header
            className={`sticky top-0 z-50 border-b border-[${colors.border.DEFAULT}] bg-[${colors.background.DEFAULT}]/95 backdrop-blur-sm dark:border-[${colors.border.dark}] dark:bg-[${colors.background.dark}]/95`}
        >
            <nav className="container mx-auto flex items-center justify-between px-4 py-4 lg:px-8">
                <div className="flex items-center gap-3">
                    <Link href="/" className="flex items-center gap-3">
                        <img
                            src="/images/easywords-s.png"
                            alt="EasyWords"
                            className="h-8 w-auto"
                        />
                        <span className="text-xl font-semibold text-[var(--foreground)] dark:text-white">
                            EasyWords
                        </span>
                    </Link>
                </div>

                <div className="flex items-center gap-2 md:gap-4">
                    <div className="hidden lg:block">
                        <LanguageSwitcher />
                    </div>

                    <div className="lg:hidden">
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    className="gap-2"
                                >
                                    <Languages className="h-4 w-4" />
                                    <ChevronDown className="h-4 w-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                {languages.map((lang) => (
                                    <button
                                        key={lang.code}
                                        type="button"
                                        onClick={() => setLanguage(lang.code)}
                                        className={`flex w-full items-center gap-2 px-2 py-2 text-sm ${
                                            language === lang.code
                                                ? 'bg-primary/10 text-primary'
                                                : ''
                                        }`}
                                    >
                                        <span className="text-lg">
                                            {lang.flag}
                                        </span>
                                        <span>{lang.label}</span>
                                    </button>
                                ))}
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>

                    {auth.user ? (
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button
                                    variant="ghost"
                                    className="size-10 rounded-full p-1"
                                >
                                    <Avatar className="size-8 overflow-hidden rounded-full bg-neutral-200">
                                        <AvatarFallback className="bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                            {getInitials(auth.user.name)}
                                        </AvatarFallback>
                                    </Avatar>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent className="w-56" align="end">
                                <DropdownMenuItem asChild>
                                    <Link
                                        href={dashboard()}
                                        className="cursor-pointer"
                                    >
                                        <User className="mr-2 h-4 w-4" />
                                        {t.nav.dashboard}
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuItem asChild>
                                    <a
                                        href="/logout"
                                        className="cursor-pointer"
                                    >
                                        <LogOut className="mr-2 h-4 w-4" />
                                        {t.nav.logout}
                                    </a>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    ) : (
                        <div className="flex items-center gap-2">
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
                        </div>
                    )}
                </div>
            </nav>
        </header>
    );
}
