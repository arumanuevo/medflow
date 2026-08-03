<?php

return [
    /*
    |--------------------------------------------------------------------------
    | NoCaptcha Site Key
    |--------------------------------------------------------------------------
    |
    | This value is the site key provided by Google reCAPTCHA.
    |
    */
    'sitekey' => env('NOCAPTCHA_SITEKEY', ''),

    /*
    |--------------------------------------------------------------------------
    | NoCaptcha Secret Key
    |--------------------------------------------------------------------------
    |
    | This value is the secret key provided by Google reCAPTCHA.
    |
    */
    'secret' => env('NOCAPTCHA_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | NoCaptcha Options
    |--------------------------------------------------------------------------
    |
    | Here you can specify additional options for reCAPTCHA.
    |
    */
    'options' => [
        'timeout' => 30,
        'debug' => false,
    ],
];