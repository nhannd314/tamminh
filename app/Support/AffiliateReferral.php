<?php

namespace App\Support;

use App\Models\Affiliate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

/**
 * Theo dõi link giới thiệu: `?ref={code}` lưu vào cookie (lần bấm cuối thắng) trong Affiliate::cookieDays() ngày,
 * đơn tạo trong thời gian đó được gắn cho affiliate. Không tính khi affiliate bị khoá hoặc tự mua qua link của mình.
 */
class AffiliateReferral
{
    public const COOKIE = 'tm_ref';

    public static function capture(Request $request): void
    {
        $code = strtoupper(trim((string) $request->query('ref')));

        if ($code === '' || strlen($code) > 16) {
            return;
        }

        $affiliate = Affiliate::query()->where('code', $code)->whereNull('suspended_at')->first();

        if ($affiliate !== null && $affiliate->user_id !== $request->user('customer')?->id) {
            Cookie::queue(self::COOKIE, $affiliate->code, Affiliate::cookieDays() * 24 * 60);
        }
    }

    public static function resolve(Request $request): ?Affiliate
    {
        $code = $request->cookie(self::COOKIE);

        if (! is_string($code) || $code === '') {
            return null;
        }

        $affiliate = Affiliate::query()->where('code', $code)->whereNull('suspended_at')->first();

        return $affiliate !== null && $affiliate->user_id !== $request->user('customer')?->id ? $affiliate : null;
    }
}
