export interface TranslationStructure {
    nav: {
        login: string;
        register: string;
        dashboard: string;
    };
    hero: {
        title: string;
        subtitle: string;
        cta: string;
    };
    features: {
        title: string;
        subtitle: string;
        items: {
            flashcards: {
                title: string;
                description: string;
            };
            aiExamples: {
                title: string;
                description: string;
            };
            multiPlatform: {
                title: string;
                description: string;
            };
            statistics: {
                title: string;
                description: string;
            };
        };
    };
    screenshots: {
        title: string;
        subtitle: string;
    };
    footer: {
        tagline: string;
        rights: string;
    };
}

export type Translations = TranslationStructure;
