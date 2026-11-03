<x-layouts.auth title="Quên mật khẩu">
    <x-slot:brand>
        <p class="font-serif text-3xl leading-snug font-medium text-cream-50">Đừng lo,<br />chuyện nhỏ thôi.</p>
        <p class="mt-4 text-sm leading-relaxed text-navy-200">Kết quả phân tích và lịch hẹn của bạn vẫn được lưu an toàn. Đặt lại mật khẩu chỉ mất vài bước.</p>
    </x-slot:brand>

    @if ($isDone)
        <section class="text-center">
            <div class="mx-auto flex size-14 items-center justify-center rounded-full bg-[#F0FDF4] text-[#15803D]"><x-ui.icon name="check" :size="28" /></div>
            <h1 class="mt-5 font-serif text-2xl font-semibold text-ink-600">Đã đổi mật khẩu</h1>
            <p class="mt-1.5 text-sm leading-relaxed text-ink-300">Mật khẩu của bạn đã được cập nhật. Hãy đăng nhập lại bằng mật khẩu mới.</p>
            <x-ui.button :href="route('login')" block class="mt-7">Đăng nhập</x-ui.button>
        </section>
    @elseif ($sentTo)
        <section>
            <div class="flex size-14 items-center justify-center rounded-full bg-copper-50 text-copper-600"><x-ui.icon name="mail-check" :size="24" /></div>
            <h1 class="mt-5 font-serif text-2xl font-semibold text-ink-600">Kiểm tra hộp thư</h1>
            <p class="mt-1.5 text-sm leading-relaxed text-ink-300">
                Nếu <strong class="font-semibold break-all text-ink-500">{{ $sentTo }}</strong> đã đăng ký tài khoản, chúng tôi đã gửi liên kết đặt lại mật khẩu tới email này.
                Liên kết có hiệu lực trong {{ config('auth.passwords.users.expire') }} phút.
            </p>
            <div class="mt-6 rounded-md bg-cream-100 px-4 py-3.5 text-sm leading-relaxed text-ink-400">Không thấy email? Hãy kiểm tra mục <strong class="font-semibold">Thư rác</strong> hoặc <strong class="font-semibold">Quảng cáo</strong>.</div>

            <form method="POST" action="{{ route('password.email') }}" class="mt-6">
                @csrf
                <input type="hidden" name="email" value="{{ $sentTo }}" />
                <x-ui.button type="submit" variant="ghost" block disabled data-resend-cooldown="60" data-resend-label="Gửi lại email">Gửi lại sau 60 giây</x-ui.button>
            </form>

            <a href="{{ route('password.request') }}" class="mt-5 inline-flex items-center gap-1.5 text-sm font-medium text-copper-600 hover:text-copper-700">
                <x-ui.icon name="pencil" :size="14" />Dùng email khác
            </a>
        </section>
    @else
        <section>
            <div class="flex size-14 items-center justify-center rounded-full bg-copper-50 text-copper-600"><x-ui.icon name="key" :size="24" /></div>
            <h1 class="mt-5 font-serif text-2xl font-semibold text-ink-600">Quên mật khẩu?</h1>
            <p class="mt-1.5 text-sm leading-relaxed text-ink-300">Nhập email bạn đã dùng để đăng ký. Chúng tôi sẽ gửi liên kết đặt lại mật khẩu.</p>

            <form method="POST" action="{{ route('password.email') }}" class="mt-7 flex flex-col gap-5" data-submit-loading novalidate>
                @csrf

                <x-ui.field label="Email" for="forgot-email" name="email">
                    <x-ui.input id="forgot-email" type="email" name="email" autocomplete="email" placeholder="ban@vidu.com" required autofocus />
                </x-ui.field>

                <x-ui.button type="submit" block data-loading-text="Đang gửi…">Gửi liên kết đặt lại</x-ui.button>
            </form>
        </section>
    @endif

    <x-slot:footer>
        <span class="inline-flex items-center gap-1.5">
            <x-ui.icon name="arrow-left" :size="16" /><a href="{{ route('login') }}" class="font-semibold text-copper-600 hover:text-copper-700">Quay lại đăng nhập</a>
        </span>
    </x-slot:footer>
</x-layouts.auth>
