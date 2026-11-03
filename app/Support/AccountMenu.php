<?php

namespace App\Support;

/**
 * Các mục của tài khoản khách hàng, dùng chung cho menu thả xuống trên header và khung trái các trang tài khoản.
 */
class AccountMenu
{
    /**
     * @return list<array{route: string, icon: string, label: string}>
     */
    public static function links(): array
    {
        return [
            ['route' => 'account.show', 'icon' => 'bookmark', 'label' => 'Đơn hàng & dịch vụ'],
            ['route' => 'account.edit', 'icon' => 'pencil', 'label' => 'Thông tin cá nhân'],
            ['route' => 'account.password.edit', 'icon' => 'key', 'label' => 'Đổi mật khẩu'],
            ['route' => 'account.affiliate.show', 'icon' => 'dollar', 'label' => 'Kiếm tiền affiliate'],
        ];
    }
}
