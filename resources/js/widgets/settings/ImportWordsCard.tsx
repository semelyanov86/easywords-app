import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { importWords } from '@/routes';
import { useTranslation } from '@/shared/i18n/useTranslation';
import { toast } from '@/shared/lib/use-toast';
import { router } from '@inertiajs/react';
import { Download, Sparkles } from 'lucide-react';
import { useState } from 'react';

export function ImportWordsCard() {
    const t = useTranslation();
    const [isImporting, setIsImporting] = useState(false);

    const handleImport = () => {
        setIsImporting(true);
        router.post(
            importWords().url,
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    toast({
                        variant: 'success',
                        title:
                            t.settings?.import_success_title ||
                            'Words imported!',
                        description:
                            t.settings?.import_success_message ||
                            'Common words have been added to your vocabulary.',
                    });
                    setIsImporting(false);
                },
                onError: () => {
                    toast({
                        variant: 'destructive',
                        title: t.settings?.error_title || 'Error',
                        description:
                            t.settings?.import_error_message ||
                            'Failed to import words. Please try again.',
                    });
                    setIsImporting(false);
                },
            },
        );
    };

    return (
        <Card className="relative overflow-hidden border-2 border-dashed border-border bg-gradient-to-br from-card to-muted/50 shadow-sm">
            <div className="absolute top-0 right-0 h-32 w-32 opacity-5">
                <Sparkles className="h-full w-full text-secondary" />
            </div>
            <div className="relative flex flex-col items-center space-y-6 p-8 text-center">
                <div className="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-secondary to-secondary/80 text-secondary-foreground shadow-lg">
                    <Download className="h-8 w-8" />
                </div>
                <div className="space-y-3">
                    <h3 className="text-xl font-bold text-foreground">
                        {t.settings?.import_words || 'Import Common Words'}
                    </h3>
                    <p className="mx-auto max-w-md text-sm leading-relaxed text-muted-foreground">
                        {t.settings?.import_description ||
                            'Save time by importing the most commonly used words for your default language. Perfect for getting started quickly!'}
                    </p>
                </div>
                <Button
                    onClick={handleImport}
                    disabled={isImporting}
                    variant="outline"
                    size="lg"
                    className="border-secondary bg-secondary/5 text-secondary hover:bg-secondary hover:text-secondary-foreground"
                >
                    <Download className="mr-2 h-5 w-5" />
                    {isImporting
                        ? t.settings?.importing || 'Importing...'
                        : t.settings?.import_words || 'Import Words'}
                </Button>
            </div>
        </Card>
    );
}
