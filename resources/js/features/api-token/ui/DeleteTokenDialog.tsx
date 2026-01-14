import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { AlertTriangle } from 'lucide-react';

interface DeleteTokenDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    onConfirm: () => void;
    tokenName: string;
    translations: {
        title: string;
        description: string;
        cancel: string;
        confirm: string;
    };
}

export function DeleteTokenDialog({
    open,
    onOpenChange,
    onConfirm,
    tokenName,
    translations,
}: DeleteTokenDialogProps) {
    const handleConfirm = () => {
        onConfirm();
        onOpenChange(false);
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                        <AlertTriangle className="h-6 w-6 text-red-600" />
                    </div>
                    <DialogTitle className="text-center">
                        {translations.title}
                    </DialogTitle>
                    <DialogDescription className="text-center">
                        {translations.description}
                        <span className="mt-2 block font-semibold text-neutral-900">
                            {tokenName}
                        </span>
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter className="sm:justify-center">
                    <Button
                        type="button"
                        variant="outline"
                        onClick={() => onOpenChange(false)}
                        className="sm:w-24"
                    >
                        {translations.cancel}
                    </Button>
                    <Button
                        type="button"
                        onClick={handleConfirm}
                        className="bg-red-600 hover:bg-red-700 sm:w-24"
                    >
                        {translations.confirm}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
