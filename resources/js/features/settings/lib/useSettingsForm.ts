import { update } from '@/routes/settings';
import { useTranslation } from '@/shared/i18n/useTranslation';
import { toast } from '@/shared/lib/use-toast';
import { useForm } from '@inertiajs/react';

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

export function useSettingsForm(initialData: SettingsFormData) {
    const t = useTranslation();

    const form = useForm<SettingsFormData>(initialData);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        form.post(update().url, {
            preserveScroll: true,
            onSuccess: () => {
                toast({
                    variant: 'success',
                    title: t.settings?.success_title || 'Success!',
                    description:
                        t.settings?.success_message ||
                        'Your settings have been saved successfully.',
                });
            },
            onError: () => {
                toast({
                    variant: 'destructive',
                    title: t.settings?.error_title || 'Error',
                    description:
                        t.settings?.error_message ||
                        'Failed to save settings. Please try again.',
                });
            },
        });
    };

    return {
        form,
        handleSubmit,
    };
}
