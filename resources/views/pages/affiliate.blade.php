@php
    // Chưa đăng nhập: nút trỏ vào trang affiliate trong tài khoản, middleware auth đưa sang đăng nhập rồi quay lại đúng trang này.
    $primaryLabel = match (true) {
        $isAffiliate => 'Vào trang affiliate của bạn',
        $customer !== null => 'Tham gia ngay - miễn phí',
        default => 'Đăng nhập để tham gia',
    };
    $faqs = [
        ['Tham gia có mất phí không?', 'Hoàn toàn miễn phí. Bạn chỉ cần một tài khoản Tâm Minh, bấm tham gia là có ngay link giới thiệu riêng.'],
        ['Khi nào hoa hồng được xác nhận?', "Với xem tướng online, hoa hồng được xác nhận ngay khi khách thanh toán mở khoá luận giải. Với vật phẩm phong thủy, hoa hồng được xác nhận khi đơn giao thành công; đơn bị huỷ thì không tính hoa hồng."],
        ['Khách bấm link nhưng vài ngày sau mới mua thì sao?', "Link có hiệu lực {$cookieDays} ngày kể từ lần bấm gần nhất trên cùng thiết bị. Trong thời gian đó khách mua lúc nào bạn cũng được tính. Nếu khách bấm link của người khác sau đó thì đơn tính cho người giới thiệu sau cùng."],
        ['Tôi nhận tiền bằng cách nào?', 'Bạn khai báo tài khoản ngân hàng trong trang affiliate. Tâm Minh chuyển khoản các khoản hoa hồng đã xác nhận và cập nhật trạng thái "Đã chi trả" để bạn theo dõi.'],
        ['Tôi tự mua qua link của mình có được hoa hồng không?', 'Không. Đơn đặt bằng chính tài khoản của bạn không được tính hoa hồng.'],
    ];
@endphp

<x-layouts.app title="Kiếm tiền affiliate" description="Chương trình affiliate Tâm Minh: chia sẻ link xem tướng online và vật phẩm phong thủy, nhận hoa hồng {{ $analysisPercent }}% - {{ $productPercent }}% mỗi đơn thành công.">
    {{-- Mở đầu --}}
    <section class="relative overflow-x-clip border-b border-cream-300 bg-cream-50 py-12 lg:py-20">
        <div class="tm-container grid items-center gap-10 lg:grid-cols-[minmax(0,1fr)_420px] lg:gap-16">
            <div class="flex flex-col items-start gap-5">
                <x-ui.eyebrow>Chương trình affiliate</x-ui.eyebrow>
                <h1 class="font-serif text-4xl leading-tight font-medium tracking-[-0.01em] text-ink-600 sm:text-5xl">
                    Chia sẻ Tâm Minh,<span class="tm-accent"> nhận hoa hồng</span>
                </h1>
                <p class="tm-lead max-w-[560px]">Giới thiệu bạn bè xem tướng khuôn mặt online và mua vật phẩm phong thủy qua link riêng của bạn. Mỗi đơn thành công, bạn nhận hoa hồng chuyển khoản trực tiếp - không vốn, không ôm hàng.</p>

                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <x-ui.button :href="route('account.affiliate.show')" size="lg" :icon="$customer ? 'coins' : 'user'" :icon-size="18">{{ $primaryLabel }}</x-ui.button>
                    @guest('customer')
                        <x-ui.button :href="route('affiliate.register')" size="lg" variant="outline">Tạo tài khoản</x-ui.button>
                    @endguest
                </div>
                @guest('customer')
                    <p class="text-sm text-ink-400">Chưa có tài khoản? Đăng ký miễn phí chưa đến một phút.</p>
                @endguest
            </div>

            <div class="grid gap-4">
                @foreach ([
                    ['face', $analysisPercent, 'Xem tướng số online', 'Trên mỗi lượt khách mở khoá luận giải - xác nhận ngay khi thanh toán.'],
                    ['gift', $productPercent, 'Vật phẩm phong thủy', 'Trên tiền hàng mỗi đơn (không tính phí giao) - xác nhận khi giao thành công.'],
                ] as [$icon, $percent, $title, $description])
                    <div class="flex items-center gap-5 rounded-2xl border border-cream-300 bg-white p-5 shadow-soft-sm sm:p-6">
                        <x-ui.icon-ring :icon="$icon" size="md" />
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-semibold text-ink-600">{{ $title }}</div>
                            <p class="mt-1 text-sm leading-snug text-ink-400">{{ $description }}</p>
                        </div>
                        <div class="font-serif text-4xl font-semibold text-copper-600">{{ $percent }}%</div>
                    </div>
                @endforeach
                <p class="text-center text-sm text-ink-400">Link giới thiệu có hiệu lực <b class="font-semibold text-ink-600">{{ $cookieDays }} ngày</b> kể từ lần khách bấm gần nhất.</p>
            </div>
        </div>
    </section>

    {{-- Cách hoạt động --}}
    <section class="py-14 lg:py-20">
        <div class="tm-container">
            <x-ui.section-heading eyebrow="Cách hoạt động" title="3 bước" accent="bắt đầu kiếm tiền" size="md" />

            <ol class="mt-10 grid gap-5 md:grid-cols-3">
                @foreach ([
                    ['user', 'Đăng nhập & tham gia', 'Đăng nhập tài khoản Tâm Minh, bấm "Tham gia" để nhận mã và link giới thiệu riêng.'],
                    ['share', 'Chia sẻ link', 'Gửi link qua Facebook, Zalo, TikTok, nhóm chat… Thêm ?ref=MÃ vào bất kỳ trang nào để giới thiệu đúng trang đó.'],
                    ['coins', 'Nhận hoa hồng', 'Theo dõi từng đơn trong trang affiliate. Tâm Minh chuyển khoản hoa hồng đã xác nhận vào tài khoản của bạn.'],
                ] as [$icon, $title, $description])
                    <li class="flex flex-col gap-4 rounded-2xl border border-cream-300 bg-white p-6 shadow-soft-sm">
                        <div class="flex items-center gap-3">
                            <span class="flex size-8 items-center justify-center rounded-full bg-navy-500 text-sm font-semibold text-copper-300">{{ $loop->iteration }}</span>
                            <x-ui.icon :name="$icon" :size="22" class="text-copper-500" />
                        </div>
                        <h3 class="font-serif text-xl font-semibold text-ink-600">{{ $title }}</h3>
                        <p class="text-base leading-relaxed text-ink-400">{{ $description }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Hỏi đáp --}}
    <section class="border-t border-cream-300 bg-cream-100 py-14 lg:py-20">
        <div class="tm-container grid items-start gap-8 lg:grid-cols-[380px_1fr] lg:gap-14">
            <div class="flex flex-col items-start gap-6 lg:sticky lg:top-[100px]">
                <x-ui.section-heading eyebrow="Hỏi & Đáp" title="Câu hỏi" accent="thường gặp" size="sm" align="left">
                    Những điều cần biết trước khi bắt đầu giới thiệu Tâm Minh.
                </x-ui.section-heading>

                <div class="w-full rounded-2xl border border-cream-300 bg-white p-6 shadow-soft-sm">
                    <div class="mb-2 font-serif text-xl font-semibold text-ink-600">Sẵn sàng bắt đầu?</div>
                    <p class="mb-4 text-base leading-relaxed text-ink-400">Tham gia miễn phí, nhận link ngay sau khi đăng nhập.</p>
                    <x-ui.button :href="route('account.affiliate.show')" block>{{ $primaryLabel }}</x-ui.button>
                </div>
            </div>

            <x-ui.accordion>
                @foreach ($faqs as [$question, $answer])
                    <x-ui.accordion-item :question="$question" :open="$loop->first">{{ $answer }}</x-ui.accordion-item>
                @endforeach
            </x-ui.accordion>
        </div>
    </section>
</x-layouts.app>
