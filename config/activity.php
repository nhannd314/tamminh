<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Thông báo hoạt động giả
    |--------------------------------------------------------------------------
    |
    | Khi bật, lệnh `activity:fake` (chạy mỗi phút qua scheduler) sẽ phát một thông báo ngẫu nhiên
    | từ database/data/fake-activities.php sau mỗi khoảng min–max phút.
    |
    */

    'fake_enabled' => env('ACTIVITY_FAKE_ENABLED', true),

    'fake_interval_minutes' => [
        'min' => (int) env('ACTIVITY_FAKE_MIN_MINUTES', 2),
        'max' => (int) env('ACTIVITY_FAKE_MAX_MINUTES', 6),
    ],

];
