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
import { UserSettings } from '@/features/settings/types/settings';
import { create as createWord } from '@/routes/words';
import { extract } from '@/routes/words/extract-from-image';
import { useTranslation } from '@/shared/i18n/useTranslation';
import { User } from '@/types';
import { AuthHeader } from '@/widgets/auth/AuthHeader';
import { Head, router, useForm } from '@inertiajs/react';
import { UploadCloud } from 'lucide-react';
import { useState } from 'react';

interface ExtractedWord {
    original: string;
    translation: string;
    language: string;
}

interface ExtractFromImagePageProps {
    words?: ExtractedWord[];
    user: User;
    settings: UserSettings;
    flash?: {
        success?: string;
        error?: string;
    };
}

export default function ExtractFromImagePage({
    words,
    user,
    settings,
}: ExtractFromImagePageProps) {
    const t = useTranslation();
    const [selectedLanguage, setSelectedLanguage] = useState<string>('en');
    const [selectedTargetLanguage, setSelectedTargetLanguage] =
        useState<string>('ru');

    const extractForm = useForm<{
        image: File | null;
        language: string;
        target_language?: string;
    }>({
        image: null,
        language: selectedLanguage,
        target_language: selectedTargetLanguage,
    });

    const targetLanguages = ['RU'];

    const handleImageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            extractForm.setData('image', file);
        }
    };

    const handleExtract = () => {
        extractForm.post(extract().url, {
            onSuccess: () => {
                extractForm.reset();
                setSelectedLanguage('en');
                setSelectedTargetLanguage('ru');
            },
        });
    };

    const handleAddWord = (
        original: string,
        translation: string,
        language: string,
    ) => {
        router.post(
            createWord().url,
            {
                original,
                translated: translation,
                language,
            },
            {
                onSuccess: () => {
                    router.reload({ only: ['flash'] });
                },
            },
        );
    };

    return (
        <>
            <Head title={t.words.extract_from_image_title} />
            <AuthHeader userName={user.name} />
            <div className="mx-auto max-w-4xl px-4 py-8 md:py-12">
                {/* Header */}
                <div className="mb-8">
                    <div className="flex items-center gap-4">
                        <Button
                            variant="ghost"
                            onClick={() => router.visit(createWord().url)}
                            className="hover:text-primary-600 text-neutral-600"
                        >
                            ← {t.words.back_to_create}
                        </Button>
                    </div>
                    <h1 className="text-3xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100">
                        {t.words.extract_from_image_title}
                    </h1>
                    <p className="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
                        {t.words.extract_from_image_description}
                    </p>
                </div>

                {/* Upload Form */}
                {!words && (
                    <Card>
                        <CardHeader>
                            <CardTitle>{t.words.upload_image_title}</CardTitle>
                            <CardDescription>
                                {t.words.upload_image_description}
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-6">
                            {/* Image Upload */}
                            <div className="flex flex-col gap-2">
                                <label
                                    htmlFor="image-upload"
                                    className="text-sm font-medium text-neutral-900 dark:text-neutral-100"
                                >
                                    {t.words.select_image}
                                </label>
                                <div className="flex items-center gap-4">
                                    <input
                                        id="image-upload"
                                        type="file"
                                        accept="image/*"
                                        onChange={handleImageChange}
                                        className="file:hover:bg-secondary-600 block w-full rounded-md border border-neutral-300 p-2 text-sm file:mr-4 file:rounded-md file:border-0 file:bg-secondary file:px-4 file:py-2 file:text-sm file:text-white dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-100"
                                    />
                                </div>
                                {extractForm.errors.image && (
                                    <p className="text-sm text-red-600">
                                        {extractForm.errors.image}
                                    </p>
                                )}
                            </div>

                            {/* Language Selection */}
                            <div className="flex flex-col gap-2">
                                <label
                                    htmlFor="source-language"
                                    className="text-sm font-medium text-neutral-900 dark:text-neutral-100"
                                >
                                    {t.words.source_language}
                                </label>
                                <Select
                                    value={selectedLanguage}
                                    onValueChange={(value) => {
                                        setSelectedLanguage(value);
                                        extractForm.setData('language', value);
                                    }}
                                >
                                    <SelectTrigger id="source-language">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {settings.languages_list
                                            ? settings.languages_list.map(
                                                  (lang) => (
                                                      <SelectItem
                                                          key={lang}
                                                          value={lang}
                                                      >
                                                          {lang}
                                                      </SelectItem>
                                                  ),
                                              )
                                            : null}
                                    </SelectContent>
                                </Select>
                                {extractForm.errors.language && (
                                    <p className="text-sm text-red-600">
                                        {extractForm.errors.language}
                                    </p>
                                )}
                            </div>

                            {/* Target Language Selection */}
                            <div className="flex flex-col gap-2">
                                <label
                                    htmlFor="target-language"
                                    className="text-sm font-medium text-neutral-900 dark:text-neutral-100"
                                >
                                    {t.words.target_language}
                                </label>
                                <Select
                                    value={selectedTargetLanguage}
                                    onValueChange={(value) => {
                                        setSelectedTargetLanguage(value);
                                        extractForm.setData(
                                            'target_language',
                                            value,
                                        );
                                    }}
                                >
                                    <SelectTrigger id="target-language">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {targetLanguages.map((lang) => (
                                            <SelectItem key={lang} value={lang}>
                                                {lang}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {extractForm.errors.target_language && (
                                    <p className="text-sm text-red-600">
                                        {extractForm.errors.target_language}
                                    </p>
                                )}
                            </div>

                            {/* Submit Button */}
                            <Button
                                onClick={handleExtract}
                                disabled={
                                    !extractForm.data.image ||
                                    extractForm.processing
                                }
                                className="hover:bg-primary-600 w-full bg-primary"
                            >
                                {extractForm.processing ? (
                                    t.words.processing
                                ) : (
                                    <>
                                        <UploadCloud className="mr-2 h-4 w-4" />
                                        {t.words.extract_words}
                                    </>
                                )}
                            </Button>
                        </CardContent>
                    </Card>
                )}

                {/* Results Table */}
                {words && words.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle>
                                {t.words.extracted_words_title}
                            </CardTitle>
                            <CardDescription>
                                {t.words.extracted_words_description}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="overflow-x-auto">
                                <table className="w-full">
                                    <thead>
                                        <tr className="border-b border-neutral-200 dark:border-neutral-700">
                                            <th className="px-4 py-3 text-left text-sm font-semibold text-neutral-900 dark:text-neutral-100">
                                                {t.words.original}
                                            </th>
                                            <th className="px-4 py-3 text-left text-sm font-semibold text-neutral-900 dark:text-neutral-100">
                                                {t.words.translation}
                                            </th>
                                            <th className="px-4 py-3 text-left text-sm font-semibold text-neutral-900 dark:text-neutral-100">
                                                {t.words.language}
                                            </th>
                                            <th className="px-4 py-3 text-left text-sm font-semibold text-neutral-900 dark:text-neutral-100">
                                                {t.words.actions}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {words.map((word) => (
                                            <tr
                                                key={word.original}
                                                className="border-b border-neutral-100 dark:border-neutral-800"
                                            >
                                                <td className="px-4 py-3 text-sm text-neutral-900 dark:text-neutral-100">
                                                    {word.original}
                                                </td>
                                                <td className="px-4 py-3 text-sm text-neutral-900 dark:text-neutral-100">
                                                    {word.translation}
                                                </td>
                                                <td className="px-4 py-3 text-sm text-neutral-900 dark:text-neutral-100">
                                                    {word.language.toUpperCase()}
                                                </td>
                                                <td className="px-4 py-3">
                                                    <Button
                                                        size="sm"
                                                        onClick={() =>
                                                            handleAddWord(
                                                                word.original,
                                                                word.translation,
                                                                word.language,
                                                            )
                                                        }
                                                        className="hover:bg-secondary-600 bg-secondary"
                                                    >
                                                        {t.words.add_word}
                                                    </Button>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {words && words.length === 0 && (
                    <Card>
                        <CardContent className="py-12 text-center">
                            <p className="text-neutral-600 dark:text-neutral-400">
                                {t.words.no_words_extracted}
                            </p>
                            <Button
                                onClick={() => router.visit(createWord().url)}
                                className="mt-4"
                            >
                                {t.words.back_to_create}
                            </Button>
                        </CardContent>
                    </Card>
                )}
            </div>
        </>
    );
}
