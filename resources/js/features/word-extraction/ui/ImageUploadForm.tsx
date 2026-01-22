import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { ExtractFormData } from '@/features/word-extraction/types';
import { useTranslation } from '@/shared/i18n/useTranslation';
import { InertiaFormProps } from '@inertiajs/react';
import { Sparkles, UploadCloud } from 'lucide-react';

interface ImageUploadFormProps {
    form: InertiaFormProps<ExtractFormData>;
    languagesList: string[];
    targetLanguages: string[];
    selectedLanguage: string;
    selectedTargetLanguage: string;
    onLanguageChange: (value: string) => void;
    onTargetLanguageChange: (value: string) => void;
    onImageChange: (e: React.ChangeEvent<HTMLInputElement>) => void;
    onSubmit: () => void;
}

export function ImageUploadForm({
    form,
    languagesList,
    targetLanguages,
    selectedLanguage,
    selectedTargetLanguage,
    onLanguageChange,
    onTargetLanguageChange,
    onImageChange,
    onSubmit,
}: ImageUploadFormProps) {
    const t = useTranslation();

    return (
        <Card className="border-neutral-200 bg-gradient-to-br from-white via-neutral-50 to-blue-50/30 shadow-lg dark:border-neutral-800 dark:from-neutral-900 dark:via-neutral-900 dark:to-blue-950/20">
            <CardHeader className="space-y-3">
                <div className="flex items-center gap-3">
                    <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-primary to-primary/80 shadow-md">
                        <Sparkles className="h-6 w-6 text-white" />
                    </div>
                    <div>
                        <CardTitle className="text-2xl font-bold text-neutral-900 dark:text-neutral-100">
                            {t.words.upload_image_title}
                        </CardTitle>
                        <CardDescription className="text-base">
                            {t.words.upload_image_description}
                        </CardDescription>
                    </div>
                </div>
            </CardHeader>
            <CardContent className="space-y-6">
                {/* Image Upload */}
                <div className="space-y-3">
                    <label
                        htmlFor="image-upload"
                        className="text-sm font-semibold text-neutral-900 dark:text-neutral-100"
                    >
                        {t.words.select_image}
                    </label>
                    <div className="group relative">
                        <input
                            id="image-upload"
                            type="file"
                            accept="image/*"
                            onChange={onImageChange}
                            className="block w-full cursor-pointer rounded-xl border-2 border-dashed border-neutral-300 bg-white p-4 text-sm transition-all file:mr-4 file:cursor-pointer file:rounded-lg file:border-0 file:bg-gradient-to-r file:from-secondary file:to-secondary/90 file:px-6 file:py-2.5 file:text-sm file:font-semibold file:text-white file:shadow-sm file:transition-all hover:border-primary hover:file:shadow-md dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-100 dark:hover:border-primary/80"
                        />
                        <UploadCloud className="pointer-events-none absolute top-1/2 right-4 h-5 w-5 -translate-y-1/2 text-neutral-400 transition-colors group-hover:text-primary dark:text-neutral-600" />
                    </div>
                    {form.errors.image && (
                        <p className="flex items-center gap-2 text-sm font-medium text-red-600 dark:text-red-400">
                            <span className="text-lg">⚠</span>
                            {form.errors.image}
                        </p>
                    )}
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                    {/* Source Language Selection */}
                    <div className="space-y-3">
                        <label
                            htmlFor="source-language"
                            className="text-sm font-semibold text-neutral-900 dark:text-neutral-100"
                        >
                            {t.words.source_language}
                        </label>
                        <Select
                            value={selectedLanguage}
                            onValueChange={onLanguageChange}
                        >
                            <SelectTrigger
                                id="source-language"
                                className="h-12 rounded-xl border-neutral-300 bg-white shadow-sm transition-all hover:border-primary dark:border-neutral-700 dark:bg-neutral-800 dark:hover:border-primary/80"
                            >
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {languagesList.map((lang) => (
                                    <SelectItem
                                        key={lang}
                                        value={lang}
                                        className="cursor-pointer"
                                    >
                                        {lang.toUpperCase()}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {form.errors.language && (
                            <p className="text-sm font-medium text-red-600 dark:text-red-400">
                                {form.errors.language}
                            </p>
                        )}
                    </div>

                    {/* Target Language Selection */}
                    <div className="space-y-3">
                        <label
                            htmlFor="target-language"
                            className="text-sm font-semibold text-neutral-900 dark:text-neutral-100"
                        >
                            {t.words.target_language}
                        </label>
                        <Select
                            value={selectedTargetLanguage}
                            onValueChange={onTargetLanguageChange}
                        >
                            <SelectTrigger
                                id="target-language"
                                className="h-12 rounded-xl border-neutral-300 bg-white shadow-sm transition-all hover:border-primary dark:border-neutral-700 dark:bg-neutral-800 dark:hover:border-primary/80"
                            >
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {targetLanguages.map((lang) => (
                                    <SelectItem
                                        key={lang}
                                        value={lang}
                                        className="cursor-pointer"
                                    >
                                        {lang.toUpperCase()}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {form.errors.target_language && (
                            <p className="text-sm font-medium text-red-600 dark:text-red-400">
                                {form.errors.target_language}
                            </p>
                        )}
                    </div>
                </div>

                {/* Submit Button */}
                <Button
                    onClick={onSubmit}
                    disabled={!form.data.image || form.processing}
                    className="h-12 w-full rounded-xl bg-gradient-to-r from-primary to-primary/90 text-base font-semibold shadow-lg transition-all hover:shadow-xl disabled:opacity-50 disabled:shadow-none"
                >
                    {form.processing ? (
                        <span className="flex items-center gap-2">
                            <span className="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                            {t.words.processing}
                        </span>
                    ) : (
                        <>
                            <Sparkles className="mr-2 h-5 w-5" />
                            {t.words.extract_words}
                        </>
                    )}
                </Button>
            </CardContent>
        </Card>
    );
}
