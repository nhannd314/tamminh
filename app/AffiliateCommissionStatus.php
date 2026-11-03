<?php

namespace App;

/**
 * Vòng đời hoa hồng: chờ xác nhận → đã xác nhận (đủ điều kiện chi trả) → đã chi trả; đơn huỷ thì hoa hồng bị huỷ.
 */
enum AffiliateCommissionStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Chờ xác nhận',
            self::Approved => 'Chờ chi trả',
            self::Paid => 'Đã chi trả',
            self::Cancelled => 'Đã huỷ',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'info',
            self::Paid => 'success',
            self::Cancelled => 'gray',
        };
    }
}
