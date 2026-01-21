import { useTranslation } from '@/shared/i18n/useTranslation';
import { Toaster } from '@/shared/ui/Toaster';
import { User } from '@/types';
import { AuthHeader } from '@/widgets/auth/AuthHeader';
import { ImportWordsCard } from '@/widgets/settings/ImportWordsCard';
import { SettingsForm } from '@/widgets/settings/SettingsForm';
import { Head } from '@inertiajs/react';
import { Settings as SettingsIcon } from 'lucide-react';

interface UserSettings {
    paginate: number;
    fresh_first: boolean;
    show_starred: boolean;
    latest_first: boolean;
    known_enabled: boolean;
    main_language: string;
    show_imported: boolean;
    languages_list: string[];
    starred_enabled: boolean;
    default_language: string;
    show_shared: boolean;
}

interface SettingsPageProps {
    user: User;
    settings: UserSettings;
}

export default function Settings({ user, settings }: SettingsPageProps) {
    const t = useTranslation();

    return (
        <>
            <Head title={t.settings?.title || 'App Settings'} />
            <div className="min-h-screen bg-background">
                <AuthHeader userName={user.name} />
                <main className="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
                    <div className="mb-10">
                        <div className="flex items-start gap-4">
                            <div className="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-primary to-primary/80 text-primary-foreground shadow-lg">
                                <SettingsIcon className="h-7 w-7" />
                            </div>
                            <div className="flex-1">
                                <h1 className="text-4xl font-bold tracking-tight text-foreground">
                                    {t.settings?.title || 'App Settings'}
                                </h1>
                                <p className="mt-2 text-lg text-muted-foreground">
                                    {t.settings?.subtitle ||
                                        'Customize your learning experience'}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="space-y-8">
                        <SettingsForm settings={settings} />
                        <ImportWordsCard />
                    </div>
                </main>
            </div>
            <Toaster />
        </>
    );
}
