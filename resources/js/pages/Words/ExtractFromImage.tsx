import { Button } from '@/components/ui/button';
import { UserSettings } from '@/features/settings/types/settings';
import {
    ExtractedWord,
    ExtractFormData,
} from '@/features/word-extraction/types';
import { EmptyWordsState } from '@/features/word-extraction/ui/EmptyWordsState';
import { ExtractedWordsTable } from '@/features/word-extraction/ui/ExtractedWordsTable';
import { ImageUploadForm } from '@/features/word-extraction/ui/ImageUploadForm';
import { create as createWord } from '@/routes/words';
import { extract } from '@/routes/words/extract-from-image';
import { useTranslation } from '@/shared/i18n/useTranslation';
import { toast } from '@/shared/lib/use-toast';
import { User } from '@/types';
import { AuthHeader } from '@/widgets/auth/AuthHeader';
import { Head, router, useForm } from '@inertiajs/react';
import axios from 'axios';
import { ArrowLeft } from 'lucide-react';
import { useEffect, useState } from 'react';

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
    flash,
}: ExtractFromImagePageProps) {
    const t = useTranslation();

    const targetLanguages = ['ru'];
    const defaultSourceLanguage = settings.languages_list?.[0] || 'en';
    const defaultTargetLanguage = targetLanguages[0];

    const [selectedLanguage, setSelectedLanguage] = useState<string>(
        defaultSourceLanguage,
    );
    const [selectedTargetLanguage, setSelectedTargetLanguage] =
        useState<string>(defaultTargetLanguage);
    const [isAddingWord, setIsAddingWord] = useState(false);

    const [displayedWords, setDisplayedWords] = useState<ExtractedWord[]>(
        words || [],
    );

    // Re-seeds the locally mutable word list whenever a new extraction arrives.
    // eslint-plugin-react-hooks 7.1 flags this via react-hooks/set-state-in-effect;
    // the idiomatic replacement is React's "adjust state during render" pattern,
    // which is a behavioural refactor and deliberately out of scope here.
    useEffect(() => {
        if (words) {
            // eslint-disable-next-line react-hooks/set-state-in-effect
            setDisplayedWords(words);
        }
    }, [words]);

    const extractForm = useForm<ExtractFormData>({
        image: null,
        language: defaultSourceLanguage,
        target_language: defaultTargetLanguage,
    });

    useEffect(() => {
        if (flash?.success) {
            toast({
                title: 'Success',
                description: flash.success,
                variant: 'default',
            });
        }
        if (flash?.error) {
            toast({
                title: 'Error',
                description: flash.error,
                variant: 'destructive',
            });
        }
    }, [flash]);

    const handleImageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            extractForm.setData('image', file);
        }
    };

    const handleLanguageChange = (value: string) => {
        setSelectedLanguage(value);
        extractForm.setData('language', value);
    };

    const handleTargetLanguageChange = (value: string) => {
        setSelectedTargetLanguage(value);
        extractForm.setData('target_language', value);
    };

    const handleExtract = () => {
        extractForm.post(extract().url, {
            onSuccess: () => {
                extractForm.reset();
                setSelectedLanguage(defaultSourceLanguage);
                setSelectedTargetLanguage(defaultTargetLanguage);
                toast({
                    title:
                        t.words.extraction_success ||
                        'Words extracted successfully',
                    description:
                        t.words.extraction_success_description ||
                        'You can now add words to your collection',
                });
            },
            onError: () => {
                toast({
                    title: 'Error',
                    description: t.words.extraction_error,
                    variant: 'destructive',
                });
            },
        });
    };

    const handleAddWord = async (
        original: string,
        translation: string,
        language: string,
    ) => {
        setIsAddingWord(true);

        try {
            await axios.post(createWord().url, {
                original,
                translated: translation,
                language,
            });

            toast({
                title: t.words.word_added_success || 'Word added successfully',
                description: `"${original}" ${t.words.has_been_added || 'has been added to your collection'}`,
            });
            // eslint-disable-next-line @typescript-eslint/no-unused-vars
        } catch (error: unknown) {
            toast({
                title: 'Error',
                description: t.words.word_add_error || 'Failed to add word',
                variant: 'destructive',
            });
        } finally {
            setIsAddingWord(false);
            setDisplayedWords((prevWords) =>
                prevWords.filter((word) => word.original !== original),
            );
        }
    };

    const handleBackToCreate = () => {
        router.visit(createWord().url);
    };

    return (
        <>
            <Head title={t.words.extract_from_image_title} />
            <AuthHeader userName={user.name} />
            <div className="min-h-screen bg-gradient-to-br from-neutral-50 via-blue-50/20 to-green-50/20 dark:from-neutral-950 dark:via-blue-950/10 dark:to-green-950/10">
                <div className="mx-auto max-w-5xl px-4 py-8 md:py-12">
                    {/* Header */}
                    <div className="mb-8 space-y-4">
                        <Button
                            variant="ghost"
                            onClick={handleBackToCreate}
                            className="group -ml-2 text-neutral-600 transition-colors hover:text-primary dark:text-neutral-400 dark:hover:text-primary"
                        >
                            <ArrowLeft className="mr-2 h-4 w-4 transition-transform group-hover:-translate-x-1" />
                            {t.words.back_to_create}
                        </Button>
                        <div>
                            <h1 className="bg-gradient-to-r from-primary via-primary/90 to-secondary bg-clip-text text-4xl font-extrabold tracking-tight text-transparent md:text-5xl">
                                {t.words.extract_from_image_title}
                            </h1>
                            <p className="mt-3 text-base text-neutral-600 dark:text-neutral-400">
                                {t.words.extract_from_image_description}
                            </p>
                        </div>
                    </div>

                    {/* Upload Form */}
                    {!words && (
                        <ImageUploadForm
                            form={extractForm}
                            languagesList={settings.languages_list || []}
                            targetLanguages={targetLanguages}
                            selectedLanguage={selectedLanguage}
                            selectedTargetLanguage={selectedTargetLanguage}
                            onLanguageChange={handleLanguageChange}
                            onTargetLanguageChange={handleTargetLanguageChange}
                            onImageChange={handleImageChange}
                            onSubmit={handleExtract}
                        />
                    )}

                    {displayedWords.length > 0 && (
                        <ExtractedWordsTable
                            words={displayedWords}
                            onAddWord={handleAddWord}
                            isAddingWord={isAddingWord}
                        />
                    )}

                    {words && displayedWords.length === 0 && (
                        <EmptyWordsState onBackToCreate={handleBackToCreate} />
                    )}
                </div>
            </div>
        </>
    );
}
