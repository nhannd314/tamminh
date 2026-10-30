{{--
    Cửa sổ chuyển khoản cho đơn vừa tạo: QR SePay + thông tin tài khoản (bảng settings).
    Tự mở khi tải trang và tự kiểm tra trạng thái đơn; SePay báo đã nhận tiền thì tải lại trang.
--}}
@php
    $order = $payment->order;
    $rows = [
        ['Ngân hàng', $payment->bank, false],
        ['Số tài khoản', $payment->accountNumber, true],
        ['Chủ tài khoản', $payment->accountName, false],
    ];
@endphp

<x-ui.modal id="payment" class="max-w-[720px]" data-modal-autoopen data-payment-status="{{ route('analyses.order-status', [$analysis, $order->code]) }}">
    <x-slot:header>
        <x-ui.icon-ring icon="lock" size="sm" />
        <div>
            <div class="font-serif text-lg font-semibold text-ink-600">Thanh toán mở khóa</div>
            <div class="text-sm text-ink-400">
                {{ $order->isFullPackage() ? 'Trọn gói '.count($order->sectionsToUnlock()).' phần luận giải' : 'Phần '.$order->section->number().' · '.$order->section->label() }}
            </div>
        </div>
    </x-slot:header>

    @if ($payment->isConfigured())
        <div class="grid gap-6 sm:grid-cols-[240px_minmax(0,1fr)]">
            <div class="flex flex-col items-center gap-2">
                <img src="{{ $payment->qrUrl() }}" alt="Mã QR chuyển khoản {{ number_format($order->amount, 0, ',', '.') }}đ" width="240" height="240"
                    class="size-60 rounded-xl border border-cream-300 bg-white object-contain p-2" />
                <p class="text-center text-xs text-ink-400">Mở app ngân hàng và quét mã QR - số tiền và nội dung được điền sẵn.</p>
            </div>

            <dl class="flex flex-col gap-3 text-sm">
                @foreach ($rows as [$label, $value, $copyable])
                    <div class="flex items-center justify-between gap-3 border-b border-cream-200 pb-3">
                        <dt class="text-ink-400">{{ $label }}</dt>
                        <dd class="flex items-center gap-2 text-right font-semibold text-ink-600">
                            {{ $value ?: '—' }}
                            @if ($copyable && $value)
                                <button type="button" class="cursor-pointer text-copper-500 hover:text-copper-700" data-copy="{{ $value }}" data-copy-message="Đã sao chép {{ mb_strtolower($label) }}." aria-label="Sao chép {{ mb_strtolower($label) }}">
                                    <x-ui.icon name="link" :size="14" />
                                </button>
                            @endif
                        </dd>
                    </div>
                @endforeach

                <div class="flex items-center justify-between gap-3 border-b border-cream-200 pb-3">
                    <dt class="text-ink-400">Số tiền</dt>
                    <dd class="text-right">
                        @if ($order->discount > 0)
                            <span class="mr-1.5 text-xs text-ink-300 line-through"><x-analysis.price :amount="$order->subtotal" /></span>
                        @endif
                        <x-analysis.price :amount="$order->amount" class="font-serif text-xl font-semibold text-copper-600" />
                    </dd>
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
            Đang chờ thanh toán… Trang sẽ tự mở khóa khi nhận được tiền.
        </div>
    @else
        <p class="text-base text-ink-500">
            Tài khoản nhận tiền chưa được cấu hình. Vui lòng liên hệ Tâm Minh để thanh toán đơn
            <b class="font-semibold text-ink-600">{{ $order->code }}</b> (<x-analysis.price :amount="$order->amount" />).
        </p>
    @endif
</x-ui.modal>
