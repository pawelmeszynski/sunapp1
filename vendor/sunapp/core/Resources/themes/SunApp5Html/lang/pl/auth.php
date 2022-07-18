<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'failed' => 'Błędny login lub hasło.',
    'ldap_failed' => 'Błąd autoryzacji LDAP.',
    'throttle' => 'Za dużo nieudanych prób logowania. Proszę spróbować za :seconds sekund.',
    'email' => 'Adres E-Mail',
    'password' => 'Hasło',
    'confirm_password' => 'Potwierdź hasło',
    'remember_me' => 'Zapamiętaj mnie',
    'remember_device' => 'Zapamietaj urządzenie',
    'forgot_password' => 'Zapomniałeś hasła?',
    'register' => 'Rejestruj',
    'login' => 'Zaloguj',
    'logout' => 'Wyloguj',
    'login_message' => 'Witamy, zaloguj się do swojego konta.',
    'create_account' => 'Utwórz konto',
    'create_account_message' => 'Wypełnij poniższy formularz, aby utworzyć nowe konto.',
    'name' => 'Nazwa',
    'term_info' => 'Akceptuję regulamin.',
    'back_to_login' => 'Wróć do logowania',
    'reset_password' => 'Zresetuj hasło',
    'reset_password_message' => 'Proszę podać adres email, na który zostało założone konto',
    'send_password_link' => 'Wyślij link resetowania hasła',
    'verify_email' => 'Zweryfikuj swój adres email',
    'verify_link_sent' => 'Świeży link weryfikacyjny został wysłany na Twój adres e-mail.',
    'verify_email_message' => 'Przed kontynuowaniem sprawdź adres e-mail pod kątem linku weryfikacyjnego.',
    'not_receive_email' => 'Jeśli nie otrzymałeś wiadomości e-mail',
    'click_to_resend' => 'kliknij tutaj, aby poprosić o kolejną',
    'logged_in' => 'Jesteś zalogowany!',
    /*'verify_2fa_google' => 'Skonfiguruj Google Authenticator!',
    'verify_2fa_google_login' => 'Wpisz jednorazowe hasło (2FA)',
    'verify_2fa_google_text1' => 'Skonfiguruj uwierzytelnianie 2FA, skanując poniższy kod kreskowy.',
    'verify_2fa_google_text2' => 'Alternatywnie możesz użyć kodu',
    'verify_2fa_warning' => 'Przed kontynuowaniem musisz skonfigurować aplikację Google Authenticator.
        W przeciwnym razie nie będziesz mógł się zalogować.',
    'verify_2fa_end_registration' => 'Dokończ rejestrację',*/

    'verify_2fa_app_register_instruction' =>
        'Użyj swojej aplikacji do autoryzacji (Google Authenticator, Microsoft Authenticator) i zeskanuj kod QR.',
    'verify_2fa_app_code_instruction' =>
        'Użyj swojej aplikacji do autoryzacji (Google Authenticator, Microsoft Authenticator) i wpisz hasło.',
    'verify_2fa_authentication' => 'Autoryzacja dwuetapowa',
    'verify_2fa_alternate_code' => 'lub użyj kodu',
    'verify_2fa_one_time_password' => 'Jednorazowe hasło'
];
