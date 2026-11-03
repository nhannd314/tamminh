<?php

namespace App;

use Filament\Support\Contracts\HasLabel;

/**
 * Tông màu ảnh bìa dựng sẵn (chữ Hán trên nền gradient) khi bài viết không có ảnh bìa riêng.
 */
enum PostTone: string implements HasLabel
{
    case Copper = 'copper';
    case Navy = 'navy';
    case Cream = 'cream';

    public function getLabel(): string
    {
        return match ($this) {
            self::Copper => 'Đồng',
            self::Navy => 'Xanh đậm',
            self::Cream => 'Kem',
        };
    }

    /**
     * Class Tailwind cho nền và màu chữ của ảnh bìa dựng sẵn.
     */
    public function coverClasses(): string
    {
        return match ($this) {
            self::Navy => 'bg-linear-to-br from-navy-500 to-navy-400 text-copper-400',
            self::Cream => 'bg-linear-to-br from-cream-300 to-copper-50 text-copper-600',
            self::Copper => 'bg-linear-to-br from-copper-100 to-cream-300 text-copper-700',
        };
    }
}
