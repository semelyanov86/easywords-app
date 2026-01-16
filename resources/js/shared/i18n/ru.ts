import type { TranslationStructure } from './types';

export const translations: TranslationStructure = {
    // Navigation
    nav: {
        login: 'Войти',
        register: 'Регистрация',
        dashboard: 'Дашборд',
        logout: 'Выйти',
    },
    // Hero Section
    hero: {
        title: 'Овладейте новым словарным запасом с легкостью',
        subtitle:
            'Сохраняйте слова, учитесь с флеш-карточками и получайте примеры использования от ИИ. Ваше персонализированное обучение начинается здесь.',
        cta: 'Начать бесплатно',
    },
    // Features Section
    features: {
        title: 'Почему EasyWords?',
        subtitle:
            'Все необходимое для эффективного пополнения словарного запаса',
        items: {
            flashcards: {
                title: 'Умные флеш-карточки',
                description:
                    'Учитесь с интерактивными карточками, которые помогают запоминать слова через активное воспроизведение. Отслеживайте прогресс и сосредоточьтесь на словах, требующих больше практики.',
            },
            aiExamples: {
                title: 'Примеры от ИИ',
                description:
                    'Получайте примеры использования слов, сгенерированные ИИ с учетом контекста. Понимайте, как слова используются в реальных ситуациях, и учитесь правильному употреблению.',
            },
            multiPlatform: {
                title: 'Мультиплатформенный доступ',
                description:
                    'Доступ к вашему словарному запасу где угодно и когда угодно. Синхронизация между веб-приложением и мобильными устройствами для непрерывного обучения.',
            },
            statistics: {
                title: 'Отслеживание прогресса',
                description:
                    'Следите за своим обучением с подробной статистикой. Смотрите, сколько слов вы выучили, вашу серию и области для улучшения.',
            },
        },
    },
    // Screenshots Section
    screenshots: {
        title: 'EasyWords в действии',
        subtitle: 'Узнайте, как наша платформа помогает вам учиться эффективно',
    },
    // Footer
    footer: {
        tagline: 'Пополните свой словарный запас, одно слово за другим.',
        rights: 'Все права защищены.',
    },
    // Profile
    profile: {
        title: 'Профиль',
        user_info: 'Информация о пользователе',
        user_id: 'ID пользователя',
        name: 'Имя',
        user_name: 'Имя',
        email: 'Email',
        created_at: 'Дата создания',
        updated_at: 'Дата обновления',
        change_password: 'Изменить пароль',
        api_keys: 'API ключи',
        api_keys_description: 'Управляйте вашими токенами доступа к API',
        no_tokens: 'У вас пока нет токенов доступа',
        add_token: 'Добавить новый токен',
        create_token: 'Создать токен',
        create_token_title: 'Создать новый токен',
        create_token_description: 'Введите имя для вашего нового API токена',
        token_name: 'Название токена',
        token_name_placeholder: 'Например: Мобильное приложение',
        token: 'Токен',
        cancel: 'Отмена',
        save: 'Сохранить',
        creating: 'Создание...',
        done: 'Готово',
        token_created: 'Токен создан',
        copy_token: 'Скопировать',
        token_copied: 'Скопировано!',
        delete_token: 'Удалить',
        delete: 'Удалить',
        delete_token_title: 'Удалить API токен',
        delete_token_description:
            'Вы уверены, что хотите удалить этот токен? Это действие нельзя отменить.',
        delete_token_confirm: 'Вы уверены, что хотите удалить этот токен?',
        delete_confirmation: 'Вы уверены, что хотите удалить?',
        last_used: 'Последнее использование',
        never_used: 'Не использовался',
        created: 'Создан',
        token_warning:
            'Сохраните этот токен сейчас. Он больше не будет показан.',
        create: 'Создать',
    },
    // Password
    password: {
        title: 'Изменить пароль',
        subtitle: 'Введите ваш текущий пароль и новый пароль для изменения',
        current_password: 'Текущий пароль',
        current_password_placeholder: 'Введите текущий пароль',
        new_password: 'Новый пароль',
        new_password_placeholder: 'Введите новый пароль',
        confirm_password: 'Подтвердите пароль',
        confirm_password_placeholder: 'Повторите новый пароль',
        save_button: 'Сохранить пароль',
        saving: 'Сохранение...',
        success_message: 'Пароль успешно изменён',
    },
    // Words
    words: {
        page_title: 'Добавить слово',
        page_description:
            'Заполните форму для добавления нового слова в ваш словарь',
        add_new_word: 'Добавить новое слово',
        original_label: 'Оригинальное значение слова',
        original_placeholder: 'Введите слово',
        translated_label: 'Перевод слова',
        translated_placeholder: 'Введите перевод или используйте ИИ',
        language_label: 'Язык',
        language_placeholder: 'Выберите язык',
        ai_translate: 'ИИ',
        translating: 'Перевод...',
        save_word: 'Сохранить слово',
        saving: 'Сохранение...',
        cancel: 'Отмена',
        go_to_word: 'Перейти в карточку',
        add_another: 'Добавить еще слово',
        success_title: 'Слово создано успешно!',
        success_description: 'Идентификатор:',
        word_exists: 'Это слово уже есть в вашем словаре для выбранного языка.',
        translation_error: 'Не удалось получить перевод. Попробуйте снова.',
    },
    // Auth
    auth: {
        login: {
            title: 'Войдите в аккаунт',
            description: 'Введите ваш email и пароль ниже для входа',
            email: 'Адрес электронной почты',
            email_placeholder: 'email@example.com',
            password: 'Пароль',
            password_placeholder: 'Пароль',
            remember_me: 'Запомнить меня',
            submit: 'Войти',
            forgot_password: 'Забыли пароль?',
            no_account: 'Нет аккаунта?',
            sign_up: 'Зарегистрироваться',
        },
        forgot_password: {
            title: 'Забыли пароль',
            description:
                'Введите ваш email, чтобы получить ссылку для сброса пароля',
            email: 'Адрес электронной почты',
            email_placeholder: 'email@example.com',
            submit: 'Отправить ссылку для сброса',
            return_to_login: 'Или вернуться к',
            log_in: 'входу',
        },
        reset_password: {
            title: 'Сбросить пароль',
            description: 'Введите ваш новый пароль ниже',
            password: 'Новый пароль',
            password_placeholder: 'Новый пароль',
            password_confirmation: 'Подтвердить пароль',
            password_confirmation_placeholder: 'Подтвердите пароль',
            submit: 'Сбросить пароль',
        },
        register: {
            title: 'Создать аккаунт',
            description: 'Введите ваши данные ниже для создания аккаунта',
            name: 'Полное имя',
            name_placeholder: 'Иван Иванов',
            email: 'Адрес электронной почты',
            email_placeholder: 'email@example.com',
            password: 'Пароль',
            password_placeholder: 'Пароль',
            password_confirmation: 'Подтвердить пароль',
            password_confirmation_placeholder: 'Подтвердите пароль',
            submit: 'Создать аккаунт',
            has_account: 'Уже есть аккаунт?',
            log_in: 'Войти',
        },
        verify_email: {
            title: 'Подтвердить email',
            description:
                'Пожалуйста, нажмите кнопку ниже, чтобы подтвердить ваш адрес электронной почты',
            submit: 'Подтвердить email',
            resend: 'Отправить подтверждение повторно',
        },
        common: {
            or: 'или',
            back: 'Назад',
            next: 'Далее',
            cancel: 'Отмена',
            save: 'Сохранить',
            done: 'Готово',
            delete: 'Удалить',
            edit: 'Редактировать',
            loading: 'Загрузка...',
            success: 'Успешно',
            error: 'Ошибка',
            required: 'Это поле обязательно для заполнения',
            invalid_email:
                'Пожалуйста, введите действительный адрес электронной почты',
            password_min_length: 'Пароль должен содержать минимум 8 символов',
            password_mismatch: 'Пароли не совпадают',
        },
    },
} as const;
