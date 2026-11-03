<?php

namespace App;

enum PalmReadingPackage: string
{
    case Basic = 'basic';
    case Premium = 'premium';

    public function label(): string
    {
        return match ($this) {
            self::Basic => 'Cơ bản (miễn phí)',
            self::Premium => 'Chuyên sâu',
        };
    }
}
