<section class="relative overflow-x-clip border-y border-cream-300 bg-cream-50 py-12 lg:py-16">
    <div class="tm-container relative grid gap-6 lg:grid-cols-2 lg:gap-8">
        <div class="pointer-events-none absolute top-1/2 left-0 z-0 -translate-y-1/2 lg:left-4" aria-hidden="true">
            <img src="{{ asset('images/zodiac-wheel.svg') }}" alt="" class="w-[340px] max-w-none animate-spin-slow opacity-15 lg:w-[460px]" />
        </div>

        <div class="relative z-10 flex flex-col justify-center" data-reveal="fade-right">
            <x-ui.section-heading eyebrow="Chỉ tay tiên đoán" title="Xem chỉ tay với chuyên gia của" accent="tâm minh" align="left">
                Đặt lịch xem chỉ tay trực tiếp cùng các Thầy của Tâm Minh - đọc đường sinh đạo, trí đạo, tâm đạo để soi rọi sức khỏe, sự nghiệp và tình duyên.
            </x-ui.section-heading>

            <div class="mt-7 grid grid-cols-3 gap-3 sm:flex sm:gap-6" data-reveal-stagger>
                <x-ui.feature icon="user" label="Chuyên gia trực tiếp luận giải" data-reveal />
                <x-ui.feature icon="book" label="Luận đủ ba đường chỉ tay chính" data-reveal />
                <x-ui.feature icon="shield" label="Gói Cơ bản hoàn toàn miễn phí" data-reveal />
            </div>
        </div>

        <x-ui.form-card title="Đặt lịch xem chỉ tay" class="relative z-10" data-reveal="fade-left">
            <form class="space-y-4" data-toast-on-submit="Đã nhận yêu cầu đặt lịch - Tâm Minh sẽ liên hệ xác nhận sớm nhất.">
                <x-ui.field label="Họ và tên" for="palm-name" name="name">
                    <x-ui.input id="palm-name" name="name" placeholder="Nhập họ và tên" autocomplete="name" />
                </x-ui.field>

                <x-ui.field label="Giới tính">
                    <div class="flex flex-wrap items-center gap-x-7 gap-y-2">
                        <x-ui.radio name="palm_gender" value="male" checked>Nam</x-ui.radio>
                        <x-ui.radio name="palm_gender" value="female">Nữ</x-ui.radio>
                    </div>
                </x-ui.field>

                <x-ui.field label="Ngày sinh">
                    <x-ui.date-select name="palm_birth" />
                </x-ui.field>

                <x-ui.field label="Số điện thoại" for="palm-phone" name="phone">
                    <x-ui.input id="palm-phone" type="tel" name="phone" inputmode="tel" placeholder="Nhập số điện thoại" autocomplete="tel" />
                </x-ui.field>

                <x-ui.field label="Gói xem">
                    <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                        <x-ui.radio-card name="package" value="basic" title="Cơ bản" description="Miễn phí" highlight checked />
                        <x-ui.radio-card name="package" value="premium" title="Chuyên sâu" description="Luận giải chi tiết cùng Thầy" />
                    </div>
                </x-ui.field>

                <x-ui.button type="submit" size="lg" block trailing-icon="arrow" class="uppercase tracking-wide">
                    Đặt lịch ngay
                </x-ui.button>
            </form>
        </x-ui.form-card>
    </div>
</section>
