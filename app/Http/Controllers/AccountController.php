<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAccountRequest;
use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Trang tài khoản khách hàng: đơn vật phẩm đã đặt, các dịch vụ đã đăng ký
 * (xem tướng khuôn mặt, đặt lịch xem chỉ tay) và sửa thông tin cá nhân.
 * Chỉ hiện những gì khách tạo khi đã đăng nhập.
 */
class AccountController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user('customer');

        return view('pages.account.show', [
            'user' => $user,
            'orders' => $user->orders()->withSum('items', 'quantity')->latest()->get(),
            'analyses' => $user->faceAnalyses()->with('sections')->latest()->get(),
            'palmReadingBookings' => $user->palmReadingBookings()->latest()->get(),
        ]);
    }

    public function edit(Request $request): View
    {
        return view('pages.account.edit', ['user' => $request->user('customer')]);
    }

    public function update(UpdateAccountRequest $request): RedirectResponse
    {
        $request->user('customer')->update([
            'name' => $request->string('name')->squish()->toString(),
            'phone' => $request->safe()->input('phone') ?: null,
            'address' => $request->string('address')->squish()->toString() ?: null,
            'newsletter_opt_in' => $request->boolean('newsletter'),
        ]);

        return to_route('account.edit')->with('toast', 'Đã lưu thông tin cá nhân.');
    }

    public function editPassword(Request $request): View
    {
        return view('pages.account.password', ['user' => $request->user('customer')]);
    }

    /**
     * Đổi mật khẩu và làm mới remember token để các thiết bị đang "ghi nhớ đăng nhập" phải đăng nhập lại.
     */
    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = $request->user('customer');
        $hadPassword = $user->hasPassword();

        $user->forceFill([
            'password' => $request->validated('password'),
            'remember_token' => Str::random(60),
        ])->save();

        Auth::guard('customer')->login($user);
        $request->session()->regenerate();

        return to_route('account.password.edit')->with('toast', $hadPassword ? 'Đã đổi mật khẩu.' : 'Đã đặt mật khẩu.');
    }
}
