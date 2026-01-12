import { Button } from '@/components/ui/button';
import { dashboardTranslations } from '@/shared/i18n/dashboard';
import { useDashboardTranslation } from '@/shared/i18n/useDashboardTranslation';
import { AuthHeader } from '@/widgets/auth/AuthHeader';
import { ArrowRight } from 'lucide-react';

interface User {
    id: number;
    name: string;
    email: string;
}

interface UserSettings {
    main_language: string;
    languages_list: string[];
}

interface DashboardPageProps {
    user: User;
    settings: UserSettings;
}

export default function Dashboard({ user, settings }: DashboardPageProps) {
    const t = useDashboardTranslation(dashboardTranslations);
    const { main_language, languages_list } = settings;

    return (
        <div className="min-h-screen bg-neutral-50">
            <AuthHeader userName={user.name} />

            <main className="mx-auto max-w-7xl px-4 py-12">
                <div className="mb-8">
                    <h1 className="text-3xl font-bold text-neutral-900">
                        {t.welcome}
                    </h1>
                </div>

                <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    {languages_list.map((language) => (
                        <div key={language} className="space-y-3">
                            <Button
                                variant="outline"
                                className="h-24 w-full border-2 text-xl font-semibold text-neutral-900 hover:border-primary hover:bg-primary/5 hover:text-primary"
                            >
                                {main_language} → {language}
                                <ArrowRight className="ml-2 h-5 w-5" />
                            </Button>
                            <Button
                                variant="outline"
                                className="h-24 w-full border-2 text-xl font-semibold text-neutral-900 hover:border-primary hover:bg-primary/5 hover:text-primary"
                            >
                                {language} → {main_language}
                                <ArrowRight className="ml-2 h-5 w-5" />
                            </Button>
                        </div>
                    ))}
                </div>
            </main>
        </div>
    );
}
