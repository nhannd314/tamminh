<?php

namespace App;

use Filament\Support\Contracts\HasLabel;

/**
 * Ngũ hành - bản mệnh mà vật phẩm phong thủy hợp (Kim, Mộc, Thủy, Hỏa, Thổ).
 */
enum FiveElement: string implements HasLabel
{
    case Kim = 'kim';
    case Moc = 'moc';
    case Thuy = 'thuy';
    case Hoa = 'hoa';
    case Tho = 'tho';

    public function getLabel(): string
    {
        return match ($this) {
            self::Kim => 'Kim',
            self::Moc => 'Mộc',
            self::Thuy => 'Thủy',
            self::Hoa => 'Hỏa',
            self::Tho => 'Thổ',
        };
    }

    /**
     * Màu chấm đại diện cho hành, dùng ở bộ lọc, thẻ sản phẩm và trang chi tiết.
     */
    public function color(): string
    {
        return match ($this) {
            self::Kim => '#C9B68C',
            self::Moc => '#4E7A4A',
            self::Thuy => '#25446F',
            self::Hoa => '#B5462F',
            self::Tho => '#946821',
        };
    }
}
