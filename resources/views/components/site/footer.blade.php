@php
    $columns = [
        'Dịch vụ' => ['Xem tướng số', 'Xem chỉ tay', 'Phong thủy nhà ở', 'Tư vấn 1-1 với Thầy', 'Xem ngày tốt', 'Đặt tên con'],
        'Khám phá' => ['Trang chủ', 'Đội ngũ chuyên gia', 'Kiến thức tướng số', 'Câu chuyện khách hàng', 'Cộng tác viên', 'Chương trình affiliate'],
        'Hỗ trợ' => ['Câu hỏi thường gặp', 'Chính sách bảo mật', 'Điều khoản dịch vụ', 'Chính sách hoàn tiền', 'Hướng dẫn sử dụng', 'Liên hệ'],
    ];

    $contacts = [
        'phone' => '0888-11-2626',
        'mail' => 'contact@tamminh.vn',
        'pin' => 'Số 26A ngõ 22 Thạch Bàn, Long Biên, Hà Nội',
    ];
@endphp

<footer class="relative bg-navy-500 pt-12 text-white/78 lg:pt-16">
    <div class="absolute inset-x-0 top-0 h-0.5 bg-linear-to-r from-transparent via-copper-400 to-transparent opacity-60"></div>

    <div class="tm-container">
        <div class="grid grid-cols-2 gap-x-6 gap-y-8 pb-10 sm:grid-cols-3 lg:grid-cols-[1.5fr_1fr_1fr_1fr_1.4fr] lg:gap-10 lg:pb-14">
            <div class="col-span-2 flex flex-col gap-4 sm:col-span-3 lg:col-span-1">
                <x-site.logo tone="dark" tagline="Hiểu mình - Biết vận - An tâm sống" />
                <p class="text-sm leading-relaxed text-white/65">Nền tảng xem tướng số, chỉ tay và phong thủy sử dụng công nghệ phân tích khuôn mặt kết hợp với tri thức nhân tướng học phương Đông</p>
                <ul class="mt-1.5 flex flex-col gap-2.5">
                    @foreach ($contacts as $icon => $value)
                        <li class="flex items-center gap-3 text-sm">
                            <x-ui.icon :name="$icon" :size="16" class="text-copper-400" /> {{ $value }}
                        </li>
                    @endforeach
                </ul>
            </div>

            @foreach ($columns as $heading => $links)
                <div>
                    <div class="tm-overline mb-[18px] text-copper-400">{{ $heading }}</div>
                    <ul class="flex flex-col gap-2.5">
                        @foreach ($links as $link)
                            <li><a href="#" class="text-sm leading-snug text-white/70 transition-colors hover:text-copper-400">{{ $link }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endforeach

            <div class="col-span-2 flex flex-col sm:col-span-3 lg:col-span-1">
                <div class="tm-overline mb-[18px] text-copper-400">Bản tin Tâm Minh</div>
                <p class="mb-4 text-sm leading-relaxed text-white/65">Mỗi tháng một bài luận sâu về tướng số, tử vi và phong thủy - thẳng đến hộp thư của bạn.</p>
                <form data-toast-on-submit="Cảm ơn bạn - chúng tôi sẽ gửi bản tin đầu tiên trong tuần này." class="flex items-center gap-2 rounded-full border border-white/15 bg-white/6 p-1.5 pl-2 transition-colors focus-within:border-copper-400">
                    <input type="email" name="email" required placeholder="email@cua.ban" aria-label="Email" class="min-w-0 flex-1 bg-transparent px-3 py-2.5 text-sm text-white outline-none placeholder:text-white/45" />
                    <x-ui.button type="submit" size="icon" class="size-10" aria-label="Đăng ký">
                        <x-ui.icon name="arrow" :size="18" />
                    </x-ui.button>
                </form>
                <p class="mt-2.5 text-xs leading-relaxed text-white/50">Không spam. Hủy đăng ký bất cứ lúc nào.</p>
                <div class="mt-5 flex gap-2.5">
                    @foreach (['facebook', 'youtube', 'tiktok', 'zalo'] as $network)
                        <x-ui.social-button :network="$network" />
                    @endforeach
                </div>
            </div>
        </div>

        <div class="flex flex-col items-start justify-between gap-3 border-t border-white/10 pt-5 pb-[max(1.25rem,env(safe-area-inset-bottom))] sm:flex-row sm:items-center">
            <div class="text-xs leading-relaxed text-white/55">
                © {{ date('Y') }} Tâm Minh JSC. <span class="mx-1.5 text-white/30">·</span> MST 0108234567 <span class="mx-1.5 text-white/30">·</span> Đã đăng ký với Bộ Công Thương
            </div>
            <div class="flex flex-wrap gap-x-5 gap-y-2.5">
                @foreach (['Điều khoản', 'Bảo mật', 'Cookie', 'Sitemap'] as $legal)
                    <a href="#" class="text-xs text-white/60 transition-colors hover:text-copper-400">{{ $legal }}</a>
                @endforeach
            </div>
        </div>
    </div>
</footer>
