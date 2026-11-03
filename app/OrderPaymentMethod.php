<?php

namespace App;

use App\Models\Setting;

enum OrderPaymentMethod: string
{
    case Cod = 'cod';
    case Online = 'online';

    /**
     * Phí giao hàng mặc định khi chưa cấu hình trong admin (VNĐ).
     */
    public const DEFAULT_SHIPPING_FEE = 30000;

    public function label(): string
    {
        return match ($this) {
            self::Cod => 'Thanh toán khi nhận hàng (COD)',
            self::Online => 'Chuyển khoản online',
        };
    }

    /**
     * Thanh toán online được miễn phí giao hàng; COD dùng phí cấu hình ở trang Cấu hình admin.
     */
    public function shippingFee(): int
    {
        return match ($this) {
            self::Online => 0,
            self::Cod => (int) Setting::get('shop.shipping_fee', self::DEFAULT_SHIPPING_FEE),
        };
    }
}
