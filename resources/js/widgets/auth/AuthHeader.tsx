import { Button } from '@/components/ui/button';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { UserMenu } from '@/features/user-menu';
import { dashboard } from '@/routes';
import words from '@/routes/words';
import { dashboardTranslations } from '@/shared/i18n/dashboard';
import { useDashboardTranslation } from '@/shared/i18n/useDashboardTranslation';
import AppLogoIcon from '@/shared/ui/AppLogoIcon';
import { LanguageSwitcher } from '@/shared/ui/LanguageSwitcher';
import { Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';

interface AuthHeaderProps {
    userName: string;
}

export function AuthHeader({ userName }: AuthHeaderProps) {
    const t = useDashboardTranslation(dashboardTranslations);

    return (
        <header className="sticky top-0 z-50 border-b border-border bg-background/80 shadow-sm backdrop-blur-md">
            <div className="mx-auto flex h-16 items-center justify-between px-4 md:h-20 md:max-w-7xl md:px-6">
                {/* Left Section - Logo & Actions */}
                <div className="flex items-center gap-3 md:gap-4 lg:gap-6">
                    {/* Logo */}
                    <a
                        href={dashboard().url}
                        className="group flex items-center gap-2 transition-transform hover:scale-105 md:gap-3"
                    >
                        <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-primary to-secondary shadow-md transition-shadow group-hover:shadow-lg md:h-10 md:w-10">
                            <AppLogoIcon />
                        </div>
                        <span className="hidden bg-gradient-to-r from-primary to-secondary bg-clip-text text-xl font-bold text-transparent md:inline md:text-2xl">
                            EasyWords
                        </span>
                    </a>

                    {/* Language Switcher - Desktop Only */}
                    <div className="hidden lg:block">
                        <LanguageSwitcher />
                    </div>

                    {/* Add Word Button - Mobile Icon Only */}
                    <TooltipProvider>
                        <Tooltip>
                            <TooltipTrigger asChild>
                                <Link href={words.create.url()}>
                                    <Button
                                        variant="default"
                                        size="icon"
                                        className="h-9 w-9 bg-gradient-to-r from-primary to-primary/90 shadow-md transition-all hover:scale-105 hover:shadow-lg md:hidden"
                                    >
                                        <Plus className="h-4 w-4" />
                                    </Button>
                                </Link>
                            </TooltipTrigger>
                            <TooltipContent side="bottom">
                                <p>{t.add_word}</p>
                            </TooltipContent>
                        </Tooltip>
                    </TooltipProvider>

                    {/* Add Word Button - Tablet & Desktop */}
                    <TooltipProvider>
                        <Tooltip>
                            <TooltipTrigger asChild>
                                <Link href={words.create.url()}>
                                    <Button
                                        variant="default"
                                        className="hidden bg-gradient-to-r from-primary to-primary/90 shadow-md transition-all hover:scale-105 hover:shadow-lg md:flex"
                                    >
                                        <Plus className="mr-2 h-4 w-4" />
                                        {t.add_word}
                                    </Button>
                                </Link>
                            </TooltipTrigger>
                            <TooltipContent side="bottom">
                                <p>{t.add_word}</p>
                            </TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                </div>

                {/* Right Section - User Menu */}
                <UserMenu userName={userName} />
            </div>
        </header>
    );
}
