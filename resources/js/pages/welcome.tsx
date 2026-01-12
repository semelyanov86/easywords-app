import { LanguageProvider } from '@/shared/i18n/LanguageContext';
import { colors } from '@/shared/ui/colors';
import { FeaturesSection } from '@/widgets/landing/FeaturesSection';
import { Footer } from '@/widgets/landing/Footer';
import { HeroSection } from '@/widgets/landing/HeroSection';
import { Navigation } from '@/widgets/landing/Navigation';
import { ScreenshotsSection } from '@/widgets/landing/ScreenshotsSection';
import { Head } from '@inertiajs/react';

export default function Welcome({
    canRegister = true,
}: {
    canRegister?: boolean;
}) {
    return (
        <LanguageProvider>
            <Head title="EasyWords - learn words easily">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600"
                    rel="stylesheet"
                />
            </Head>

            <div
                className={`min-h-screen bg-[${colors.background.DEFAULT}] dark:bg-[${colors.background.dark}]`}
            >
                <Navigation canRegister={canRegister} />
                <main>
                    <HeroSection />
                    <FeaturesSection />
                    <ScreenshotsSection />
                </main>
                <Footer />
            </div>
        </LanguageProvider>
    );
}
