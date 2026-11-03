<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerLoginRequest;
use App\Http\Requests\CustomerRegisterRequest;
use App\Models\User;
use App\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Đăng nhập khách hàng bằng guard `customer`, tách biệt với trang quản trị (`web`).
 * Chỉ tài khoản vai trò người dùng được đăng nhập ở đây; admin dùng /admin.
 */
class CustomerAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('pages.auth.login');
    }

    public function login(CustomerLoginRequest $request): RedirectResponse
    {
        $credentials = [...$request->safe()->only(['email', 'password']), 'role' => UserRole::User];

        if (! Auth::guard('customer')->attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->safe()->only('email'))
                ->withErrors(['email' => 'Email hoặc mật khẩu chưa đúng.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function showRegister(): View
    {
        return view('pages.auth.register');
    }

    public function register(CustomerRegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            ...$request->safe()->only(['name', 'email', 'password']),
            'phone' => $request->safe()->input('phone') ?: null,
            'newsletter_opt_in' => $request->boolean('newsletter'),
        ]);

        Auth::guard('customer')->login($user);
        $request->session()->regenerate();

        // Đến từ trang cần đăng nhập (VD: tham gia affiliate) rồi chuyển sang đăng ký thì quay lại đúng trang đó.
        return redirect()->intended(route('home'))->with('toast', 'Chào mừng bạn đến với Tâm Minh!');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('toast', 'Bạn đã đăng xuất.');
    }
}
