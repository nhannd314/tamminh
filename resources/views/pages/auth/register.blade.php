<x-layouts.auth title="Đăng ký" max-width="460px">
    <x-slot:brand>
        <p class="font-serif text-3xl leading-snug font-medium text-cream-50">Bắt đầu hành trình<br />hiểu rõ bản thân.</p>
        <ul class="mt-8 flex flex-col gap-4 text-sm text-navy-100">
            @foreach ([
                ['bookmark', 'Lưu kết quả phân tích tướng mặt và xem lại bất cứ lúc nào'],
                ['calendar-check', 'Đặt lịch tư vấn trực tiếp với chuyên gia'],
                ['gift', 'Ưu đãi riêng cho thành viên khi mua vật phẩm phong thủy'],
            ] as [$icon, $benefit])
                <li class="flex items-start gap-3"><x-ui.icon :name="$icon" :size="18" class="mt-px text-copper-300" />{{ $benefit }}</li>
            @endforeach
        </ul>
    </x-slot:brand>

    <h1 class="font-serif text-2xl font-semibold text-ink-600">Tạo tài khoản</h1>
    <p class="mt-1.5 text-sm text-ink-300">Miễn phí, chỉ mất chưa đến một phút.</p>

    <x-ui.google-button label="Đăng ký với Google" />

    <form method="POST" action="{{ route('register') }}" class="mt-6 flex flex-col gap-5" data-submit-loading novalidate>
        @csrf

        <x-ui.field label="Họ và tên" for="register-name" name="name">
            <x-ui.input id="register-name" name="name" autocomplete="name" placeholder="Nguyễn Văn An" required maxlength="100" autofocus />
        </x-ui.field>

        <div class="grid gap-5 sm:grid-cols-2">
            <x-ui.field label="Email" for="register-email" name="email">
                <x-ui.input id="register-email" type="email" name="email" autocomplete="email" placeholder="ban@vidu.com" required />
            </x-ui.field>

            <div class="tm-field">
                <label for="register-phone" class="tm-label">Số điện thoại <span class="font-normal text-ink-200">(tùy chọn)</span></label>
                <x-ui.input id="register-phone" type="tel" name="phone" inputmode="tel" autocomplete="tel" placeholder="09xx xxx xxx" maxlength="20" />
                @error('phone')
                    <p class="tm-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <x-ui.field label="Mật khẩu" for="register-password" name="password">
            <x-ui.password-input id="register-password" name="password" autocomplete="new-password" placeholder="Tối thiểu 8 ký tự" required strength />
        </x-ui.field>

        <x-ui.field label="Nhập lại mật khẩu" for="register-password-confirmation" name="password_confirmation">
            <x-ui.password-input id="register-password-confirmation" name="password_confirmation" autocomplete="new-password" placeholder="Nhập lại mật khẩu" required />
        </x-ui.field>

        <div class="flex flex-col gap-1">
            <x-ui.checkbox name="agree_terms">
                Tôi đồng ý với <a href="{{ route('pages.show', 'dieu-khoan-dich-vu') }}" target="_blank" class="font-medium text-copper-600 hover:text-copper-700">Điều khoản sử dụng</a>
                và <a href="{{ route('pages.show', 'chinh-sach-bao-mat') }}" target="_blank" class="font-medium text-copper-600 hover:text-copper-700">Chính sách bảo mật</a>
            </x-ui.checkbox>
            @error('agree_terms')
                <p class="tm-error pl-6">{{ $message }}</p>
            @enderror
        </div>

        <x-ui.checkbox name="newsletter" :checked="! $errors->any() || old('newsletter')">Nhận bài viết phong thủy và ưu đãi qua email</x-ui.checkbox>

        <x-ui.button type="submit" block data-loading-text="Đang tạo tài khoản…">Tạo tài khoản</x-ui.button>
    </form>

    <x-slot:footer>
        Đã có tài khoản? <a href="{{ route('login') }}" class="font-semibold text-copper-600 hover:text-copper-700">Đăng nhập</a>
    </x-slot:footer>
</x-layouts.auth>
