export type DashboardTranslations = {
    [key: string]: string;
};

export const dashboardTranslations: Record<string, DashboardTranslations> = {
    ru: {
        welcome: 'Для начала обучения необходимо выбрать язык',
        profile_settings: 'Настройки профиля',
        app_settings: 'Настройки приложения',
        personal_statistics: 'Персональная статистика',
        change_password: 'Изменить пароль',
        logout: 'Выйти из системы',
        add_word: 'Добавить слово',
    },
    en: {
        welcome: 'To start learning, select a language',
        profile_settings: 'Profile Settings',
        app_settings: 'App Settings',
        personal_statistics: 'Personal Statistics',
        change_password: 'Change Password',
        logout: 'Logout',
        add_word: 'Add Word',
    },
    de: {
        welcome: 'Um mit dem Lernen zu beginnen, wählen Sie eine Sprache',
        profile_settings: 'Profil-Einstellungen',
        app_settings: 'App-Einstellungen',
        personal_statistics: 'Persönliche Statistik',
        change_password: 'Passwort ändern',
        logout: 'Abmelden',
        add_word: 'Wort hinzufügen',
    },
};
