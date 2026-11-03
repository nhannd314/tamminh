<?php

namespace App\Support;

use App\Events\ActivityOccurred;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Throwable;

/**
 * Phát thông báo hoạt động qua Reverb. Lỗi phát (Reverb chưa chạy...) chỉ được ghi log,
 * không bao giờ làm hỏng thao tác của khách.
 */
class ActivityFeed
{
    public static function announce(string $kind, string $message): void
    {
        try {
            ActivityOccurred::dispatch($kind, $message);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    /**
     * Chỉ lộ tên gọi (từ cuối của họ tên) để không đưa thông tin cá nhân lên thông báo công khai.
     */
    public static function displayName(?string $fullName): string
    {
        $givenName = Str::of((string) $fullName)->squish()->explode(' ')->last();

        return filled($givenName) ? Str::limit($givenName, 20, '') : 'Một khách hàng';
    }

    /**
     * Một thông báo ngẫu nhiên từ file dữ liệu giả.
     *
     * @return array{kind: string, message: string}
     */
    public static function fake(): array
    {
        /** @var array<string, mixed> $data */
        $data = require database_path('data/fake-activities.php');

        $templates = collect($data['templates']);
        $roll = random_int(1, (int) $templates->sum('weight'));
        $template = $templates->first(function (array $template) use (&$roll): bool {
            $roll -= $template['weight'];

            return $roll <= 0;
        });

        $message = strtr($template['message'], [
            '{name}' => Arr::random($data['names']),
            '{city}' => Arr::random($data['cities']),
            '{product}' => Arr::random($data['products']),
            '{package}' => Arr::random($data['packages']),
        ]);

        return ['kind' => $template['kind'], 'message' => $message];
    }
}
