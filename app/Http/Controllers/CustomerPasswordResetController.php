<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\UserRole;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

/**
 * Quên mật khẩu cho tài khoản khách hàng: gửi liên kết qua email rồi đặt mật khẩu mới.
 * Luôn báo "đã gửi" dù email có tồn tại hay không để không lộ ai đã đăng ký; tài khoản admin không đặt lại ở đây.
 */
class CustomerPasswordResetController extends Controller
{
    /**
     * Bước 1 (nhập email), bước 2 (kiểm tra hộp thư) hoặc bước 4 (đã đổi mật khẩu), tuỳ dữ liệu flash.
     */
    public function showForgot(): View
    {
        return view('pages.auth.forgot-password', [
            'sentTo' => session('reset_link_sent_to'),
            'isDone' => (bool) session('password_reset_done'),
        ]);
    }

    public function sendLink(Request $request): RedirectResponse
    {
        $request->validate(
            ['email' => ['required', 'string', 'email']],
            ['email.required' => 'Vui lòng nhập email hợp lệ.', 'email.email' => 'Vui lòng nhập email hợp lệ.'],
        );

        $email = $request->string('email')->trim()->lower()->toString();

        Password::broker()->sendResetLink(['email' => $email, 'role' => UserRole::User]);

        return to_route('password.request')->with('reset_link_sent_to', $email);
    }

    /**
     * Bước 3: mở từ liên kết trong email.
     */
    public function showReset(Request $request, string $token): View
    {
        return view('pages.auth.reset-password', [
            'token' => $token,
            'email' => $request->string('email')->toString(),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ], [
            'password.required' => 'Mật khẩu cần ít nhất 8 ký tự.',
            'password.min' => 'Mật khẩu cần ít nhất 8 ký tự.',
            'password.confirmed' => 'Mật khẩu nhập lại không khớp.',
        ]);

        $status = Password::broker()->reset(
            [...$request->only('email', 'password', 'password_confirmation', 'token'), 'role' => UserRole::User],
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['token' => 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn. Vui lòng gửi lại liên kết mới.']);
        }

        return to_route('password.request')->with('password_reset_done', true);
    }
}
