<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'yookassa' => [
        'shop_id'    => env('YOOKASSA_SHOP_ID'),
        'secret_key' => env('YOOKASSA_SECRET_KEY'),
    ],

    'russian_post' => [
        'base_url' => env('RUSSIAN_POST_BASE_URL', 'https://otpravka-api.pochta.ru/'),
        'api_key'  => env('RUSSIAN_POST_API_KEY'),
        'login'    => env('RUSSIAN_POST_LOGIN'),
        'password' => env('RUSSIAN_POST_PASSWORD'),
    ],

    'sdek' => [
    'base_url' => env('SDEK_BASE_URL', 'https://api.cdek.ru/'),
    'api_key'  => env('SDEK_API_KEY'),
    'account'  => env('SDEK_ACCOUNT'),
],

];
