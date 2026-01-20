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
import { useInitials } from '@/hooks/use-initials';
import profile from '@/routes/profile';
import { show } from '@/routes/settings';
import { dashboardTranslations } from '@/shared/i18n/dashboard';
import { useLanguage, type Language } from '@/shared/i18n/LanguageContext';
import { useDashboardTranslation } from '@/shared/i18n/useDashboardTranslation';
import { router } from '@inertiajs/react';
import {
    BarChart3,
    ChevronDown,
    KeyRound,
    LogOut,
    Settings,
    User,
} from 'lucide-react';

interface UserMenuProps {
    userName: string;
}

interface LanguageOption {
    code: Language;
    label: string;
    flag: string;
}

const LANGUAGES: LanguageOption[] = [
    { code: 'en', label: 'English', flag: '🇬🇧' },
    { code: 'ru', label: 'Русский', flag: '🇷🇺' },
    { code: 'de', label: 'Deutsch', flag: '🇩🇪' },
];

export function UserMenu({ userName }: UserMenuProps) {
    const getInitials = useInitials();
    const { language, setLanguage } = useLanguage();
    const t = useDashboardTranslation(dashboardTranslations);

    const handleLogout = () => {
        router.post('/logout');
    };

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button
                    variant="ghost"
                    className="group flex items-center gap-2 rounded-full pr-3 pl-1 transition-all hover:bg-neutral-100 md:gap-3 md:pr-4"
                >
                    <Avatar className="h-9 w-9 border-2 border-primary/20 shadow-sm transition-all group-hover:border-primary/40 md:h-10 md:w-10">
                        <AvatarFallback className="bg-gradient-to-br from-primary to-secondary text-sm font-semibold text-white md:text-base">
                            {getInitials(userName)}
                        </AvatarFallback>
                    </Avatar>
                    <span className="hidden text-sm font-medium text-neutral-700 md:block">
                        {userName}
                    </span>
                    <ChevronDown className="h-4 w-4 text-neutral-500 transition-transform group-hover:text-neutral-700 group-data-[state=open]:rotate-180" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent
                className="w-64 rounded-xl border border-neutral-200 bg-white shadow-xl"
                align="end"
                sideOffset={8}
            >
                {/* User Info Header */}
                <div className="px-3 py-3">
                    <p className="text-sm font-semibold text-neutral-900">
                        {userName}
                    </p>
                    <p className="text-xs text-neutral-500">
                        {t.manage_account || 'Manage your account'}
                    </p>
                </div>

                <DropdownMenuSeparator className="bg-neutral-200" />

                {/* Mobile Language Switcher */}
                <div className="lg:hidden">
                    <DropdownMenuLabel className="text-xs font-semibold tracking-wide text-neutral-500 uppercase">
                        {t.language || 'Language'}
                    </DropdownMenuLabel>
                    <div className="flex flex-col gap-1 px-2 py-2">
                        {LANGUAGES.map((lang) => (
                            <button
                                key={lang.code}
                                type="button"
                                onClick={() => setLanguage(lang.code)}
                                className={`flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors ${
                                    language === lang.code
                                        ? 'bg-primary/10 font-medium text-primary'
                                        : 'text-neutral-700 hover:bg-neutral-50'
                                }`}
                            >
                                <span className="text-lg">{lang.flag}</span>
                                <span>{lang.label}</span>
                            </button>
                        ))}
                    </div>
                    <DropdownMenuSeparator className="bg-neutral-200" />
                </div>

                {/* Menu Items */}
                <div className="py-1">
                    <DropdownMenuItem asChild>
                        <a
                            href={profile.show().url}
                            className="flex cursor-pointer items-center gap-3 rounded-lg px-3 py-2.5 text-neutral-700 transition-all hover:bg-primary/5 hover:text-primary focus:bg-primary/5 focus:text-primary"
                        >
                            <div className="rounded-lg bg-primary/10 p-1.5">
                                <User className="h-4 w-4 text-primary" />
                            </div>
                            <span className="font-medium">
                                {t.profile_settings}
                            </span>
                        </a>
                    </DropdownMenuItem>

                    <DropdownMenuItem asChild>
                        <a
                            href={show().url}
                            className="flex cursor-pointer items-center gap-3 rounded-lg px-3 py-2.5 text-neutral-700 transition-all hover:bg-secondary/5 hover:text-secondary focus:bg-secondary/5 focus:text-secondary"
                        >
                            <div className="rounded-lg bg-secondary/10 p-1.5">
                                <Settings className="h-4 w-4 text-secondary" />
                            </div>
                            <span className="font-medium">
                                {t.app_settings}
                            </span>
                        </a>
                    </DropdownMenuItem>

                    <DropdownMenuItem asChild>
                        <a
                            href="/statistics"
                            className="flex cursor-pointer items-center gap-3 rounded-lg px-3 py-2.5 text-neutral-700 transition-all hover:bg-accent/5 hover:text-accent focus:bg-accent/5 focus:text-accent"
                        >
                            <div className="rounded-lg bg-accent/10 p-1.5">
                                <BarChart3 className="h-4 w-4 text-accent" />
                            </div>
                            <span className="font-medium">
                                {t.personal_statistics}
                            </span>
                        </a>
                    </DropdownMenuItem>

                    <DropdownMenuItem asChild>
                        <a
                            href={profile.password.edit().url}
                            className="flex cursor-pointer items-center gap-3 rounded-lg px-3 py-2.5 text-neutral-700 transition-all hover:bg-primary/5 hover:text-primary focus:bg-primary/5 focus:text-primary"
                        >
                            <div className="rounded-lg bg-primary/10 p-1.5">
                                <KeyRound className="h-4 w-4 text-primary" />
                            </div>
                            <span className="font-medium">
                                {t.change_password}
                            </span>
                        </a>
                    </DropdownMenuItem>
                </div>

                <DropdownMenuSeparator className="bg-neutral-200" />

                <div className="p-1">
                    <DropdownMenuItem asChild>
                        <button
                            type="button"
                            onClick={handleLogout}
                            className="flex w-full cursor-pointer items-center gap-3 rounded-lg px-3 py-2.5 text-neutral-700 transition-all hover:bg-red-50 hover:text-red-600 focus:bg-red-50 focus:text-red-600"
                        >
                            <div className="rounded-lg bg-red-50 p-1.5">
                                <LogOut className="h-4 w-4 text-red-600" />
                            </div>
                            <span className="font-medium">{t.logout}</span>
                        </button>
                    </DropdownMenuItem>
                </div>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
