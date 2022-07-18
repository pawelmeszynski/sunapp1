<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'Pole musi zostać zaakceptowane.',
    'active_url' => 'Podano nieprawidłowy adres URL.',
    'after' => 'Podana data musi być późniejsza od :date.',
    'after_or_equal' => 'Podana data nie może być wcześniejsza niż :date.',
    'alpha' => 'Pole może zawierać jedynie litery.',
    'alpha_dash' => 'Pole może zawierać jedynie litery, cyfry i myślniki.',
    'alpha_num' => 'Pole może zawierać jedynie litery i cyfry.',
    'array' => 'Pole musi być tablicą.',
    'before' => 'Podana data musi być wcześniejsza od :date.',
    'before_or_equal' => 'Podana data nie może być późniejsza niż :date.',
    'between' => [
        'numeric' => 'Pole musi zawierać się w granicach :min - :max.',
        'file' => 'Pole musi zawierać się w granicach :min - :max kilobajtów.',
        'string' => 'Pole musi zawierać się w granicach :min - :max znaków.',
        'array' => 'Pole musi składać się z :min - :max elementów.',
    ],
    'boolean' => 'Pole musi mieć wartość prawda albo fałsz',
    'confirmed' => 'To potwierdzenie nie zgadza się.',
    'date' => 'Nie jest prawidłową datą.',
    'date_equals' => 'Podana data musi być równa :date.',
    'date_format' => 'Podana data nie jest w formacie :format.',
    'different' => 'To pole oraz :other muszą się różnić.',
    'digits' => 'Pole musi składać się z :digits cyfr.',
    'digits_between' => 'Pole musi mieć od :min do :max cyfr.',
    'dimensions' => 'Pole ma niepoprawne wymiary.',
    'distinct' => 'Pole ma zduplikowane wartości.',
    'email' => 'Format tego pola jest nieprawidłowy.',
    'exists' => 'Zaznaczone pole jest nieprawidłowe.',
    'file' => 'Pole musi być plikiem.',
    'filled' => 'Pole jest wymagane.',
    'gt' => [
        'numeric' => 'Wartość musi być większa niż :value.',
        'file' => 'Rozmiar pliku musi być większy niż :value kilobajtów.',
        'string' => 'Długość tekstu musi być dłuższa niż :value znaków.',
        'array' => 'Tablica musi mieć więcej niż :value elementów.',
    ],
    'gte' => [
        'numeric' => 'Wartość musi być większy lub równy :value.',
        'file' => 'Rozmiar pliku musi być większy lub równy :value kijobajtów.',
        'string' => 'Długość tekstu musi być dłuższa lub równa :value znaków.',
        'array' => 'Tablica musi mieć :value lub więcej elementów.',
    ],
    'image' => 'Pole musi być obrazkiem.',
    'in' => 'Zaznaczone pole jest nieprawidłowe.',
    'in_array' => 'Element nie znajduje się w :other.',
    'integer' => 'Wartość musi być liczbą całkowitą.',
    'ip' => 'Wartość musi być prawidłowym adresem IP.',
    'ipv4' => 'Wartość musi być prawidłowym adresem IPv4.',
    'ipv6' => 'Wartość musi być prawidłowym adresem IPv6.',
    'json' => 'Wartość musi być poprawnym ciągiem znaków JSON.',
    'lt' => [
        'numeric' => 'Wartość musi być mniejsza niż :value.',
        'file' => 'Rozmiar pliku musi być mniejszy niż :value kijobajtów.',
        'string' => 'Długość tekstu musi być krótsza niż :value znaków.',
        'array' => 'Tablica musi mieć mniej niż :value elementów.',
    ],
    'lte' => [
        'numeric' => 'Wartość musi być mniejsza lub równa :value.',
        'file' => 'Rozmiar pliku musi być mniejszy lub równy :value kijobajtów.',
        'string' => 'Długość tekstu musi być krótsza lub równa :value znaków.',
        'array' => 'Tablica musi mieć :value lub mniej elementów.',
    ],
    'max' => [
        'numeric' => 'Wartość nie może być większa niż :max.',
        'file' => 'Rozmiar pliku nie może być większy niż :max kilobajtów.',
        'string' => 'Długość tekstu nie powinna być dłuższa niż :max znaków.',
        'array' => 'Tablica nie może mieć więcej niż :max elementów.',
    ],
    'mimes' => 'Plik musi być typu :values.',
    'mimetypes' => 'Plik musi być jednym z typu :values.',
    'min' => [
        'numeric' => 'Wartość musi być nie mniejsza od :min.',
        'file' => 'Rozmiar pliku musi mieć przynajmniej :min kilobajtów.',
        'string' => 'Długość tekstu musi mieć przynajmniej :min znaków.',
        'array' => 'Tablica musi mieć przynajmniej :min elementów.',
    ],
    'not_in' => 'Zaznaczone pole jest nieprawidłowe.',
    'not_regex' => 'Format pola jest nieprawidłowy.',
    'numeric' => 'Wartość musi być liczbą.',
    'present' => 'Pole musi być obecne.',
    'regex' => 'Format pola jest nieprawidłowy.',
    'required' => 'Pole jest wymagane.',
    'required_if' => 'Pole jest wymagane gdy :other jest :value.',
    'required_unless' => 'Pole jest wymagane jeżeli :other nie znajduje się w :values.',
    'required_with' => 'Pole jest wymagane gdy :values jest obecny.',
    'required_with_all' => 'Pole jest wymagane gdy :values jest obecny.',
    'required_without' => 'Pole jest wymagane gdy :values nie jest obecny.',
    'required_without_all' => 'Pole jest wymagane gdy żadne z :values nie są obecne.',
    'same' => 'To pole i :other muszą się zgadzać.',
    'size' => [
        'numeric' => 'Wartość musi mieć :size.',
        'file' => 'Rozmiar pliku musi mieć :size kilobajtów.',
        'string' => 'Długość tekstu musi mieć :size znaków.',
        'array' => 'Tablica musi zawierać :size elementów.',
    ],
    'starts_with' => 'Wartość musi się zaczynać jednym z wymienionych: :values',
    'string' => 'Pole musi być ciągiem znaków.',
    'timezone' => 'Wartość musi być prawidłową strefą czasową.',
    'unique' => 'Taki element już występuje.',
    'uploaded' => 'Nie udało się wgrać pliku.',
    'url' => 'Format adresu jest nieprawidłowy.',
    'uuid' => 'Pole musi być poprawnym identyfikatorem UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
