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
        change_password: 'Passwort ändern',
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
    // Password
    password: {
        title: 'Passwort ändern',
        subtitle: 'Geben Sie Ihr aktuelles Passwort und ein neues Passwort ein',
        current_password: 'Aktuelles Passwort',
        current_password_placeholder: 'Aktuelles Passwort eingeben',
        new_password: 'Neues Passwort',
        new_password_placeholder: 'Neues Passwort eingeben',
        confirm_password: 'Passwort bestätigen',
        confirm_password_placeholder: 'Neues Passwort wiederholen',
        save_button: 'Passwort speichern',
        saving: 'Speichern...',
        success_message: 'Passwort erfolgreich geändert',
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
        // Study Page
        study_title: 'Übersetzen Sie das Wort',
        study_subtitle:
            'Raten Sie die Wortübersetzung. Zum Überprüfen drehen Sie die Karte. Wenn Sie das Wort gelernt haben, klicken Sie auf die Schaltfläche Gelernt. Um zurückzugehen, klicken Sie auf die Schaltfläche Vorherige.',
        share_word: 'Wort teilen',
        delete: 'Löschen',
        add_to_favorites: 'Zu Favoriten hinzufügen',
        remove_from_favorites: 'Aus Favoriten entfernen',
        mark_learned: 'Gelernt',
        flip: 'Umdrehen',
        previous: 'Vorherige',
        next: 'Nächste',
        show_example: 'Beispiel zeigen',
        keyboard_shortcuts: 'Tastaturkürzel',
        shortcuts_description:
            'Verwenden Sie die Tastatur zur schnellen Steuerung',
        shortcut_enter: 'Eingabe - Als gelernt markieren',
        shortcut_space: 'Leertaste - Karte umdrehen',
        shortcut_backspace: 'Rücktaste - Vorheriges Wort',
        shortcut_delete: 'Entf - Wort löschen',
        shortcut_arrow_left: '← - Vorheriges Wort',
        shortcut_arrow_right: '→ - Nächstes Wort',
        share_modal_title: 'Benutzer auswählen',
        share_modal_subtitle:
            'Um fortzufahren, wählen Sie einen Benutzer aus und klicken Sie auf Weiter',
        share: 'Teilen',
        close: 'Schließen',
        word_deleted: 'Wort erfolgreich gelöscht',
        word_shared: 'Wort erfolgreich geteilt',
        word_learned: 'Wort als gelernt markiert',
        word_starred: 'Wort zu Favoriten hinzugefügt',
        word_unstarred: 'Wort aus Favoriten entfernt',
        error_deleting: 'Fehler beim Löschen des Wortes',
        error_sharing: 'Fehler beim Teilen des Wortes',
        error_marking_learned: 'Fehler beim Markieren des Wortes als gelernt',
        error_toggling_starred: 'Fehler beim Ändern des Favoritenstatus',
        premium_required: 'Verfügbar für Premium-Benutzer',
        examples_title: 'Verwendungsbeispiele',
        examples_original: 'Original',
        examples_translated: 'Übersetzung',
        back_to_word: 'Zurück zum Wort',
    },
    // Settings
    settings: {
        title: 'App-Einstellungen',
        subtitle: 'Passen Sie Ihre App-Anzeige und Ihr Verhalten an',
        save: 'Speichern',
        saving: 'Speichern...',
        updated_successfully: 'Einstellungen erfolgreich aktualisiert',
        import_words: 'Wörter importieren',
        import_description:
            'Zu faul, Wörter manuell hinzuzufügen? Importieren Sie die am häufigsten verwendeten Wörter für Ihre Standardsprache',
        importing: 'Importiere...',
        success_title: 'Erfolg',
        success_message: 'Einstellungen erfolgreich gespeichert',
        error_title: 'Fehler',
        error_message:
            'Beim Speichern der Einstellungen ist ein Fehler aufgetreten',
        import_success_title: 'Import abgeschlossen',
        import_success_message: 'Wörter erfolgreich importiert',
        import_error_message:
            'Beim Importieren der Wörter ist ein Fehler aufgetreten',
        general: 'Allgemein',
        general_description: 'Grundlegende App-Einstellungen',
        visibility: 'Sichtbarkeit',
        visibility_description: 'Wortanzeige konfigurieren',
        sorting: 'Sortierung',
        sorting_description: 'Wortreihenfolge konfigurieren',
        fields: {
            paginate: 'Karten pro Seite',
            default_language: 'Lernsprache',
            show_starred: 'Nur Favoriten anzeigen',
            known_enabled: 'Gelernte anzeigen',
            latest_first: 'Nach kürzlich hinzugefügten filtern',
            show_imported: 'Importierte Wörter anzeigen',
            show_shared: 'Geteilte Wörter anzeigen',
            fresh_first: 'Frischeste zuerst',
        },
    },
    // Import
    import: {
        words_imported: '{count} Wörter erfolgreich importiert',
        no_words_imported: 'Keine neuen Wörter zum Importieren',
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
