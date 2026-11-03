<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Throwable;

/**
 * Đăng nhập / đăng ký khách hàng bằng Google (guard `customer`).
 * Tìm theo `google_id`, sau đó theo email đã được Google xác minh (gắn Google vào tài khoản sẵn có);
 * chưa có thì tạo tài khoản mới. Tài khoản admin không đăng nhập bằng Google ở đây.
 */
class GoogleAuthController extends Controller
{
    public function redirect(): SymfonyRedirectResponse|RedirectResponse
    {
        if (! self::isConfigured()) {
            return to_route('login')->withErrors(['email' => 'Đăng nhập Google chưa được cấu hình.']);
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->filled('error')) {
            return to_route('login')->withErrors(['email' => 'Bạn đã huỷ đăng nhập bằng Google.']);
        }

        try {
            /** @var GoogleUser $googleUser */
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $exception) {
            Log::warning('Đăng nhập Google thất bại: '.$exception->getMessage());

            return to_route('login')->withErrors(['email' => 'Không thể đăng nhập bằng Google, vui lòng thử lại.']);
        }

        $email = Str::lower((string) $googleUser->getEmail());

        if ($email === '' || ! ($googleUser->user['email_verified'] ?? $googleUser->user['verified_email'] ?? false)) {
            return to_route('login')->withErrors(['email' => 'Tài khoản Google chưa xác minh email, vui lòng dùng cách đăng nhập khác.']);
        }

        $user = User::query()->where('google_id', $googleUser->getId())->first()
            ?? User::query()->where('email', $email)->first();

        if ($user?->isAdmin()) {
            return to_route('login')->withErrors(['email' => 'Tài khoản quản trị vui lòng đăng nhập tại trang quản trị.']);
        }

        if ($user === null) {
            // Chưa có mật khẩu: khách muốn dùng email + mật khẩu thì tự đặt trong trang tài khoản.
            $user = User::create([
                'name' => $googleUser->getName() ?: Str::before($email, '@'),
                'email' => $email,
            ]);
        }

        $user->forceFill([
            'google_id' => $googleUser->getId(),
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        Auth::guard('customer')->login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended(route('home'))->with('toast', 'Xin chào '.$user->name.'!');
    }

    /**
     * Ẩn nút Google khi chưa khai báo GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET.
     */
    public static function isConfigured(): bool
    {
        return filled(config('services.google.client_id')) && filled(config('services.google.client_secret'));
    }
}
