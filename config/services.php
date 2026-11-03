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

    'google' => [
        // Google Cloud Console > APIs & Services > Credentials > OAuth client ID (Web application).
        // Authorized redirect URI phải khớp tuyệt đối: APP_URL + /dang-nhap/google/callback.
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', '/dang-nhap/google/callback'),
    ],

    'sepay' => [
        // Tài khoản nhận tiền (đã liên kết với SePay); `bank` là mã ngân hàng theo SePay: MBBank, Vietcombank, ACB…
        'bank' => env('SEPAY_BANK'),
        'account_number' => env('SEPAY_ACCOUNT_NUMBER'),
        'account_name' => env('SEPAY_ACCOUNT_NAME'),
        // Nội dung chuyển khoản; {code} là mã đơn - phải khớp tiền tố mã thanh toán cấu hình trên SePay.
        'transfer_content' => env('SEPAY_TRANSFER_CONTENT', '{code}'),
        'note' => 'Vui lòng giữ nguyên nội dung chuyển khoản. Hệ thống tự động mở khóa trong vòng 1-2 phút sau khi nhận được tiền.',
        // Khoá API cấu hình ở SePay > Webhook > Chứng thực "API Key" (gửi qua header Authorization: Apikey …).
        'webhook_key' => env('SEPAY_WEBHOOK_KEY'),
        'qr_url' => 'https://qr.sepay.vn/img',
    ],

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        // Alias luôn trỏ tới bản Flash hiện hành; tên cố định (VD: gemini-2.5-flash) có thể bị Google ngừng cho tài khoản mới.
        'model' => env('GEMINI_MODEL', 'gemini-flash-latest'),
        'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
        'timeout' => (int) env('GEMINI_TIMEOUT', 120),
    ],

    'face_detector' => [
        'python' => env('FACE_DETECTOR_PYTHON', 'python'),
        'script' => resource_path('python/face_landmarks.py'),
        'timeout' => (int) env('FACE_DETECTOR_TIMEOUT', 60),
    ],

];
