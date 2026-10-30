@php
    $hasHeroPhoto = file_exists(public_path('images/hero-mountains.webp'));
    $heroImage = $hasHeroPhoto ? 'images/hero-mountains.webp' : 'images/ink-mountains.svg';
@endphp

<section @class([
    'relative overflow-hidden bg-[#fdf7f0] bg-no-repeat py-12 lg:py-16',
    'bg-cover bg-bottom' => $hasHeroPhoto,
    'bg-right-bottom bg-size-[auto_200px] md:bg-size-[auto_320px] lg:bg-size-[auto_420px]' => ! $hasHeroPhoto,
]) style="background-image: url('{{ asset($heroImage) }}')">
    <div class="tm-container grid items-start gap-9 lg:grid-cols-[1.1fr_1fr] lg:gap-14">
        <div class="relative z-10 animate-fade-up">
            <x-ui.eyebrow>Nhân tướng Tâm Minh</x-ui.eyebrow>

            <h1 class="tm-display mt-4 mb-5">
                Xem tướng hiểu mình<br />tâm sinh<span class="tm-accent"> tướng mạo</span>
            </h1>

            <p class="mb-7 max-w-[560px] text-[15px] leading-relaxed text-ink-500 sm:mb-8 sm:text-base">
                Kế thừa tinh hoa nhân tướng học phương Đông, Tâm Minh vẽ nên tấm bản đồ chi tiết về con người bạn - phân tích cá nhân hóa, luận giải sâu sắc về vận mệnh, tài lộc và tình duyên.
            </p>

            <div class="grid max-w-[540px] grid-cols-3 gap-3 sm:gap-6" data-reveal-stagger>
                <x-ui.feature icon="face" label="Luận giải chuyên sâu" data-reveal />
                <x-ui.feature icon="shield" label="Bảo mật thông tin KH" data-reveal />
                <x-ui.feature icon="bolt" label="Kết quả nhanh chóng" data-reveal />
            </div>
        </div>

        <x-ui.form-card title="Nhập thông tin của bạn" class="relative z-20 animate-fade-up [animation-delay:120ms]">
            <form id="xem-tuong" method="POST" action="{{ route('analyses.store') }}" class="scroll-mt-28 space-y-4" enctype="multipart/form-data" data-submit-loading>
                @csrf

                <x-ui.field label="Họ và tên" for="face-name" name="name">
                    <x-ui.input id="face-name" name="name" placeholder="Nhập họ và tên" autocomplete="name" required maxlength="100" />
                </x-ui.field>

                <x-ui.field label="Giới tính" name="gender">
                    <div class="flex flex-wrap items-center gap-x-7 gap-y-2">
                        <x-ui.radio name="gender" value="male" checked>Nam</x-ui.radio>
                        <x-ui.radio name="gender" value="female">Nữ</x-ui.radio>
                    </div>
                </x-ui.field>

                <x-ui.field label="Ngày sinh" name="birth_date">
                    <x-ui.date-select name="birth" />
                    @if ($errors->hasAny(['birth_day', 'birth_month', 'birth_year']))
                        <p class="tm-error">Vui lòng chọn đầy đủ ngày sinh.</p>
                    @endif
                </x-ui.field>

                <x-ui.field label="Giờ sinh">
                    <x-ui.time-select name="birth" />
                </x-ui.field>

                <x-ui.field label="Upload ảnh chân dung" name="portrait">
                    <x-ui.file-drop name="portrait" accept="image/jpeg,image/png,image/webp" required hint="(Ảnh rõ mặt, không đeo kính, không che mặt)" />
                </x-ui.field>

                <x-ui.field name="agree_terms">
                    <x-ui.checkbox name="agree_terms" required>
                        <span>Tôi đồng ý với <a href="#" class="tm-link">Điều khoản dịch vụ</a> và <a href="#" class="tm-link">Chính sách bảo mật</a> của Tâm Minh</span>
                    </x-ui.checkbox>
                </x-ui.field>

                <x-ui.button type="submit" size="lg" block trailing-icon="arrow" class="uppercase tracking-wide" data-loading-text="Đang gửi ảnh…">
                    Xem kết quả ngay
                </x-ui.button>
            </form>
        </x-ui.form-card>
    </div>
</section>
