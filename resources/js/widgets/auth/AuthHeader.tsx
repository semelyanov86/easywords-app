import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { useInitials } from '@/hooks/use-initials';
import { dashboard } from '@/routes';
import { dashboardTranslations } from '@/shared/i18n/dashboard';
import { useLanguage, type Language } from '@/shared/i18n/LanguageContext';
import { useDashboardTranslation } from '@/shared/i18n/useDashboardTranslation';
import AppLogoIcon from '@/shared/ui/AppLogoIcon';
import { LanguageSwitcher } from '@/shared/ui/LanguageSwitcher';
import { router } from '@inertiajs/react';
import { LogOut, Plus, Settings, User } from 'lucide-react';

interface AuthHeaderProps {
    userName: string;
}

export function AuthHeader({ userName }: AuthHeaderProps) {
    const getInitials = useInitials();
    const { language, setLanguage } = useLanguage();
    const t = useDashboardTranslation(dashboardTranslations);

    const languages: { code: Language; label: string; flag: string }[] = [
        { code: 'en', label: 'English', flag: '🇬🇧' },
        { code: 'ru', label: 'Русский', flag: '🇷🇺' },
        { code: 'de', label: 'Deutsch', flag: '🇩🇪' },
    ];

    const handleLogout = () => {
        router.post('/logout');
    };

    return (
        <header className="border-b border-sidebar-border/80">
            <div className="mx-auto flex h-16 items-center justify-between px-4 md:max-w-7xl">
                <div className="flex items-center space-x-4 md:space-x-6">
                    <a
                        href={dashboard().url}
                        className="flex items-center gap-3"
                    >
                        <AppLogoIcon />
                        <span className="text-xl font-semibold text-[var(--foreground)] md:block dark:text-white">
                            EasyWords
                        </span>
                    </a>

                    <div className="hidden md:block">
                        <LanguageSwitcher />
                    </div>

                    <TooltipProvider>
                        <Tooltip>
                            <TooltipTrigger asChild>
                                <Button
                                    variant="default"
                                    className="hover:bg-primary-600 bg-primary p-2 md:hidden"
                                >
                                    <Plus className="h-4 w-4" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>{t.add_word}</p>
                            </TooltipContent>
                        </Tooltip>
                    </TooltipProvider>

                    <TooltipProvider>
                        <Tooltip>
                            <TooltipTrigger asChild>
                                <Button
                                    variant="default"
                                    className="hover:bg-primary-600 hidden bg-primary md:flex"
                                >
                                    <Plus className="mr-2 h-4 w-4" />
                                    {t.add_word}
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>{t.add_word}</p>
                            </TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                </div>

                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button
                            variant="ghost"
                            className="size-10 rounded-full p-1"
                        >
                            <Avatar className="size-8 overflow-hidden rounded-full bg-neutral-200">
                                <AvatarFallback className="bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {getInitials(userName)}
                                </AvatarFallback>
                            </Avatar>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent className="w-56" align="end">
                        <div className="md:hidden">
                            <DropdownMenuLabel>Language</DropdownMenuLabel>
                            <div className="flex flex-col gap-1 py-2">
                                {languages.map((lang) => (
                                    <button
                                        key={lang.code}
                                        type="button"
                                        onClick={() => setLanguage(lang.code)}
                                        className={`flex items-center gap-2 px-2 py-1.5 text-sm ${
                                            language === lang.code
                                                ? 'bg-primary/10 text-primary'
                                                : ''
                                        }`}
                                    >
                                        <span className="text-base">
                                            {lang.flag}
                                        </span>
                                        <span>{lang.label}</span>
                                    </button>
                                ))}
                            </div>
                            <DropdownMenuSeparator />
                        </div>

                        <DropdownMenuItem asChild>
                            <a href="/profile" className="cursor-pointer">
                                <User className="mr-2 h-4 w-4" />
                                {t.profile_settings}
                            </a>
                        </DropdownMenuItem>
                        <DropdownMenuItem asChild>
                            <a href="/settings" className="cursor-pointer">
                                <Settings className="mr-2 h-4 w-4" />
                                {t.app_settings}
                            </a>
                        </DropdownMenuItem>
                        <DropdownMenuItem asChild>
                            <a href="/statistics" className="cursor-pointer">
                                <Settings className="mr-2 h-4 w-4" />
                                {t.personal_statistics}
                            </a>
                        </DropdownMenuItem>
                        <DropdownMenuItem asChild>
                            <a
                                href="/password/change"
                                className="cursor-pointer"
                            >
                                <Settings className="mr-2 h-4 w-4" />
                                {t.change_password}
                            </a>
                        </DropdownMenuItem>
                        <DropdownMenuItem asChild>
                            <button
                                type="button"
                                onClick={handleLogout}
                                className="flex w-full cursor-pointer items-center"
                            >
                                <LogOut className="mr-2 h-4 w-4" />
                                {t.logout}
                            </button>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </header>
    );
}
