export interface TranslationStructure {
    nav: {
        login: string;
        register: string;
        dashboard: string;
        logout: string;
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
    words: {
        page_title: string;
        page_description: string;
        add_new_word: string;
        original_label: string;
        original_placeholder: string;
        translated_label: string;
        translated_placeholder: string;
        language_label: string;
        language_placeholder: string;
        ai_translate: string;
        translating: string;
        save_word: string;
        saving: string;
        cancel: string;
        go_to_word: string;
        add_another: string;
        success_title: string;
        success_description: string;
        word_exists: string;
        translation_error: string;
    };
    profile: {
        title: string;
        user_info: string;
        user_id: string;
        name: string;
        user_name: string;
        email: string;
        created_at: string;
        updated_at: string;
        api_keys: string;
        api_keys_description: string;
        no_tokens: string;
        add_token: string;
        create_token: string;
        create_token_title: string;
        token_name: string;
        token_name_placeholder: string;
        token: string;
        token_created_title: string;
        token_created_description: string;
        cancel: string;
        save: string;
        creating: string;
        done: string;
        token_created: string;
        copy_token: string;
        token_copied: string;
        delete_token: string;
        delete_token_confirm: string;
        delete_confirmation: string;
        last_used: string;
        never_used: string;
        created: string;
    };
    auth: {
        login: {
            title: string;
            description: string;
            email: string;
            email_placeholder: string;
            password: string;
            password_placeholder: string;
            remember_me: string;
            submit: string;
            forgot_password: string;
            no_account: string;
            sign_up: string;
        };
        forgot_password: {
            title: string;
            description: string;
            email: string;
            email_placeholder: string;
            submit: string;
            return_to_login: string;
            log_in: string;
        };
        reset_password: {
            title: string;
            description: string;
            password: string;
            password_placeholder: string;
            password_confirmation: string;
            password_confirmation_placeholder: string;
            submit: string;
        };
        register: {
            title: string;
            description: string;
            name: string;
            name_placeholder: string;
            email: string;
            email_placeholder: string;
            password: string;
            password_placeholder: string;
            password_confirmation: string;
            password_confirmation_placeholder: string;
            submit: string;
            has_account: string;
            log_in: string;
        };
        verify_email: {
            title: string;
            description: string;
            submit: string;
            resend: string;
        };
        common: {
            or: string;
            back: string;
            next: string;
            cancel: string;
            save: string;
            done: string;
            delete: string;
            edit: string;
            loading: string;
            success: string;
            error: string;
            required: string;
            invalid_email: string;
            password_min_length: string;
            password_mismatch: string;
        };
    };
}

export type Translations = TranslationStructure;
