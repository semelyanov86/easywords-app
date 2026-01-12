import { useTranslation } from '@/shared/i18n/useTranslation';
import { colors } from '@/shared/ui/colors';

const screenshots = [
    { src: '/images/screenshots/1.png', alt: 'EasyWords word list view' },
    { src: '/images/screenshots/2.png', alt: 'Flashcard learning interface' },
    { src: '/images/screenshots/3.png', alt: 'Word detail with AI examples' },
    { src: '/images/screenshots/4.png', alt: 'Progress statistics dashboard' },
    { src: '/images/screenshots/5.png', alt: 'Mobile app interface' },
] as const;

export function ScreenshotsSection() {
    const t = useTranslation();

    return (
        <section
            className={`bg-[${colors.background.DEFAULT}] py-20 dark:bg-[${colors.background.dark}]`}
        >
            <div className="container mx-auto px-4 lg:px-8">
                <div className="mx-auto max-w-6xl">
                    <div className="mb-16 text-center">
                        <h2
                            className={`mb-4 text-3xl font-bold text-[${colors.text.DEFAULT}] lg:text-4xl dark:text-[${colors.text.dark}]`}
                        >
                            {t.screenshots.title}
                        </h2>
                        <p
                            className={`text-lg text-[${colors.text.muted}] dark:text-[${colors.text.darkMuted}]`}
                        >
                            {t.screenshots.subtitle}
                        </p>
                    </div>

                    <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {screenshots.map((screenshot, index) => (
                            <figure
                                key={screenshot.src}
                                className={`overflow-hidden rounded-lg border border-[${colors.border.DEFAULT}] bg-white shadow-sm dark:border-[${colors.border.dark}] dark:bg-[${colors.accent.cardDark}] ${
                                    index === screenshots.length - 1 &&
                                    screenshots.length % 2 !== 0
                                        ? 'sm:col-span-2 lg:col-span-1'
                                        : ''
                                }`}
                            >
                                <img
                                    src={screenshot.src}
                                    alt={screenshot.alt}
                                    loading="lazy"
                                    className="h-full w-full object-cover"
                                />
                            </figure>
                        ))}
                    </div>
                </div>
            </div>
        </section>
    );
}
