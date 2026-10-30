<?php

namespace App\Support\Astrology;

/**
 * Ngũ hành, theo thứ tự hiển thị trên trang kết quả.
 */
enum Element: string
{
    case Kim = 'kim';
    case Thuy = 'thuy';
    case Moc = 'moc';
    case Hoa = 'hoa';
    case Tho = 'tho';

    public function label(): string
    {
        return match ($this) {
            self::Kim => 'Kim',
            self::Thuy => 'Thủy',
            self::Moc => 'Mộc',
            self::Hoa => 'Hỏa',
            self::Tho => 'Thổ',
        };
    }

    public function glyph(): string
    {
        return match ($this) {
            self::Kim => '金',
            self::Thuy => '水',
            self::Moc => '木',
            self::Hoa => '火',
            self::Tho => '土',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Kim => '#847C70',
            self::Thuy => '#25446F',
            self::Moc => '#4F8A4B',
            self::Hoa => '#C2412D',
            self::Tho => '#B6822F',
        };
    }

    /**
     * Số cục trong Tử vi: Thủy nhị, Mộc tam, Kim tứ, Thổ ngũ, Hỏa lục.
     */
    public function cucNumber(): int
    {
        return match ($this) {
            self::Thuy => 2,
            self::Moc => 3,
            self::Kim => 4,
            self::Tho => 5,
            self::Hoa => 6,
        };
    }

    /**
     * Hành được hành này sinh ra (Kim sinh Thủy, Thủy sinh Mộc…).
     */
    public function generates(): self
    {
        return match ($this) {
            self::Kim => self::Thuy,
            self::Thuy => self::Moc,
            self::Moc => self::Hoa,
            self::Hoa => self::Tho,
            self::Tho => self::Kim,
        };
    }

    /**
     * Hành sinh ra hành này (Thổ sinh Kim…).
     */
    public function generatedBy(): self
    {
        return match ($this) {
            self::Kim => self::Tho,
            self::Thuy => self::Kim,
            self::Moc => self::Thuy,
            self::Hoa => self::Moc,
            self::Tho => self::Hoa,
        };
    }
}
