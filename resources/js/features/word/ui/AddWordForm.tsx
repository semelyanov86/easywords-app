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
import { cn } from '@/lib/utils';
import { dashboard } from '@/routes/index';
import { useTranslation } from '@/shared/i18n/useTranslation';
import { router, useForm } from '@inertiajs/react';
import { Check, Loader2, Sparkles, X } from 'lucide-react';
import { useState, type FormEvent } from 'react';
import { useWordTranslation } from '../lib/use-word-translation';

interface AddWordFormProps {
    languages: string[];
    createdWordId?: number;
}

interface WordFormData {
    original: string;
    translated: string;
    language: string;
}

interface SuccessNotification {
    id: number;
}

export function AddWordForm({ languages, createdWordId }: AddWordFormProps) {
    const [successNotification, setSuccessNotification] =
        useState<SuccessNotification | null>(
            createdWordId ? { id: createdWordId } : null,
        );
    const t = useTranslation();

    const { data, setData, post, processing, errors, reset } =
        useForm<WordFormData>({
            original: '',
            translated: '',
            language: languages[0] || '',
        });

    const {
        translate,
        isTranslating,
        error: translationError,
    } = useWordTranslation();

    const canTranslate = data.original.trim() !== '' && data.language !== '';

    const handleTranslate = async () => {
        if (!canTranslate) return;

        const translation = await translate(data.original, data.language);
        if (translation) {
            setData('translated', translation);
        }
    };

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();

        post('/words', {
            preserveScroll: true,
            onSuccess: (page) => {
                const responseData = page.props as {
                    word?: { id: number };
                };

                if (responseData.word?.id) {
                    setSuccessNotification({ id: responseData.word.id });
                    reset();
                }
            },
        });
    };

    const handleCancel = () => {
        router.get(dashboard.url());
    };

    const handleGoToWord = () => {
        // Пока переходим на dashboard, потом доработаем
        router.get(dashboard.url());
    };

    if (successNotification) {
        return (
            <div className="rounded-lg border-2 border-secondary bg-secondary/5 p-8 text-center">
                <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-secondary">
                    <Check className="h-8 w-8 text-white" />
                </div>
                <h2 className="mb-2 text-2xl font-bold text-neutral-900 dark:text-neutral-100">
                    {t.words.success_title}
                </h2>
                <p className="mb-6 text-neutral-600 dark:text-neutral-400">
                    {t.words.success_description}{' '}
                    <span className="font-mono font-semibold text-primary">
                        #{successNotification.id}
                    </span>
                </p>
                <div className="flex justify-center gap-3">
                    <Button
                        onClick={handleGoToWord}
                        className="bg-primary hover:bg-primary/90"
                    >
                        {t.words.go_to_word}
                    </Button>
                    <Button
                        variant="outline"
                        onClick={() => setSuccessNotification(null)}
                    >
                        {t.words.add_another}
                    </Button>
                </div>
            </div>
        );
    }

    return (
        <form
            onSubmit={handleSubmit}
            className="space-y-6 rounded-lg border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-800 dark:bg-neutral-900"
        >
            {/* Оригинальное значение */}
            <div className="space-y-2">
                <Label
                    htmlFor="original"
                    className="text-sm font-medium text-neutral-700 dark:text-neutral-300"
                >
                    {t.words.original_label}
                    <span className="ml-1 text-red-500">*</span>
                </Label>
                <Input
                    id="original"
                    type="text"
                    value={data.original}
                    onChange={(e) => setData('original', e.target.value)}
                    placeholder={t.words.original_placeholder}
                    className={cn(
                        'transition-colors',
                        errors.original && 'border-red-500 focus:ring-red-500',
                    )}
                    required
                />
                {errors.original && (
                    <p className="text-sm text-red-600 dark:text-red-400">
                        {errors.original}
                    </p>
                )}
            </div>

            {/* Язык */}
            <div className="space-y-2">
                <Label
                    htmlFor="language"
                    className="text-sm font-medium text-neutral-700 dark:text-neutral-300"
                >
                    {t.words.language_label}
                    <span className="ml-1 text-red-500">*</span>
                </Label>
                <Select
                    value={data.language}
                    onValueChange={(value) => setData('language', value)}
                >
                    <SelectTrigger
                        className={cn(
                            'transition-colors',
                            errors.language &&
                                'border-red-500 focus:ring-red-500',
                        )}
                    >
                        <SelectValue
                            placeholder={t.words.language_placeholder}
                        />
                    </SelectTrigger>
                    <SelectContent>
                        {languages.map((lang) => (
                            <SelectItem key={lang} value={lang}>
                                {lang.toUpperCase()}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                {errors.language && (
                    <p className="text-sm text-red-600 dark:text-red-400">
                        {errors.language}
                    </p>
                )}
            </div>

            {/* Перевод */}
            <div className="space-y-2">
                <Label
                    htmlFor="translated"
                    className="text-sm font-medium text-neutral-700 dark:text-neutral-300"
                >
                    {t.words.translated_label}
                    <span className="ml-1 text-red-500">*</span>
                </Label>
                <div className="flex gap-2">
                    <Input
                        id="translated"
                        type="text"
                        value={data.translated}
                        onChange={(e) => setData('translated', e.target.value)}
                        placeholder={t.words.translated_placeholder}
                        className={cn(
                            'flex-1 transition-colors',
                            errors.translated &&
                                'border-red-500 focus:ring-red-500',
                        )}
                        required
                    />
                    <Button
                        type="button"
                        onClick={handleTranslate}
                        disabled={!canTranslate || isTranslating}
                        variant="outline"
                        className={cn(
                            'gap-2 transition-all',
                            canTranslate &&
                                !isTranslating &&
                                'border-secondary text-secondary hover:bg-secondary hover:text-white',
                        )}
                    >
                        {isTranslating ? (
                            <>
                                <Loader2 className="h-4 w-4 animate-spin" />
                                {t.words.translating}
                            </>
                        ) : (
                            <>
                                <Sparkles className="h-4 w-4" />
                                {t.words.ai_translate}
                            </>
                        )}
                    </Button>
                </div>
                {errors.translated && (
                    <p className="text-sm text-red-600 dark:text-red-400">
                        {errors.translated}
                    </p>
                )}
                {translationError && (
                    <p className="text-sm text-red-600 dark:text-red-400">
                        {translationError}
                    </p>
                )}
            </div>

            {/* Кнопки действий */}
            <div className="flex gap-3 pt-4">
                <Button
                    type="submit"
                    disabled={processing}
                    className="flex-1 gap-2 bg-primary hover:bg-primary/90"
                >
                    {processing ? (
                        <>
                            <Loader2 className="h-4 w-4 animate-spin" />
                            {t.words.saving}
                        </>
                    ) : (
                        t.words.save_word
                    )}
                </Button>
                <Button
                    type="button"
                    onClick={handleCancel}
                    variant="outline"
                    disabled={processing}
                    className="gap-2"
                >
                    <X className="h-4 w-4" />
                    {t.words.cancel}
                </Button>
            </div>
        </form>
    );
}
