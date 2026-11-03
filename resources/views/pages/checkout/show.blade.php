@php
    $money = fn (int $amount): string => number_format($amount, 0, ',', '.').'đ';
    $selectedMethod = old('payment_method', \App\OrderPaymentMethod::Online->value);
    $selectedShipping = $shippingFees[$selectedMethod] ?? 0;
    $codFee = $shippingFees[\App\OrderPaymentMethod::Cod->value];
    $customer = auth('customer')->user();
@endphp

<x-layouts.app title="Thanh toán" description="Thanh toán đơn vật phẩm phong thủy Tâm Minh.">
    @push('head')
        <meta name="robots" content="noindex, nofollow">
    @endpush

    <div class="tm-container pt-6 pb-16">
        <x-ui.breadcrumb class="mb-6" :items="['Trang chủ' => route('home'), 'Giỏ hàng' => route('cart.show'), 'Thanh toán' => null]" />

        <h1 class="font-serif text-3xl leading-tight font-semibold text-ink-600 sm:text-4xl">Thanh toán</h1>

        <form method="POST" action="{{ route('checkout.store') }}" class="mt-6 grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_400px] lg:gap-8"
            data-submit-loading data-checkout data-checkout-subtotal="{{ $subtotal }}" data-checkout-fees='@json($shippingFees)'>
            @csrf

            <div class="flex flex-col gap-6">
                <section class="rounded-2xl border border-cream-300 bg-white p-5 shadow-soft-sm sm:p-7" aria-labelledby="checkout-contact">
                    <h2 id="checkout-contact" class="mb-5 flex items-center gap-3 font-serif text-xl font-semibold text-ink-600">
                        <span class="flex size-8 items-center justify-center rounded-full bg-copper-50 font-sans text-sm font-semibold text-copper-700">1</span>
                        Thông tin nhận hàng
                    </h2>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.field label="Họ và tên người nhận" for="checkout-name" name="customer_name">
                            <x-ui.input id="checkout-name" name="customer_name" :value="$customer?->name" placeholder="Nguyễn Văn A" autocomplete="name" required maxlength="100" />
                        </x-ui.field>

                        <x-ui.field label="Số điện thoại" for="checkout-phone" name="phone">
                            <x-ui.input id="checkout-phone" type="tel" name="phone" :value="$customer?->phone" inputmode="tel" placeholder="09xx xxx xxx" autocomplete="tel" required maxlength="20" />
                        </x-ui.field>

                        <x-ui.field label="Địa chỉ nhận hàng" for="checkout-address" name="address" class="sm:col-span-2">
                            <x-ui.input id="checkout-address" name="address" :value="$customer?->address" placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành phố" autocomplete="street-address" required maxlength="500" />
                        </x-ui.field>

                        <x-ui.field label="Ghi chú (không bắt buộc)" for="checkout-note" name="note" class="sm:col-span-2">
                            <textarea id="checkout-note" name="note" rows="3" maxlength="1000" placeholder="Thời gian nhận hàng, yêu cầu khai quang…" @class(['tm-input resize-y']) @if ($errors->has('note')) aria-invalid="true" @endif>{{ old('note') }}</textarea>
                        </x-ui.field>
                    </div>
                </section>

                <section class="rounded-2xl border border-cream-300 bg-white p-5 shadow-soft-sm sm:p-7" aria-labelledby="checkout-payment">
                    <h2 id="checkout-payment" class="mb-5 flex items-center gap-3 font-serif text-xl font-semibold text-ink-600">
                        <span class="flex size-8 items-center justify-center rounded-full bg-copper-50 font-sans text-sm font-semibold text-copper-700">2</span>
                        Hình thức thanh toán
                    </h2>

                    <x-ui.field name="payment_method">
                        <div class="grid gap-3">
                            <label class="tm-radio-card !items-center !gap-4 !p-4 sm:!p-5">
                                <input type="radio" name="payment_method" value="online" class="sr-only" @checked($selectedMethod === 'online') />
                                <span class="tm-radio-dot"></span>
                                <x-ui.icon-ring icon="lock" size="sm" class="hidden sm:flex" />
                                <span class="min-w-0 flex-1">
                                    <span class="flex flex-wrap items-center gap-2">
                                        <span class="text-base font-semibold text-ink-600">Chuyển khoản online</span>
                                        <span class="rounded-full bg-copper-500 px-2.5 py-0.5 text-xs font-semibold text-white">Miễn phí giao hàng</span>
                                    </span>
                                    <span class="mt-1 block text-sm leading-relaxed text-ink-400">Quét mã QR bằng app ngân hàng sau khi đặt - hệ thống tự xác nhận trong 1-2 phút.</span>
                                </span>
                            </label>

                            <label class="tm-radio-card !items-center !gap-4 !p-4 sm:!p-5">
                                <input type="radio" name="payment_method" value="cod" class="sr-only" @checked($selectedMethod === 'cod') />
                                <span class="tm-radio-dot"></span>
                                <x-ui.icon-ring icon="send" size="sm" class="hidden sm:flex" />
                                <span class="min-w-0 flex-1">
                                    <span class="block text-base font-semibold text-ink-600">Thanh toán khi nhận hàng (COD)</span>
                                    <span class="mt-1 block text-sm leading-relaxed text-ink-400">Trả tiền mặt cho nhân viên giao hàng. Phí giao hàng {{ $money($codFee) }}.</span>
                                </span>
                            </label>
                        </div>
                    </x-ui.field>
                </section>
            </div>

            <aside class="flex flex-col gap-4 rounded-2xl border border-cream-300 bg-white p-6 shadow-soft-sm lg:sticky lg:top-24">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="font-sans text-sm font-semibold tracking-[0.14em] text-ink-600 uppercase">Đơn hàng</h2>
                    <a href="{{ route('cart.show') }}" class="text-sm font-semibold text-copper-600 hover:text-copper-700">Sửa giỏ</a>
                </div>

                <ul class="flex flex-col gap-3.5 border-b border-cream-300 pb-4">
                    @foreach ($lines as $line)
                        <li class="flex gap-3">
                            <div class="relative size-16 shrink-0 rounded-lg border border-cream-300">
                                <div class="size-full overflow-hidden rounded-lg">
                                    <x-products.cover :product="$line->product" :cover="$line->product->coverUrl()" icon-class="text-3xl" />
                                </div>
                                <span class="absolute -top-2 -right-2 flex size-5 items-center justify-center rounded-full bg-navy-500 text-xs font-semibold text-white">{{ $line->quantity }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-sm leading-snug font-semibold text-ink-600">{{ $line->product->name }}</div>
                                @if ($line->optionsLabel())
                                    <div class="mt-0.5 text-xs text-ink-400">{{ $line->optionsLabel() }}</div>
                                @endif
                            </div>
                            <div class="text-sm font-semibold whitespace-nowrap text-ink-600">{{ $money($line->subtotal()) }}</div>
                        </li>
                    @endforeach
                </ul>

                <dl class="flex flex-col gap-3 text-base">
                    <div class="flex justify-between gap-4">
                        <dt class="text-ink-400">Tạm tính</dt>
                        <dd class="font-medium text-ink-600">{{ $money($subtotal) }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-ink-400">Phí giao hàng</dt>
                        <dd class="font-medium text-ink-600" data-checkout-shipping>{{ $selectedShipping > 0 ? $money($selectedShipping) : 'Miễn phí' }}</dd>
                    </div>
                    <div class="flex items-baseline justify-between gap-4 border-t border-cream-300 pt-4">
                        <dt class="font-semibold text-ink-600">Tổng cộng</dt>
                        <dd class="font-serif text-3xl font-semibold text-copper-600" data-checkout-total>{{ $money($subtotal + $selectedShipping) }}</dd>
                    </div>
                </dl>

                @if ($codFee > 0)
                    <p class="rounded-lg bg-copper-50 px-4 py-3 text-sm leading-relaxed text-copper-700">
                        Chuyển khoản online để được <b class="font-semibold">miễn {{ $money($codFee) }} phí giao hàng</b>.
                    </p>
                @endif

                <x-ui.button type="submit" size="lg" block trailing-icon="arrow" class="uppercase tracking-wide" data-loading-text="Đang đặt hàng…">
                    Đặt hàng
                </x-ui.button>

                <ul class="grid gap-2.5 border-t border-cream-300 pt-4 text-sm text-ink-500">
                    @foreach ([['badge', 'Khai quang trước khi giao'], ['send', 'Giao toàn quốc 2–4 ngày'], ['check', 'Đổi trả trong 7 ngày']] as [$icon, $label])
                        <li class="flex items-center gap-2.5"><x-ui.icon :name="$icon" :size="18" class="text-copper-600" />{{ $label }}</li>
                    @endforeach
                </ul>

                <x-site.contact-help message="Cần tư vấn trước khi đặt? Liên hệ Tâm Minh:" />
            </aside>
        </form>
    </div>
</x-layouts.app>
