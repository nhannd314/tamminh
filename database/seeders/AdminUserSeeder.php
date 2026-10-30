<?php

namespace Database\Seeders;

use App\Models\User;
use App\UserRole;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Tạo (hoặc cập nhật) tài khoản quản trị. Chạy lại nhiều lần vẫn chỉ có một tài khoản.
     */
    public function run(): void
    {
        $admin = User::query()->firstOrNew(['email' => 'nhannd314@gmail.com']);

        $admin->fill([
            'name' => 'Quản trị viên',
            'password' => 'MatKhau@314!',
        ]);
        $admin->role = UserRole::Admin;
        $admin->email_verified_at ??= now();
        $admin->save();
    }
}
