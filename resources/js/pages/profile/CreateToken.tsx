import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import profile from '@/routes/profile';
import { profileTranslations } from '@/shared/i18n/profile';
import { useNestedTranslation } from '@/shared/i18n/useNestedTranslation';
import { useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

interface CreateTokenProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
}

export default function CreateToken({ open, onOpenChange }: CreateTokenProps) {
    const t = useNestedTranslation(profileTranslations);
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
    });

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        post(profile.tokens.store().url, {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                onOpenChange(false);
            },
        });
    };

    const handleOpenChange = (newOpen: boolean) => {
        if (!newOpen) {
            reset();
        }
        onOpenChange(newOpen);
    };

    return (
        <Dialog open={open} onOpenChange={handleOpenChange}>
            <DialogContent>
                <form onSubmit={handleSubmit}>
                    <DialogHeader>
                        <DialogTitle>
                            {t.createTokenTitle || 'Create API Token'}
                        </DialogTitle>
                        <DialogDescription>
                            {t.createTokenDescription ||
                                'Enter a name for your new API token'}
                        </DialogDescription>
                    </DialogHeader>

                    <div className="py-4">
                        <Label htmlFor="name">
                            {t.tokenName || 'Token Name'}
                        </Label>
                        <Input
                            id="name"
                            type="text"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            placeholder={
                                t.tokenNamePlaceholder || 'My Application'
                            }
                            className="mt-2"
                            autoFocus
                        />
                        {errors.name && (
                            <p className="mt-1 text-sm text-red-600">
                                {errors.name}
                            </p>
                        )}
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => handleOpenChange(false)}
                            disabled={processing}
                        >
                            {t.cancel || 'Cancel'}
                        </Button>
                        <Button type="submit" disabled={processing}>
                            {processing
                                ? t.creating || 'Creating...'
                                : t.create || 'Create'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
