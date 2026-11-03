<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Kênh liên hệ của cửa hàng (hotline, Zalo, email, địa chỉ, giờ làm việc, mạng xã hội).
 * Admin sửa ở trang Cấu hình > Liên hệ (bảng settings, khoá `contact.*`); chưa lưu thì dùng mặc định trong config/site.php.
 */
class ShopContact
{
    /**
     * Trang mạng xã hội admin nhập link (Zalo dựng từ số Zalo nên không nằm ở đây).
     *
     * @var list<string>
     */
    public const SOCIAL_NETWORKS = ['facebook', 'youtube', 'tiktok'];

    public static function hotline(): string
    {
        return self::value('phone');
    }

    public static function zalo(): string
    {
        return self::value('zalo');
    }

    public static function email(): string
    {
        return self::value('email');
    }

    public static function address(): string
    {
        return self::value('address');
    }

    public static function hours(): string
    {
        return self::value('hours');
    }

    /**
     * Link gọi hotline, VD "tel:0888112626".
     */
    public static function hotlineUrl(): ?string
    {
        $digits = preg_replace('/[^\d+]+/', '', self::hotline());

        return $digits === '' ? null : 'tel:'.$digits;
    }

    /**
     * Link nhắn Zalo theo số Zalo cửa hàng (đầu 0 đổi sang mã vùng 84 như Zalo yêu cầu).
     */
    public static function zaloUrl(): ?string
    {
        $digits = preg_replace('/\D+/', '', self::zalo());

        if ($digits === '') {
            return null;
        }

        return 'https://zalo.me/'.(str_starts_with($digits, '0') ? '84'.substr($digits, 1) : $digits);
    }

    /**
     * Link mạng xã hội đã cấu hình theo thứ tự hiển thị (Zalo cuối cùng); kênh để trống không có trong danh sách.
     *
     * @return array<string, string>
     */
    public static function socialLinks(): array
    {
        $links = collect(self::SOCIAL_NETWORKS)->mapWithKeys(fn (string $network): array => [$network => self::value($network)]);

        return $links->put('zalo', (string) self::zaloUrl())->filter()->all();
    }

    public static function emailUrl(): ?string
    {
        return self::email() === '' ? null : 'mailto:'.self::email();
    }

    /**
     * Khoá trong bảng settings ứng với từng kênh, dùng chung với trang Cấu hình admin.
     */
    public static function settingKey(string $channel): string
    {
        return 'contact.'.$channel;
    }

    private static function value(string $channel): string
    {
        $saved = Setting::get(self::settingKey($channel));

        return trim((string) (filled($saved) ? $saved : config('site.'.$channel)));
    }
}
