<x-layouts.auth title="Đặt mật khẩu mới">
    <x-slot:brand>
        <p class="font-serif text-3xl leading-snug font-medium text-cream-50">Đừng lo,<br />chuyện nhỏ thôi.</p>
        <p class="mt-4 text-sm leading-relaxed text-navy-200">Kết quả phân tích và lịch hẹn của bạn vẫn được lưu an toàn. Đặt lại mật khẩu chỉ mất vài bước.</p>
    </x-slot:brand>

    <div class="flex size-14 items-center justify-center rounded-full bg-copper-50 text-copper-600"><x-ui.icon name="lock-keyhole" :size="24" /></div>
    <h1 class="mt-5 font-serif text-2xl font-semibold text-ink-600">Đặt mật khẩu mới</h1>
    <p class="mt-1.5 text-sm leading-relaxed text-ink-300">Mật khẩu mới nên khác với mật khẩu bạn đã dùng trước đây.</p>

    @error('token')
        <div class="mt-5 rounded-md border border-danger/30 bg-danger/5 px-4 py-3 text-sm leading-relaxed text-danger">
            {{ $message }}
            <a href="{{ route('password.request') }}" class="font-semibold underline">Gửi lại liên kết</a>
        </div>
    @enderror

    <form method="POST" action="{{ route('password.update') }}" class="mt-7 flex flex-col gap-5" data-submit-loading novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}" />
        <input type="hidden" name="email" value="{{ old('email', $email) }}" />

        <x-ui.field label="Mật khẩu mới" for="reset-password" name="password">
            <x-ui.password-input id="reset-password" name="password" autocomplete="new-password" placeholder="Tối thiểu 8 ký tự" required autofocus strength />
        </x-ui.field>

        <x-ui.field label="Nhập lại mật khẩu mới" for="reset-password-confirmation" name="password_confirmation">
            <x-ui.password-input id="reset-password-confirmation" name="password_confirmation" autocomplete="new-password" placeholder="••••••••" required />
        </x-ui.field>

        <x-ui.button type="submit" block data-loading-text="Đang cập nhật…">Cập nhật mật khẩu</x-ui.button>
    </form>

    <x-slot:footer>
        <span class="inline-flex items-center gap-1.5">
            <x-ui.icon name="arrow-left" :size="16" /><a href="{{ route('login') }}" class="font-semibold text-copper-600 hover:text-copper-700">Quay lại đăng nhập</a>
        </span>
    </x-slot:footer>
</x-layouts.auth>
