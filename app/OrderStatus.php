<?php

namespace App;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Chờ thanh toán',
            self::Paid => 'Đã thanh toán',
            self::Failed => 'Thất bại',
            self::Cancelled => 'Đã huỷ',
        };
    }
}
