import type { TranslationStructure } from './types';

export const translations: TranslationStructure = {
    // Navigation
    nav: {
        login: 'Anmelden',
        register: 'Registrieren',
        dashboard: 'Dashboard',
        logout: 'Abmelden',
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
    // Profile
    profile: {
        title: 'Profil',
        user_info: 'Benutzerinformationen',
        user_id: 'Benutzer-ID',
        name: 'Name',
        user_name: 'Name',
        email: 'E-Mail',
        created_at: 'Erstellt am',
        updated_at: 'Aktualisiert am',
        api_keys: 'API-Schlüssel',
        api_keys_description: 'Verwalten Sie Ihre API-Zugriffstoken',
        no_tokens: 'Sie haben noch keine Token',
        add_token: 'Neues Token hinzufügen',
        create_token: 'Token erstellen',
        create_token_title: 'Neues Token erstellen',
        create_token_description:
            'Geben Sie einen Namen für Ihr neues API-Token ein',
        token_name: 'Token-Name',
        token_name_placeholder: 'z. B. Mobile App',
        token: 'Token',
        cancel: 'Abbrechen',
        save: 'Speichern',
        creating: 'Erstelle...',
        done: 'Fertig',
        token_created: 'Token erstellt',
        copy_token: 'Kopieren',
        token_copied: 'Kopiert!',
        delete_token: 'Löschen',
        delete: 'Löschen',
        delete_token_title: 'API-Token löschen',
        delete_token_description:
            'Möchten Sie dieses Token wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden.',
        delete_token_confirm: 'Möchten Sie dieses Token wirklich löschen?',
        delete_confirmation: 'Möchten Sie wirklich löschen?',
        last_used: 'Zuletzt verwendet',
        never_used: 'Nie',
        created: 'Erstellt',
        token_warning:
            'Speichern Sie dieses Token jetzt. Es wird nicht mehr angezeigt.',
        create: 'Erstellen',
    },
    // Words
    words: {
        page_title: 'Wort hinzufügen',
        page_description:
            'Füllen Sie das Formular aus, um ein neues Wort zu Ihrem Wortschatz hinzuzufügen',
        add_new_word: 'Neues Wort hinzufügen',
        original_label: 'Originalwort',
        original_placeholder: 'Wort eingeben',
        translated_label: 'Übersetzung',
        translated_placeholder: 'Übersetzung eingeben oder KI verwenden',
        language_label: 'Sprache',
        language_placeholder: 'Sprache auswählen',
        ai_translate: 'KI',
        translating: 'Übersetzung...',
        save_word: 'Wort speichern',
        saving: 'Speichern...',
        cancel: 'Abbrechen',
        go_to_word: 'Zur Karte',
        add_another: 'Weiteres Wort hinzufügen',
        success_title: 'Wort erfolgreich erstellt!',
        success_description: 'ID:',
        word_exists:
            'Dieses Wort existiert bereits in Ihrem Wortschatz für die ausgewählte Sprache.',
        translation_error:
            'Übersetzung konnte nicht abgerufen werden. Bitte versuchen Sie es erneut.',
    },
    // Auth
    auth: {
        login: {
            title: 'Melden Sie sich an',
            description:
                'Geben Sie Ihre E-Mail-Adresse und Ihr Passwort unten ein',
            email: 'E-Mail-Adresse',
            email_placeholder: 'email@example.com',
            password: 'Passwort',
            password_placeholder: 'Passwort',
            remember_me: 'Angemeldet bleiben',
            submit: 'Anmelden',
            forgot_password: 'Passwort vergessen?',
            no_account: 'Haben Sie kein Konto?',
            sign_up: 'Registrieren',
        },
        forgot_password: {
            title: 'Passwort vergessen',
            description:
                'Geben Sie Ihre E-Mail-Adresse ein, um einen Link zum Zurücksetzen des Passworts zu erhalten',
            email: 'E-Mail-Adresse',
            email_placeholder: 'email@example.com',
            submit: 'Link zum Zurücksetzen senden',
            return_to_login: 'Oder zurück zur',
            log_in: 'Anmeldung',
        },
        reset_password: {
            title: 'Passwort zurücksetzen',
            description: 'Geben Sie Ihr neues Passwort unten ein',
            password: 'Neues Passwort',
            password_placeholder: 'Neues Passwort',
            password_confirmation: 'Passwort bestätigen',
            password_confirmation_placeholder: 'Passwort bestätigen',
            submit: 'Passwort zurücksetzen',
        },
        register: {
            title: 'Konto erstellen',
            description:
                'Geben Sie Ihre Daten unten ein, um ein Konto zu erstellen',
            name: 'Vollständiger Name',
            name_placeholder: 'Max Mustermann',
            email: 'E-Mail-Adresse',
            email_placeholder: 'email@example.com',
            password: 'Passwort',
            password_placeholder: 'Passwort',
            password_confirmation: 'Passwort bestätigen',
            password_confirmation_placeholder: 'Passwort bestätigen',
            submit: 'Konto erstellen',
            has_account: 'Haben Sie bereits ein Konto?',
            log_in: 'Anmelden',
        },
        verify_email: {
            title: 'E-Mail bestätigen',
            description:
                'Bitte klicken Sie auf die Schaltfläche unten, um Ihre E-Mail-Adresse zu bestätigen',
            submit: 'E-Mail bestätigen',
            resend: 'Bestätigungs-E-Mail erneut senden',
        },
        common: {
            or: 'oder',
            back: 'Zurück',
            next: 'Weiter',
            cancel: 'Abbrechen',
            save: 'Speichern',
            done: 'Fertig',
            delete: 'Löschen',
            edit: 'Bearbeiten',
            loading: 'Wird geladen...',
            success: 'Erfolg',
            error: 'Fehler',
            required: 'Dieses Feld ist erforderlich',
            invalid_email: 'Bitte geben Sie eine gültige E-Mail-Adresse ein',
            password_min_length:
                'Das Passwort muss mindestens 8 Zeichen lang sein',
            password_mismatch: 'Die Passwörter stimmen nicht überein',
        },
    },
} as const;
