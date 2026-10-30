<?php

namespace App;

enum UserRole: string
{
    case User = 'user';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::User => 'Người dùng',
            self::Admin => 'Quản trị viên',
        };
    }
}
