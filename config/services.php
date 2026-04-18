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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'ghostscript' => [
        'binary' => env('GS_BINARY'),
    ],

    /*
    | Kompres PDF tanpa binary server (shared hosting): rewrite lewat FPDI+TCPDF.
    | Ghostscript (GS_BINARY) dipakai dulu jika ada; jika tidak, fallback ini.
    */
    'pdf' => [
        'php_rewrite' => [
            'enabled' => (bool) env('PDF_COMPRESS_PHP_ENABLED', true),
            'max_pages' => (int) env('PDF_COMPRESS_PHP_MAX_PAGES', 50),
            'timeout_seconds' => (int) env('PDF_COMPRESS_PHP_TIMEOUT', 60),
        ],
    ],

];
