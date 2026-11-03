@php
    $money = fn (int $amount): string => number_format($amount, 0, ',', '.').'đ';
    $isOnline = $order->payment_method === \App\OrderPaymentMethod::Online;
@endphp

<x-layouts.app :title="'Đơn hàng '.$order->code" description="Thông tin đơn hàng Tâm Minh.">
    @push('head')
        <meta name="robots" content="noindex, nofollow">
    @endpush

    <div class="tm-container max-w-[1040px] pt-6 pb-16">
        <x-ui.breadcrumb class="mb-6" :items="['Trang chủ' => route('home'), 'Đơn hàng '.$order->code => null]" />

        <section class="flex flex-col items-center gap-3 rounded-2xl border border-cream-300 bg-white px-6 py-10 text-center shadow-soft-sm">
            @if ($order->isCancelled())
                <x-ui.icon-ring icon="x" size="lg" />
                <h1 class="font-serif text-3xl font-semibold text-ink-600">Đơn hàng đã huỷ</h1>
                <p class="max-w-[520px] text-base leading-relaxed text-ink-400">Đơn <b class="font-semibold text-ink-600">{{ $order->code }}</b> đã được huỷ. Liên hệ Tâm Minh nếu bạn cần hỗ trợ.</p>
            @elseif ($payment)
                <x-ui.icon-ring icon="lock" size="lg" />
                <h1 class="font-serif text-3xl font-semibold text-ink-600">Đặt hàng thành công - chờ thanh toán</h1>
                <p class="max-w-[560px] text-base leading-relaxed text-ink-400">Chuyển khoản <b class="font-semibold text-copper-600">{{ $money($order->total) }}</b> để hoàn tất đơn <b class="font-semibold text-ink-600">{{ $order->code }}</b>. Đơn được giao miễn phí sau khi nhận tiền.</p>
            @else
                <x-ui.icon-ring icon="check" size="lg" />
                <h1 class="font-serif text-3xl font-semibold text-ink-600">Cảm ơn bạn đã đặt hàng!</h1>
                <p class="max-w-[560px] text-base leading-relaxed text-ink-400">
                    Đơn <b class="font-semibold text-ink-600">{{ $order->code }}</b>
                    {{ $isOnline ? 'đã được thanh toán.' : 'đã được ghi nhận.' }}
                    Tâm Minh sẽ gọi số <b class="font-semibold text-ink-600">{{ $order->phone }}</b> để xác nhận và khai quang trước khi giao.
                </p>
            @endif
        </section>

        <div class="mt-6 grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_380px] lg:gap-8">
            <div class="flex flex-col gap-6">
                @if ($payment)
                    <section class="rounded-2xl border border-copper-200 bg-white p-5 shadow-soft-sm sm:p-7" aria-labelledby="order-payment"
                        data-payment-watch="{{ route('orders.payment-status', $order) }}" data-payment-paid-message="Đã nhận thanh toán, đang cập nhật đơn…">
                        <h2 id="order-payment" class="mb-5 font-serif text-xl font-semibold text-ink-600">Chuyển khoản</h2>

                        @if ($payment->isConfigured())
                            <div class="grid gap-6 sm:grid-cols-[240px_minmax(0,1fr)]">
                                <div class="flex flex-col items-center gap-2">
                                    <img src="{{ $payment->qrUrl() }}" alt="Mã QR chuyển khoản {{ $money($order->total) }}" width="240" height="240"
                                        class="size-60 rounded-xl border border-cream-300 bg-white object-contain p-2" />
                                    <p class="text-center text-xs text-ink-400">Mở app ngân hàng và quét mã QR - số tiền và nội dung được điền sẵn.</p>
                                </div>

                                <dl class="flex flex-col gap-3 text-sm">
                                    @foreach ([['Ngân hàng', $payment->bank, false], ['Số tài khoản', $payment->accountNumber, true], ['Chủ tài khoản', $payment->accountName, false]] as [$label, $value, $copyable])
                                        <div class="flex items-center justify-between gap-3 border-b border-cream-200 pb-3">
                                            <dt class="text-ink-400">{{ $label }}</dt>
                                            <dd class="flex items-center gap-2 text-right font-semibold text-ink-600">
                                                {{ $value ?: '—' }}
                                                @if ($copyable && $value)
                                                    <button type="button" class="cursor-pointer text-copper-500 hover:text-copper-700" data-copy="{{ $value }}" data-copy-message="Đã sao chép {{ mb_strtolower($label) }}." aria-label="Sao chép {{ mb_strtolower($label) }}">
                                                        <x-ui.icon name="copy" :size="16" />
                                                    </button>
                                                @endif
                                            </dd>
                                        </div>
                                    @endforeach

                                    <div class="flex items-center justify-between gap-3 border-b border-cream-200 pb-3">
                                        <dt class="text-ink-400">Số tiền</dt>
                                        <dd class="font-serif text-xl font-semibold text-copper-600">{{ $money($order->total) }}</dd>
                                    </div>

                                    <div class="flex flex-col gap-1.5 rounded-lg border border-copper-200 bg-copper-50 p-3">
                                        <dt class="text-xs font-semibold tracking-eyebrow text-copper-700 uppercase">Nội dung chuyển khoản</dt>
                                        <dd class="flex items-center justify-between gap-2">
                                            <span class="font-mono text-lg font-semibold tracking-wider text-ink-600">{{ $payment->content }}</span>
                                            <x-ui.button size="sm" variant="outline" :data-copy="$payment->content" data-copy-message="Đã sao chép nội dung chuyển khoản.">Sao chép</x-ui.button>
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            @if ($payment->note)
                                <p class="mt-5 text-sm leading-relaxed text-ink-500">{{ $payment->note }}</p>
                            @endif

                            <div class="mt-4 flex items-center gap-2.5 rounded-lg bg-cream-100 px-4 py-3 text-sm text-ink-500" data-payment-message>
                                <span class="size-4 shrink-0 animate-spin rounded-full border-2 border-copper-300 border-t-transparent"></span>
                                Đang chờ thanh toán… Trang sẽ tự cập nhật khi nhận được tiền.
                            </div>
                        @else
                            <p class="text-base text-ink-500">
                                Tài khoản nhận tiền chưa được cấu hình. Vui lòng liên hệ Tâm Minh để thanh toán đơn
                                <b class="font-semibold text-ink-600">{{ $order->code }}</b> ({{ $money($order->total) }}).
                            </p>
                        @endif
                    </section>
                @endif

                <section class="rounded-2xl border border-cream-300 bg-white p-5 shadow-soft-sm sm:p-7" aria-labelledby="order-shipping">
                    <h2 id="order-shipping" class="mb-4 font-serif text-xl font-semibold text-ink-600">Thông tin nhận hàng</h2>
                    <dl class="grid gap-3 text-base sm:grid-cols-[160px_minmax(0,1fr)]">
                        @foreach (['Người nhận' => $order->customer_name, 'Số điện thoại' => $order->phone, 'Địa chỉ' => $order->address, 'Thanh toán' => $order->payment_method->label(), 'Ghi chú' => $order->note] as $label => $value)
                            @if (filled($value))
                                <dt class="text-ink-400">{{ $label }}</dt>
                                <dd class="font-medium break-words text-ink-600">{{ $value }}</dd>
                            @endif
                        @endforeach
                    </dl>
                </section>
            </div>

            <aside class="flex flex-col gap-4 rounded-2xl border border-cream-300 bg-white p-6 shadow-soft-sm">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="font-sans text-sm font-semibold tracking-[0.14em] text-ink-600 uppercase">Đơn {{ $order->code }}</h2>
                    <span @class([
                        'rounded-full px-2.5 py-0.5 text-xs font-semibold',
                        'bg-success/10 text-success' => $order->isPaid(),
                        'bg-cream-200 text-ink-500' => ! $order->isPaid(),
                    ])>{{ $order->isPaid() ? 'Đã thanh toán' : ($isOnline ? 'Chờ thanh toán' : 'Thu tiền khi giao') }}</span>
                </div>

                <ul class="flex flex-col gap-3.5 border-b border-cream-300 pb-4">
                    @foreach ($order->items as $item)
                        <li class="flex gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="text-sm leading-snug font-semibold text-ink-600">
                                    @if ($item->product)
                                        <a href="{{ route('products.show', $item->product) }}" class="hover:text-copper-700">{{ $item->product_name }}</a>
                                    @else
                                        {{ $item->product_name }}
                                    @endif
                                    <span class="font-normal text-ink-400">× {{ $item->quantity }}</span>
                                </div>
                                @if ($item->optionsLabel())
                                    <div class="mt-0.5 text-xs text-ink-400">{{ $item->optionsLabel() }}</div>
                                @endif
                            </div>
                            <div class="text-sm font-semibold whitespace-nowrap text-ink-600">{{ $money($item->subtotal) }}</div>
                        </li>
                    @endforeach
                </ul>

                <dl class="flex flex-col gap-3 text-base">
                    <div class="flex justify-between gap-4">
                        <dt class="text-ink-400">Tạm tính</dt>
                        <dd class="font-medium text-ink-600">{{ $money($order->subtotal) }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-ink-400">Phí giao hàng</dt>
                        <dd class="font-medium text-ink-600">{{ $order->shipping_fee > 0 ? $money($order->shipping_fee) : 'Miễn phí' }}</dd>
                    </div>
                    <div class="flex items-baseline justify-between gap-4 border-t border-cream-300 pt-4">
                        <dt class="font-semibold text-ink-600">Tổng cộng</dt>
                        <dd class="font-serif text-3xl font-semibold text-copper-600">{{ $money($order->total) }}</dd>
                    </div>
                </dl>

                <x-site.contact-help :message="$payment ? 'Đã chuyển khoản mà đơn chưa cập nhật? Liên hệ kèm mã đơn '.$order->code.':' : 'Cần hỗ trợ về đơn '.$order->code.'? Liên hệ:'" />

                <a href="{{ route('products.index') }}" class="text-center text-sm font-semibold text-copper-600 hover:text-copper-700">← Tiếp tục mua sắm</a>
            </aside>
        </div>
    </div>
</x-layouts.app>
