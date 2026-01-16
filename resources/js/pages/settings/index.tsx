import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { importWords } from '@/routes';
import { update } from '@/routes/settings';
import { useTranslation } from '@/shared/i18n/useTranslation';
import { User } from '@/types';
import { AuthHeader } from '@/widgets/auth/AuthHeader';
import { router, useForm } from '@inertiajs/react';
import { Download, Settings as SettingsIcon } from 'lucide-react';

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

const LANGUAGES = [
    { code: 'RU', label: 'Russian' },
    { code: 'EN', label: 'English' },
    { code: 'DE', label: 'German' },
];

export default function Settings({ user, settings }: SettingsPageProps) {
    const t = useTranslation();

    const form = useForm({
        paginate: settings.paginate,
        main_language: settings.main_language,
        show_starred: settings.show_starred,
        known_enabled: settings.known_enabled,
        latest_first: settings.latest_first,
        show_imported: settings.show_imported,
        show_shared: settings.show_shared,
        fresh_first: settings.fresh_first,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        form.post(update().url);
    };

    const handleImport = () => {
        router.post(importWords().url);
    };

    return (
        <div className="min-h-screen bg-gradient-to-br from-neutral-50 via-blue-50/30 to-green-50/20">
            <AuthHeader userName={user.name} />
            <main className="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
                <div className="mb-8">
                    <div className="mb-4 flex items-center gap-3">
                        <div className="flex h-12 w-12 items-center justify-center rounded-full bg-primary text-white">
                            <SettingsIcon className="h-6 w-6" />
                        </div>
                        <div>
                            <h1 className="text-3xl font-bold text-neutral-900 sm:text-4xl">
                                {t.settings?.title || 'App Settings'}
                            </h1>
                            <p className="mt-1 text-base text-neutral-600 sm:text-lg">
                                {t.settings?.subtitle ||
                                    'Customize your app display and behavior'}
                            </p>
                        </div>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm">
                        <div className="space-y-6">
                            <div className="grid gap-6 sm:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="paginate">
                                        {t.settings?.fields?.paginate}
                                    </Label>
                                    <Input
                                        id="paginate"
                                        type="number"
                                        min="1"
                                        max="100"
                                        value={form.data.paginate}
                                        onChange={(e) =>
                                            form.setData(
                                                'paginate',
                                                parseInt(e.target.value) || 20,
                                            )
                                        }
                                        disabled={form.processing}
                                        className="w-full"
                                    />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="main_language">
                                        {t.settings?.fields?.main_language}
                                    </Label>
                                    <Select
                                        value={form.data.main_language}
                                        onValueChange={(value) =>
                                            form.setData('main_language', value)
                                        }
                                        disabled={form.processing}
                                    >
                                        <SelectTrigger id="main_language">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {settings.languages_list.map(
                                                (lang) => (
                                                    <SelectItem
                                                        key={lang}
                                                        value={lang}
                                                    >
                                                        {LANGUAGES.find(
                                                            (l) =>
                                                                l.code === lang,
                                                        )?.label || lang}
                                                    </SelectItem>
                                                ),
                                            )}
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>

                            <div className="space-y-4">
                                <div className="flex items-center space-x-2">
                                    <Checkbox
                                        id="show_starred"
                                        checked={form.data.show_starred}
                                        onCheckedChange={(checked) =>
                                            form.setData(
                                                'show_starred',
                                                checked as boolean,
                                            )
                                        }
                                        disabled={form.processing}
                                    />
                                    <Label
                                        htmlFor="show_starred"
                                        className="cursor-pointer"
                                    >
                                        {t.settings?.fields?.show_starred}
                                    </Label>
                                </div>

                                <div className="flex items-center space-x-2">
                                    <Checkbox
                                        id="known_enabled"
                                        checked={form.data.known_enabled}
                                        onCheckedChange={(checked) =>
                                            form.setData(
                                                'known_enabled',
                                                checked as boolean,
                                            )
                                        }
                                        disabled={form.processing}
                                    />
                                    <Label
                                        htmlFor="known_enabled"
                                        className="cursor-pointer"
                                    >
                                        {t.settings?.fields?.known_enabled}
                                    </Label>
                                </div>

                                <div className="flex items-center space-x-2">
                                    <Checkbox
                                        id="latest_first"
                                        checked={form.data.latest_first}
                                        onCheckedChange={(checked) =>
                                            form.setData(
                                                'latest_first',
                                                checked as boolean,
                                            )
                                        }
                                        disabled={form.processing}
                                    />
                                    <Label
                                        htmlFor="latest_first"
                                        className="cursor-pointer"
                                    >
                                        {t.settings?.fields?.latest_first}
                                    </Label>
                                </div>

                                <div className="flex items-center space-x-2">
                                    <Checkbox
                                        id="show_imported"
                                        checked={form.data.show_imported}
                                        onCheckedChange={(checked) =>
                                            form.setData(
                                                'show_imported',
                                                checked as boolean,
                                            )
                                        }
                                        disabled={form.processing}
                                    />
                                    <Label
                                        htmlFor="show_imported"
                                        className="cursor-pointer"
                                    >
                                        {t.settings?.fields?.show_imported}
                                    </Label>
                                </div>

                                <div className="flex items-center space-x-2">
                                    <Checkbox
                                        id="show_shared"
                                        checked={form.data.show_shared}
                                        onCheckedChange={(checked) =>
                                            form.setData(
                                                'show_shared',
                                                checked as boolean,
                                            )
                                        }
                                        disabled={form.processing}
                                    />
                                    <Label
                                        htmlFor="show_shared"
                                        className="cursor-pointer"
                                    >
                                        {t.settings?.fields?.show_shared}
                                    </Label>
                                </div>

                                <div className="flex items-center space-x-2">
                                    <Checkbox
                                        id="fresh_first"
                                        checked={form.data.fresh_first}
                                        onCheckedChange={(checked) =>
                                            form.setData(
                                                'fresh_first',
                                                checked as boolean,
                                            )
                                        }
                                        disabled={form.processing}
                                    />
                                    <Label
                                        htmlFor="fresh_first"
                                        className="cursor-pointer"
                                    >
                                        {t.settings?.fields?.fresh_first}
                                    </Label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="flex justify-end">
                        <Button
                            type="submit"
                            disabled={form.processing}
                            className="hover:bg-primary-600 bg-primary"
                        >
                            {form.processing
                                ? t.settings?.saving || 'Saving...'
                                : t.settings?.save || 'Save'}
                        </Button>
                    </div>
                </form>

                <div className="mt-8 rounded-xl border-2 border-dashed border-neutral-200 bg-white p-6 shadow-sm">
                    <div className="flex flex-col items-center space-y-4 text-center">
                        <div className="flex h-12 w-12 items-center justify-center rounded-full bg-secondary text-white">
                            <Download className="h-6 w-6" />
                        </div>
                        <div className="space-y-2">
                            <h3 className="text-lg font-semibold text-neutral-900">
                                {t.settings?.import_words || 'Import Words'}
                            </h3>
                            <p className="text-sm text-neutral-600">
                                {t.settings?.import_description ||
                                    'Too lazy to add words manually? Import the most commonly used words for your default language'}
                            </p>
                        </div>
                        <Button
                            onClick={handleImport}
                            disabled={form.processing}
                            variant="outline"
                            className="border-primary text-primary hover:bg-primary hover:text-white"
                        >
                            {form.processing
                                ? t.settings?.importing || 'Importing...'
                                : t.settings?.import_words || 'Import Words'}
                        </Button>
                    </div>
                </div>
            </main>
        </div>
    );
}
