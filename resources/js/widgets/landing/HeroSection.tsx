import { useTranslation } from '@/shared/i18n/useTranslation';
import { colors } from '@/shared/ui/colors';
import { Link } from '@inertiajs/react';

export function HeroSection() {
    const t = useTranslation();

    return (
        <section
            className={`relative overflow-hidden bg-gradient-to-b from-[${colors.accent.gradientStart}] to-[${colors.accent.gradientEnd}] py-20 lg:py-32 dark:from-[${colors.accent.darkGradientStart}] dark:to-[${colors.accent.darkGradientEnd}]`}
        >
            <div className="container mx-auto px-4 lg:px-8">
                <div className="mx-auto max-w-4xl text-center">
                    <h1
                        className={`mb-6 text-4xl leading-tight font-bold text-[${colors.text.DEFAULT}] lg:text-6xl dark:text-[${colors.text.dark}]`}
                    >
                        {t.hero.title}
                    </h1>
                    <p
                        className={`mb-10 text-lg leading-relaxed text-[${colors.text.muted}] lg:text-xl dark:text-[${colors.text.darkMuted}]`}
                    >
                        {t.hero.subtitle}
                    </p>
                    <div className="flex flex-col items-center gap-4 sm:flex-row sm:justify-center">
                        <Link
                            href="/register"
                            className={`inline-flex items-center justify-center rounded-sm bg-[${colors.primary.DEFAULT}] px-8 py-3 text-sm font-semibold text-white transition-colors hover:bg-[${colors.primary.hover}] focus:ring-2 focus:outline-none focus:ring-[${colors.primary.DEFAULT}] focus:ring-offset-2 dark:bg-[${colors.primary.dark}] dark:hover:bg-[${colors.primary.darkHover}] dark:focus:ring-offset-[${colors.background.dark}]`}
                        >
                            {t.hero.cta}
                        </Link>
                    </div>
                </div>

                {/* Logo/Brand Image */}
                <div className="mt-16 flex justify-center">
                    <img
                        src="/images/easywords-full.svg"
                        alt="EasyWords Logo"
                        className="h-auto w-full max-w-2xl"
                    />
                </div>
            </div>

            {/* Decorative Elements */}
            <div
                className="absolute inset-0 -z-10 overflow-hidden"
                aria-hidden="true"
            >
                <div
                    className={`absolute -top-40 -right-40 h-96 w-96 rounded-full bg-[${colors.primary.DEFAULT}]/5 dark:bg-[${colors.primary.dark}]/10`}
                />
                <div
                    className={`absolute -bottom-40 -left-40 h-96 w-96 rounded-full bg-[${colors.primary.DEFAULT}]/5 dark:bg-[${colors.primary.dark}]/10`}
                />
            </div>
        </section>
    );
}
