<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use App\UserRole;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;

class UserForm
{
    /**
     * @return array<int, Component>
     */
    public static function components(): array
    {
        return [
            TextInput::make('name')
                ->label('Họ tên')
                ->required()
                ->maxLength(255),
            TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),
            TextInput::make('phone')
                ->label('Điện thoại')
                ->tel()
                ->maxLength(20),
            TextInput::make('address')
                ->label('Địa chỉ')
                ->maxLength(255),
            Select::make('role')
                ->label('Vai trò')
                ->options(collect(UserRole::cases())->mapWithKeys(fn (UserRole $role): array => [$role->value => $role->label()])->all())
                ->required()
                ->default(UserRole::User->value)
                ->selectablePlaceholder(false)
                ->disabled(fn (?User $record): bool => $record?->is(auth()->user()) ?? false)
                ->helperText('Chỉ quản trị viên mới vào được trang quản trị. Không thể tự đổi vai trò của chính mình.'),
            TextInput::make('password')
                ->label('Mật khẩu')
                ->password()
                ->revealable()
                ->minLength(8)
                ->required(fn (string $operation): bool => $operation === 'create')
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->helperText(fn (string $operation): ?string => $operation === 'edit' ? 'Để trống nếu không đổi mật khẩu.' : null),
            Toggle::make('newsletter_opt_in')
                ->label('Đăng ký nhận bản tin'),
        ];
    }
}
