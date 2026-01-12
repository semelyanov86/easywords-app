import type { TranslationStructure } from './types';

export const translations: TranslationStructure = {
    // Navigation
    nav: {
        login: 'Anmelden',
        register: 'Registrieren',
        dashboard: 'Dashboard',
    },
    // Hero Section
    hero: {
        title: 'Meistern Sie mühelos neuen Wortschatz',
        subtitle:
            'Speichern Sie Wörter, lernen Sie mit Karteikarten und erhalten Sie KI-generierte Beispiele. Ihre persönliche Reise zum Wortschatzaufbau beginnt hier.',
        cta: 'Kostenlos starten',
    },
    // Features Section
    features: {
        title: 'Warum EasyWords?',
        subtitle:
            'Alles, was Sie für einen effizienten Wortschatzaufbau brauchen',
        items: {
            flashcards: {
                title: 'Intelligente Karteikarten',
                description:
                    'Lernen Sie mit interaktiven Wendekarten, die Ihnen helfen, Wörter durch aktives Abrufen zu merken. Verfolgen Sie Ihren Fortschritt und konzentrieren Sie sich auf Wörter, die mehr Übung benötigen.',
            },
            aiExamples: {
                title: 'KI-gestützte Beispiele',
                description:
                    'Erhalten Sie kontextsensitive Verwendungsbeispiele, die von KI generiert wurden. Verstehen Sie, wie Wörter in realen Situationen verwendet werden, und lernen Sie den richtigen Gebrauch.',
            },
            multiPlatform: {
                title: 'Multiplattform-Zugriff',
                description:
                    'Greifen Sie überall und jederzeit auf Ihren Wortschatz zu. Nahtlose Synchronisation zwischen Web- und Mobilanwendungen für unterbrechungsfreies Lernen.',
            },
            statistics: {
                title: 'Fortschrittsverfolgung',
                description:
                    'Verfolgen Sie Ihre Lernreise mit detaillierter Statistik. Sehen Sie, wie viele Wörter Sie gelernt haben, Ihre Serie und Bereiche zur Verbesserung.',
            },
        },
    },
    // Screenshots Section
    screenshots: {
        title: 'EasyWords in Aktion',
        subtitle:
            'Entdecken Sie, wie unsere Plattform Ihnen hilft, effektiv zu lernen',
    },
    // Footer
    footer: {
        tagline: 'Bauen Sie Ihren Wortschatz auf, ein Wort nach dem anderen.',
        rights: 'Alle Rechte vorbehalten.',
    },
} as const;
