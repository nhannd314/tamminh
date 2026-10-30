<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    'sepay' => [
        // Khoá API cấu hình ở SePay > Webhook > Chứng thực "API Key" (gửi qua header Authorization: Apikey …).
        'webhook_key' => env('SEPAY_WEBHOOK_KEY'),
        'qr_url' => 'https://qr.sepay.vn/img',
    ],

    'face_detector' => [
        'python' => env('FACE_DETECTOR_PYTHON', 'python'),
        'script' => resource_path('python/face_landmarks.py'),
        'timeout' => (int) env('FACE_DETECTOR_TIMEOUT', 60),
    ],

];
