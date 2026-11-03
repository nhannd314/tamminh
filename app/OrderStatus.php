<?php

namespace App;

/**
 * Trạng thái xử lý đơn vật phẩm; việc đã thanh toán hay chưa ghi riêng ở `orders.paid_at`.
 */
enum OrderStatus: string
{
    case New = 'new';
    case Processing = 'processing';
    case Shipping = 'shipping';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Mới',
            self::Processing => 'Đang chuẩn bị',
            self::Shipping => 'Đang giao',
            self::Completed => 'Hoàn tất',
            self::Cancelled => 'Đã huỷ',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'warning',
            self::Processing => 'info',
            self::Shipping => 'primary',
            self::Completed => 'success',
            self::Cancelled => 'gray',
        };
    }
}
