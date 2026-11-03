@php
    $money = fn (int $amount): string => number_format($amount, 0, ',', '.').'đ';
    $paidSectionCount = count(\App\AnalysisSection::paid());
    $badge = 'shrink-0 rounded-full px-2.5 py-0.5 text-xs font-semibold';
@endphp

<x-layouts.app title="Tài khoản của tôi" description="Thông tin tài khoản, đơn hàng và dịch vụ đã đăng ký tại Tâm Minh.">
    @push('head')
        <meta name="robots" content="noindex, nofollow">
    @endpush

    <div class="tm-container max-w-[1040px] pt-6 pb-16">
        <x-ui.breadcrumb class="mb-6" :items="['Trang chủ' => route('home'), 'Tài khoản' => null]" />

        <div class="grid items-start gap-6 lg:grid-cols-[320px_minmax(0,1fr)] lg:gap-8">
            <x-account.sidebar :user="$user" />

            <div class="flex flex-col gap-6">
                <h1 class="sr-only">Tài khoản của tôi</h1>

                {{-- Thông tin cá nhân --}}
                <section class="rounded-2xl border border-cream-300 bg-white p-5 shadow-soft-sm sm:p-7" aria-labelledby="account-profile">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <h2 id="account-profile" class="font-serif text-xl font-semibold text-ink-600">Thông tin cá nhân</h2>
                        <x-ui.button :href="route('account.edit')" size="sm" variant="outline" icon="pencil" :icon-size="14">Chỉnh sửa</x-ui.button>
                    </div>
                    <dl class="grid gap-3 text-base sm:grid-cols-[180px_minmax(0,1fr)]">
                        @foreach ([
                            'Họ và tên' => $user->name,
                            'Email' => $user->email,
                            'Số điện thoại' => $user->phone,
                            'Địa chỉ giao hàng' => $user->address,
                            'Thành viên từ' => $user->created_at->format('m/Y'),
                        ] as $label => $value)
                            <dt class="text-ink-400">{{ $label }}</dt>
                            <dd class="font-medium break-words text-ink-600">{{ $value ?: '—' }}</dd>
                        @endforeach
                    </dl>
                </section>
                {{-- Đơn hàng --}}
                <section class="rounded-2xl border border-cream-300 bg-white p-5 shadow-soft-sm sm:p-7" aria-labelledby="account-orders">
                    <h2 id="account-orders" class="mb-4 font-serif text-xl font-semibold text-ink-600">Đơn hàng đã mua</h2>

                    @forelse ($orders as $order)
                        <a href="{{ route('orders.show', $order) }}" class="-mx-2 flex items-center gap-4 rounded-lg border-b border-cream-200 px-2 py-3.5 last:border-b-0 hover:bg-cream-50">
                            <div class="min-w-0 flex-1">
                                <div class="font-mono text-sm font-semibold tracking-wider text-ink-600">{{ $order->code }}</div>
                                <div class="mt-0.5 text-xs text-ink-400">{{ $order->created_at->format('d/m/Y H:i') }} · {{ (int) $order->items_sum_quantity }} vật phẩm</div>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="font-semibold whitespace-nowrap text-copper-600">{{ $money($order->total) }}</span>
                                <span @class([
                                    $badge,
                                    'bg-cream-200 text-ink-400' => $order->isCancelled(),
                                    'bg-warning/10 text-warning' => $order->awaitsOnlinePayment(),
                                    'bg-success/10 text-success' => ! $order->isCancelled() && ! $order->awaitsOnlinePayment(),
                                ])>{{ $order->awaitsOnlinePayment() ? 'Chờ thanh toán' : $order->status->label() }}</span>
                            </div>
                        </a>
                    @empty
                        <div class="flex flex-col items-start gap-3 rounded-xl bg-cream-50 p-5 text-sm text-ink-400">
                            Bạn chưa có đơn hàng nào.
                            <x-ui.button :href="route('products.index')" size="sm" variant="outline">Xem vật phẩm phong thủy</x-ui.button>
                        </div>
                    @endforelse
                </section>

                {{-- Dịch vụ đã đăng ký --}}
                <section class="rounded-2xl border border-cream-300 bg-white p-5 shadow-soft-sm sm:p-7" aria-labelledby="account-services">
                    <h2 id="account-services" class="mb-5 font-serif text-xl font-semibold text-ink-600">Dịch vụ đã đăng ký</h2>

                    <h3 class="mb-2 flex items-center gap-2 text-sm font-semibold tracking-[0.14em] text-ink-500 uppercase">
                        <x-ui.icon name="face" :size="16" class="text-copper-500" /> Xem tướng khuôn mặt
                    </h3>
                    @forelse ($analyses as $analysis)
                        @php($unlockedCount = $paidSectionCount - count($analysis->lockedSections()))
                        <a href="{{ route('analyses.show', $analysis) }}" class="-mx-2 flex items-center gap-4 rounded-lg border-b border-cream-200 px-2 py-3.5 last:border-b-0 hover:bg-cream-50">
                            <div class="min-w-0 flex-1">
                                <div class="truncate font-semibold text-ink-600">{{ $analysis->name }}</div>
                                <div class="mt-0.5 text-xs text-ink-400">{{ $analysis->created_at->format('d/m/Y H:i') }} · Ngày sinh {{ $analysis->birth_date->format('d/m/Y') }}</div>
                            </div>
                            <span @class([
                                $badge,
                                'bg-success/10 text-success' => $analysis->isFullyUnlocked(),
                                'bg-copper-50 text-copper-700' => ! $analysis->isFullyUnlocked(),
                            ])>{{ $analysis->isFullyUnlocked() ? 'Trọn gói' : "Đã mở {$unlockedCount}/{$paidSectionCount} phần" }}</span>
                        </a>
                    @empty
                        <div class="flex flex-col items-start gap-3 rounded-xl bg-cream-50 p-5 text-sm text-ink-400">
                            Bạn chưa có lượt xem tướng nào.
                            <x-ui.button :href="route('home').'#xem-tuong'" size="sm" variant="outline">Xem tướng miễn phí</x-ui.button>
                        </div>
                    @endforelse

                    <h3 class="mt-7 mb-2 flex items-center gap-2 text-sm font-semibold tracking-[0.14em] text-ink-500 uppercase">
                        <x-ui.icon name="calendar-check" :size="16" class="text-copper-500" /> Lịch xem chỉ tay
                    </h3>
                    @forelse ($palmReadingBookings as $booking)
                        <div class="flex items-center gap-4 border-b border-cream-200 py-3.5 last:border-b-0">
                            <div class="min-w-0 flex-1">
                                <div class="truncate font-semibold text-ink-600">Gói {{ $booking->package->label() }}</div>
                                <div class="mt-0.5 text-xs text-ink-400">{{ $booking->created_at->format('d/m/Y H:i') }} · {{ $booking->name }} · {{ $booking->phone }}</div>
                            </div>
                            <span @class([
                                $badge,
                                'bg-success/10 text-success' => $booking->isContacted(),
                                'bg-warning/10 text-warning' => ! $booking->isContacted(),
                            ])>{{ $booking->isContacted() ? 'Đã liên hệ' : 'Chờ liên hệ' }}</span>
                        </div>
                    @empty
                        <div class="flex flex-col items-start gap-3 rounded-xl bg-cream-50 p-5 text-sm text-ink-400">
                            Bạn chưa đặt lịch xem chỉ tay.
                            <x-ui.button :href="route('home').'#xem-chi-tay'" size="sm" variant="outline">Đặt lịch xem chỉ tay</x-ui.button>
                        </div>
                    @endforelse
                </section>
            </div>
        </div>
    </div>
</x-layouts.app>
