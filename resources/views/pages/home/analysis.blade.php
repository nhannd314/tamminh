<section class="border-t border-cream-300 bg-cream-50 py-16 lg:py-20">
    <div class="tm-container">
        <x-ui.section-heading eyebrow="Toàn cảnh luận giải" title="Phân tích" accent="tướng số" rule class="mb-12 max-w-[760px] lg:mb-14" data-reveal>
            Mỗi báo cáo từ Tâm Minh đi qua bảy lớp luận giải truyền thống - từ ngũ hành tướng pháp đến lời khuyên cải vận - kết tinh từ cổ thư nhân tướng học và kinh nghiệm thực luận nhiều năm của các Thầy.
        </x-ui.section-heading>

        {{-- Row 1 --}}
        <div class="mb-5 grid gap-5 md:grid-cols-2 lg:grid-cols-[1fr_1fr_1.05fr]" data-reveal-stagger>
            <x-ui.card number="01" title="Ngũ hành tướng pháp" description="Năm hình mặt cơ bản trong tướng pháp Á Đông - định khung cốt cách." class="border-copper-200" data-reveal>
                <ul class="mt-1 flex flex-col gap-3">
                    @foreach ($nguHanh as $element)
                        <li class="flex items-start gap-3.5">
                            <x-ui.glyph>{{ $element['glyph'] }}</x-ui.glyph>
                            <div>
                                <div class="mb-0.5 text-base font-semibold text-ink-600">Hành {{ $element['name'] }}</div>
                                <div class="text-sm leading-snug text-ink-300">{{ $element['description'] }}</div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </x-ui.card>

            <x-ui.card number="02" class="border-copper-200" data-reveal>
                <h3 class="tm-card-title pr-10">Tổng quan thần khí sắc</h3>
                <p class="tm-card-desc">Ba tầng năng lượng mà người xem tướng đọc trước tiên trên khuôn mặt - phản ánh trạng thái tinh thần, sức khỏe và vận khí hiện tại của bạn.</p>
                <div class="mt-1 flex flex-col divide-y divide-cream-300">
                    @foreach ($thanKhiSac as $layer)
                        <div class="flex items-start gap-4 py-3.5 first:pt-1">
                            <x-ui.glyph tone="navy" size="md">{{ $layer['glyph'] }}</x-ui.glyph>
                            <div>
                                <div class="mb-1 font-serif text-lg font-semibold text-ink-600">{{ $layer['name'] }}</div>
                                <div class="text-sm leading-snug text-ink-500">{{ $layer['description'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>

            <x-ui.card number="03" title="Tam đình" description="Khuôn mặt chia ba đoạn ngang - mỗi đoạn ứng một thời kỳ vận mệnh." class="border-copper-200 md:col-span-2 lg:col-span-1" data-reveal>
                <div class="mt-1 flex flex-col gap-3.5">
                    @foreach ($tamDinh as $zone)
                        <div class="flex items-center gap-5">
                            <x-ui.glyph>{{ $zone['glyph'] }}</x-ui.glyph>
                            <div>
                                <div class="mb-0.5 text-base font-semibold text-ink-600">{{ $zone['name'] }}</div>
                                <div class="mb-1 text-sm tracking-wide text-copper-600">{{ $zone['range'] }}</div>
                                <div class="text-sm leading-snug text-ink-300">{{ $zone['meaning'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="tm-callout mt-auto">
                    <x-ui.icon name="sparkle" :size="16" class="mt-px text-copper-500" />
                    <p class="text-sm leading-relaxed text-ink-500">Tam đình cân đối - ba đoạn dài đều nhau - báo hiệu cuộc đời hanh thông, phúc lộc viên mãn. Đoạn nào nổi trội sẽ cho biết thời kỳ phát vận rõ rệt nhất.</p>
                </div>
            </x-ui.card>
        </div>

        {{-- Row 2: Ngũ quan --}}
        <x-ui.card :hover="false" number="04" title="Ngũ quan" description="Năm giác quan trên khuôn mặt - mỗi quan ứng một vận trình, một phần đời." class="mb-5 pb-8" data-reveal>
            <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-6 border-t border-cream-300 pt-6 sm:grid-cols-3 lg:grid-cols-5 lg:gap-0 lg:divide-x lg:divide-cream-300">
                @foreach ($nguQuan as $sense)
                    <div class="flex flex-col items-center gap-2 px-2 text-center lg:px-5">
                        <x-ui.glyph tone="ring" size="lg" class="mb-1.5">{{ $sense['glyph'] }}</x-ui.glyph>
                        <div class="font-serif text-lg leading-tight font-semibold text-ink-600 lg:text-xl">{{ $sense['name'] }}</div>
                        <div class="text-sm font-semibold tracking-[0.12em] text-copper-600 uppercase">{{ $sense['subtitle'] }}</div>
                        <div class="max-w-[160px] text-sm leading-snug text-ink-300">{{ $sense['description'] }}</div>
                    </div>
                @endforeach
            </div>
        </x-ui.card>

        {{-- Row 3: Thập nhị cung --}}
        <x-ui.card :hover="false" number="05" title="Thập nhị cung mệnh" description="12 cung trên lá số - mỗi cung soi rọi một khía cạnh của vận mệnh." class="mb-5 pb-8" data-reveal>
            <div class="mt-3 grid grid-cols-2 gap-2.5 sm:grid-cols-3 lg:grid-cols-4 lg:gap-3.5">
                @foreach ($thapNhiCung as $palace)
                    <div class="relative flex flex-col gap-1 rounded-[10px] border border-cream-300 bg-cream-50 p-4 transition-all duration-200 hover:-translate-y-px hover:border-copper-300 hover:bg-white hover:shadow-soft-sm">
                        <div class="absolute top-3 right-3.5 text-sm font-semibold tracking-wide text-ink-200">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</div>
                        <div class="mb-1.5 font-serif text-3xl leading-none font-semibold text-copper-700">{{ $palace['glyph'] }}</div>
                        <div class="text-base font-semibold text-ink-600">{{ $palace['name'] }}</div>
                        <div class="text-sm leading-snug text-ink-300">{{ $palace['description'] }}</div>
                    </div>
                @endforeach
            </div>
        </x-ui.card>

        {{-- Row 4 --}}
        <div class="grid gap-5 lg:grid-cols-2" data-reveal-stagger>
            <x-ui.card number="06" title="Phong thủy ứng dụng" description="Sau khi luận tướng, Tâm Minh đưa ra bốn nhóm chỉ dẫn phong thủy theo bản mệnh của bạn." class="border-copper-200" data-reveal>
                <div class="mt-2 grid grid-cols-1 gap-x-5 gap-y-4 sm:grid-cols-2">
                    @foreach ($phongThuy as $tip)
                        <div class="flex items-start gap-3">
                            <x-ui.icon-ring :icon="$tip['icon']" size="sm" />
                            <div>
                                <div class="mb-1 text-base font-semibold text-ink-600">{{ $tip['title'] }}</div>
                                <div class="text-sm leading-snug text-ink-300">{{ $tip['description'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>

            <x-ui.card number="07" title="Lời khuyên cải vận" description="Tâm Minh không chỉ luận - chúng tôi đưa ra đường đi để bạn chủ động cải vận." class="border-copper-200 bg-linear-to-b from-white to-cream-100" data-reveal>
                <dl class="mt-1.5 flex flex-col divide-y divide-cream-300">
                    @foreach ($advice as $aspect => $guidance)
                        <div class="grid gap-1 py-3 first:pt-1 sm:grid-cols-[110px_1fr] sm:items-baseline sm:gap-4">
                            <dt class="text-xs font-bold tracking-[0.12em] text-copper-600 uppercase sm:text-sm">{{ $aspect }}</dt>
                            <dd class="text-[15px] leading-relaxed text-ink-500 sm:text-base">{{ $guidance }}</dd>
                        </div>
                    @endforeach
                </dl>
                <blockquote class="tm-quote mt-3">"Tướng tự tâm sinh, tâm tự tướng diệt - vận mệnh nằm trong tay người biết tu sửa chính mình."</blockquote>
            </x-ui.card>
        </div>
    </div>
</section>
