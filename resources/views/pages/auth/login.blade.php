<x-layouts.auth title="Đăng nhập">
    <x-slot:brand>
        <p class="font-serif text-3xl leading-snug font-medium text-cream-50">"Biết người là trí,<br />biết mình là sáng."</p>
        <p class="mt-4 text-sm leading-relaxed text-navy-200">Đăng nhập để xem lại kết quả phân tích tướng mặt, theo dõi đơn hàng và lịch hẹn với chuyên gia.</p>
    </x-slot:brand>

    <h1 class="font-serif text-2xl font-semibold text-ink-600">Đăng nhập</h1>
    <p class="mt-1.5 text-sm text-ink-300">Chào mừng bạn quay lại. Chọn cách đăng nhập bên dưới.</p>

    @if (session('status'))
        <p class="mt-5 rounded-md bg-copper-50 px-4 py-3 text-sm text-copper-700">{{ session('status') }}</p>
    @endif

    <x-ui.google-button label="Tiếp tục với Google" />

    <form method="POST" action="{{ route('login') }}" class="mt-6 flex flex-col gap-5" data-submit-loading novalidate>
        @csrf

        <x-ui.field label="Email" for="login-email" name="email">
            <x-ui.input id="login-email" type="email" name="email" autocomplete="email" placeholder="ban@vidu.com" required autofocus />
        </x-ui.field>

        <div class="tm-field">
            <div class="flex items-center justify-between gap-3">
                <label for="login-password" class="tm-label">Mật khẩu</label>
                <a href="{{ route('password.request') }}" class="text-xs font-medium text-copper-600 hover:text-copper-700">Quên mật khẩu?</a>
            </div>
            <x-ui.password-input id="login-password" name="password" autocomplete="current-password" placeholder="••••••••" required />
            @error('password')
                <p class="tm-error">{{ $message }}</p>
            @enderror
        </div>

        <x-ui.checkbox name="remember" checked>Ghi nhớ đăng nhập</x-ui.checkbox>

        <x-ui.button type="submit" block data-loading-text="Đang đăng nhập…">Đăng nhập</x-ui.button>
    </form>

    <x-slot:footer>
        Chưa có tài khoản? <a href="{{ route('register') }}" class="font-semibold text-copper-600 hover:text-copper-700">Đăng ký miễn phí</a>
    </x-slot:footer>
</x-layouts.auth>
