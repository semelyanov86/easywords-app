import { toast } from '@/shared/lib/use-toast';
import type { PageProps } from '@/types';
import { usePage } from '@inertiajs/react';
import { useEffect } from 'react';

export function useFlashMessages(): void {
    const { flash } = usePage<PageProps>().props;

    useEffect(() => {
        if (flash?.success) {
            toast({
                title: flash.success,
                variant: 'default',
            });
        }

        if (flash?.error) {
            toast({
                title: flash.error,
                variant: 'destructive',
            });
        }
    }, [flash?.success, flash?.error]);
}
