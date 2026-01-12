import { useTranslation } from '@/shared/i18n/useTranslation';
import { colors } from '@/shared/ui/colors';

const features = [
    { key: 'flashcards', icon: '📚' },
    { key: 'aiExamples', icon: '🤖' },
    { key: 'multiPlatform', icon: '📱' },
    { key: 'statistics', icon: '📊' },
] as const;

export function FeaturesSection() {
    const t = useTranslation();

    return (
        <section className="bg-white py-20 dark:bg-[#161615]">
            <div className="container mx-auto px-4 lg:px-8">
                <div className="mx-auto max-w-6xl">
                    <div className="mb-16 text-center">
                        <h2
                            className={`mb-4 text-3xl font-bold text-[${colors.text.DEFAULT}] lg:text-4xl dark:text-[${colors.text.dark}]`}
                        >
                            {t.features.title}
                        </h2>
                        <p
                            className={`text-lg text-[${colors.text.muted}] dark:text-[${colors.text.darkMuted}]`}
                        >
                            {t.features.subtitle}
                        </p>
                    </div>

                    <div className="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                        {features.map((feature) => {
                            const featureData = t.features.items[feature.key];
                            return (
                                <article
                                    key={feature.key}
                                    className={`rounded-lg border border-[${colors.border.DEFAULT}] bg-[${colors.accent.gradientStart}] p-6 transition-shadow hover:shadow-lg dark:border-[${colors.border.dark}] dark:bg-[${colors.accent.cardEvenDarker}]`}
                                >
                                    <div
                                        className="mb-4 text-4xl"
                                        aria-hidden="true"
                                    >
                                        {feature.icon}
                                    </div>
                                    <h3
                                        className={`mb-3 text-xl font-semibold text-[${colors.text.DEFAULT}] dark:text-[${colors.text.dark}]`}
                                    >
                                        {featureData.title}
                                    </h3>
                                    <p
                                        className={`leading-relaxed text-[${colors.text.muted}] dark:text-[${colors.text.darkMuted}]`}
                                    >
                                        {featureData.description}
                                    </p>
                                </article>
                            );
                        })}
                    </div>
                </div>
            </div>
        </section>
    );
}
