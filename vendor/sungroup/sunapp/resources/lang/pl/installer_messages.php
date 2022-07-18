<?php

return [

    /**
     *
     * Shared translations.
     *
     */
    'title' => 'Instalator',
    'next' => 'Następny krok',
    'back' => 'Poprzedni',
    'finish' => 'Instaluj',
    'forms' => [
        'errorTitle' => 'Wystąpiły następujące błędy:',
    ],

    /**
     *
     * Home page translations.
     *
     */
    'welcome' => [
        'templateTitle' => 'Witamy',
        'title' => 'Instalator',
        'message' => 'Prosta instalacja i konfiguracja.',
        'next' => 'Sprawdź wymagania',
    ],

    /**
     *
     * Requirements page translations.
     *
     */
    'requirements' => [
        'templateTitle' => 'Krok 1 | Wymagania serwera',
        'title' => 'Wymagania serwera',
        'next' => 'Sprawdź uprawnienia',
    ],

    /**
     *
     * Permissions page translations.
     *
     */
    'permissions' => [
        'templateTitle' => 'Krok 2 | Uprawnienia',
        'title' => 'Uprawnienia',
        'next' => 'Skonfiguruj środowisko',
    ],

    /**
     *
     * Environment page translations.
     *
     */
    'environment' => [
        'menu' => [
            'templateTitle' => 'Krok 3 | Ustawienia środowiska',
            'title' => 'Ustawienia środowiska',
            'desc' => 'Wybierz sposób konfiguracji pliku <code>.env</code> aplikacji.',
            'wizard-button' => 'Kreator z przewodnikiem',
            'classic-button' => 'Klasyczny edytor tekstu',
        ],
        'wizard' => [
            'templateTitle' => 'Krok 3 | Ustawienia środowiska | Kreator z przewodnikiem',
            'title' => 'Kreator pliku <code>.env</code>',
            'tabs' => [
                'environment' => 'Środowisko',
                'database' => 'Baza danych',
                'application' => 'Aplikacja'
            ],
            'form' => [
                'name_required' => 'Wymagana jest nazwa środowiska.',
                'app_name_label' => 'Nazwa aplikacji',
                'app_name_placeholder' => 'Nazwa aplikacji',
                'app_environment_label' => 'Środowisko aplikacji',
                'app_environment_label_local' => 'Lokalna',
                'app_environment_label_developement' => 'Deweloperskie',
                'app_environment_label_qa' => 'Qa',
                'app_environment_label_production' => 'Produkcyjne',
                'app_environment_label_other' => 'Inne',
                'app_environment_placeholder_other' => 'Wprowadź swoje środownisko...',
                'app_debug_label' => 'Debugowanie aplikacji',
                'app_debug_label_true' => 'Tak',
                'app_debug_label_false' => 'Nie',
                'app_log_level_label' => 'Poziom logowania aplikacji',
                'app_log_level_label_debug' => 'debug',
                'app_log_level_label_info' => 'info',
                'app_log_level_label_notice' => 'notice',
                'app_log_level_label_warning' => 'warning',
                'app_log_level_label_error' => 'error',
                'app_log_level_label_critical' => 'critical',
                'app_log_level_label_alert' => 'alert',
                'app_log_level_label_emergency' => 'emergency',
                'app_url_label' => 'URL aplikacji',
                'app_url_placeholder' => 'URL aplikacji',
                'db_connection_label' => 'Połączenie z bazą danych',
                'db_connection_label_mysql' => 'mysql',
                'db_connection_label_sqlite' => 'sqlite',
                'db_connection_label_pgsql' => 'pgsql',
                'db_connection_label_sqlsrv' => 'sqlsrv',
                'db_host_label' => 'Host bazy danych',
                'db_host_placeholder' => 'Host bazy danych',
                'db_port_label' => 'Port bazy danych',
                'db_port_placeholder' => 'Port bazy danych',
                'db_name_label' => 'Nazwa bazy danych',
                'db_name_placeholder' => 'Nazwa bazy danych',
                'db_username_label' => 'Nazwa użytkownika bazy danych',
                'db_username_placeholder' => 'Nazwa użytkownika bazy danych',
                'db_password_label' => 'Hasło bazy danych',
                'db_password_placeholder' => 'Hasło bazy danych',

                'app_tabs' => [
                    'more_info' => 'Więcej informacji',
                    'broadcasting_title' => 'Nadawanie, buforowanie, sesja i kolejki',
                    'broadcasting_label' => 'Sterownik nadwania',
                    'broadcasting_placeholder' => 'Sterownik nadawania',
                    'cache_label' => 'Sterownik pamięci podręcznej',
                    'cache_placeholder' => 'Sterownik pamięci podręcznej',
                    'session_label' => 'Sterownik sesji',
                    'session_placeholder' => 'Sterownik sesji',
                    'queue_label' => 'Sterownik kolejki',
                    'queue_placeholder' => 'Sterownik kolejki',
                    'redis_label' => 'Sterownik Redis',
                    'redis_host' => 'Host Redis',
                    'redis_password' => 'Hasło Redis',
                    'redis_port' => 'Port Redis',

                    'mail_label' => 'Poczta',
                    'mail_driver_label' => 'Sterownik poczty',
                    'mail_driver_placeholder' => 'Sterownik poczty',
                    'mail_host_label' => 'Host poczty',
                    'mail_host_placeholder' => 'Host poczty',
                    'mail_port_label' => 'Port poczty',
                    'mail_port_placeholder' => 'Port poczty',
                    'mail_username_label' => 'Nazwa użytkownika poczty',
                    'mail_username_placeholder' => 'Nazwa użytkownika poczty',
                    'mail_password_label' => 'Hasło do poczty',
                    'mail_password_placeholder' => 'Hasło do poczty',
                    'mail_encryption_label' => 'Szyfrowanie poczty',
                    'mail_encryption_placeholder' => 'Szyfrowanie poczty',

                    'pusher_label' => 'Pusher',
                    'pusher_app_id_label' => 'Pusher App Id',
                    'pusher_app_id_palceholder' => 'Pusher App Id',
                    'pusher_app_key_label' => 'Pusher App Key',
                    'pusher_app_key_palceholder' => 'Pusher App Key',
                    'pusher_app_secret_label' => 'Pusher App Secret',
                    'pusher_app_secret_palceholder' => 'Pusher App Secret',
                ],
                'buttons' => [
                    'setup_database' => 'Ustawienia bazy danych',
                    'setup_application' => 'Ustawienia aplikacji',
                    'install' => 'Instaluj',
                ],
            ],
        ],
        'classic' => [
            'templateTitle' => 'Krok 3 | Ustawienia środowiska | Klasyczny edytor tekstu',
            'title' => 'Klasyczny edytor tekstu',
            'save' => 'Zapisz plik .env',
            'back' => 'Użyj kreatora',
            'install' => 'Zapisz i zainstaluj',
        ],
        'success' => 'Twój plik .env z ustawieniami został zapisany.',
        'errors' => 'Nie można zapisać pliku .env, Utwórz go ręcznie.',
    ],

    'install' => 'Instlacja',

    /**
     *
     * Installed Log translations.
     *
     */
    'installed' => [
        'success_log_message' => 'Aplikacja pomyślnie zainstalowana ',
    ],

    /**
     *
     * Final page translations.
     *
     */
    'final' => [
        'title' => 'Instalacja zakończona',
        'templateTitle' => 'Instalacja zakończona',
        'finished' => 'Aplikacja została pomyślnie zainstalowana.',
        'migration' => 'Migracje i wsad - wyjście konsoli:',
        'console' => 'Wyjście konsoli:',
        'log' => 'Wpis dziennika instalacji:',
        'env' => 'Końcowy plik .env:',
        'exit' => 'Kliknij tutaj, aby wyjść',
    ],

    /**
     *
     * Update specific translations
     *
     */
    'updater' => [
        /**
         *
         * Shared translations.
         *
         */
        'title' => 'Aktualizator',

        /**
         *
         * Welcome page translations for update feature.
         *
         */
        'welcome' => [
            'title' => 'Witamy w aktualizacji',
            'message' => 'Witamy w kreatorze aktualizacji.',
        ],

        /**
         *
         * Welcome page translations for update feature.
         *
         */
        'overview' => [
            'title' => 'Przegląd',
            'message' => 'Jest 1 aktualizacja. Są tam :number aktualizacje.',
            'install_updates' => "Zainstaluj aktualizacje"
        ],

        /**
         *
         * Final page translations.
         *
         */
        'final' => [
            'title' => 'Skończone',
            'finished' => 'Baza danych aplikacji została pomyślnie zaktualizowana.',
            'exit' => 'Kliknij tutaj, aby wyjść',
        ],

        'log' => [
            'success_message' => 'Aplikacja pomyślnie zaktualizowana ',
        ],
    ],
];
