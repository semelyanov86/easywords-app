import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useSettingsForm } from '@/features/settings/lib/useSettingsForm';
import { SettingCheckbox } from '@/features/settings/ui/SettingCheckbox';
import { SettingsFormCard } from '@/features/settings/ui/SettingsFormCard';
import { useTranslation } from '@/shared/i18n/useTranslation';
import { Eye, Filter, List, Save } from 'lucide-react';

interface SettingsFormData {
    paginate: number;
    main_language: string;
    default_language: string;
    show_starred: boolean;
    known_enabled: boolean;
    latest_first: boolean;
    show_imported: boolean;
    show_shared: boolean;
    fresh_first: boolean;
}

interface SettingsFormProps {
    settings: SettingsFormData & {
        languages_list: string[];
    };
}

const LANGUAGES = [
    { code: 'RU', label: 'Russian' },
    { code: 'EN', label: 'English' },
    { code: 'DE', label: 'German' },
];

export function SettingsForm({ settings }: SettingsFormProps) {
    const t = useTranslation();

    const { form, handleSubmit } = useSettingsForm({
        paginate: settings.paginate,
        default_language: settings.default_language,
        main_language: settings.main_language,
        show_starred: settings.show_starred,
        known_enabled: settings.known_enabled,
        latest_first: settings.latest_first,
        show_imported: settings.show_imported,
        show_shared: settings.show_shared,
        fresh_first: settings.fresh_first,
    });

    return (
        <form onSubmit={handleSubmit} className="space-y-6">
            <SettingsFormCard
                title={t.settings?.general || 'General Settings'}
                description={
                    t.settings?.general_description ||
                    'Configure basic application behavior'
                }
                icon={<List className="h-5 w-5" />}
            >
                <div className="grid gap-6 sm:grid-cols-2">
                    <div className="space-y-2">
                        <Label
                            htmlFor="paginate"
                            className="text-sm font-medium"
                        >
                            {t.settings?.fields?.paginate || 'Items per page'}
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
                        <Label
                            htmlFor="default_language"
                            className="text-sm font-medium"
                        >
                            {t.settings?.fields?.default_language ||
                                'Main Language'}
                        </Label>
                        <Select
                            value={form.data.default_language}
                            onValueChange={(value) =>
                                form.setData('default_language', value)
                            }
                            disabled={form.processing}
                        >
                            <SelectTrigger id="default_language">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {settings.languages_list.map((lang) => (
                                    <SelectItem key={lang} value={lang}>
                                        {LANGUAGES.find((l) => l.code === lang)
                                            ?.label || lang}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                </div>
            </SettingsFormCard>

            <SettingsFormCard
                title={t.settings?.visibility || 'Visibility Options'}
                description={
                    t.settings?.visibility_description ||
                    'Control what content is displayed'
                }
                icon={<Eye className="h-5 w-5" />}
            >
                <div className="space-y-1">
                    <SettingCheckbox
                        id="show_starred"
                        label={
                            t.settings?.fields?.show_starred ||
                            'Show starred words'
                        }
                        checked={form.data.show_starred}
                        onChange={(checked) =>
                            form.setData('show_starred', checked)
                        }
                        disabled={form.processing}
                    />
                    <SettingCheckbox
                        id="known_enabled"
                        label={
                            t.settings?.fields?.known_enabled ||
                            'Show known words'
                        }
                        checked={form.data.known_enabled}
                        onChange={(checked) =>
                            form.setData('known_enabled', checked)
                        }
                        disabled={form.processing}
                    />
                    <SettingCheckbox
                        id="show_imported"
                        label={
                            t.settings?.fields?.show_imported ||
                            'Show imported words'
                        }
                        checked={form.data.show_imported}
                        onChange={(checked) =>
                            form.setData('show_imported', checked)
                        }
                        disabled={form.processing}
                    />
                    <SettingCheckbox
                        id="show_shared"
                        label={
                            t.settings?.fields?.show_shared ||
                            'Show shared words'
                        }
                        checked={form.data.show_shared}
                        onChange={(checked) =>
                            form.setData('show_shared', checked)
                        }
                        disabled={form.processing}
                    />
                </div>
            </SettingsFormCard>

            <SettingsFormCard
                title={t.settings?.sorting || 'Sorting Preferences'}
                description={
                    t.settings?.sorting_description ||
                    'Choose how words are ordered'
                }
                icon={<Filter className="h-5 w-5" />}
            >
                <div className="space-y-1">
                    <SettingCheckbox
                        id="latest_first"
                        label={
                            t.settings?.fields?.latest_first ||
                            'Show latest words first'
                        }
                        checked={form.data.latest_first}
                        onChange={(checked) =>
                            form.setData('latest_first', checked)
                        }
                        disabled={form.processing}
                    />
                    <SettingCheckbox
                        id="fresh_first"
                        label={
                            t.settings?.fields?.fresh_first ||
                            'Show fresh words first'
                        }
                        checked={form.data.fresh_first}
                        onChange={(checked) =>
                            form.setData('fresh_first', checked)
                        }
                        disabled={form.processing}
                    />
                </div>
            </SettingsFormCard>

            <div className="flex justify-end gap-4">
                <Button type="submit" disabled={form.processing} size="lg">
                    <Save className="mr-2 h-4 w-4" />
                    {form.processing
                        ? t.settings?.saving || 'Saving...'
                        : t.settings?.save || 'Save Settings'}
                </Button>
            </div>
        </form>
    );
}
