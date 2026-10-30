{{--
    Nút mở khoá một phần luận giải: gửi form tạo đơn chờ thanh toán.
    Usage: <x-analysis.unlock-cta :analysis="$analysis" :section="$type" :price="49000" />
--}}
@props([
    'analysis',
    'section',
    'price',
])

<form method="POST" action="{{ route('analyses.unlock', $analysis) }}" data-print-hidden
    {{ $attributes->class('flex flex-col items-center gap-4 rounded-lg border border-copper-200 bg-copper-50 p-4 text-center sm:flex-row sm:justify-between sm:p-5 sm:text-left') }}>
    @csrf
    <input type="hidden" name="section" value="{{ $section->value }}" />

    <div class="min-w-0">
        <div class="font-serif text-lg font-semibold text-ink-600">
            Mở khóa luận giải này chỉ <span class="text-copper-600">{{ intdiv($price, 1000) }}K</span>
        </div>
        <p class="mt-1 text-sm text-ink-500">{{ $section->teaser() }}</p>
    </div>

    <x-ui.button type="submit" icon="lock" class="shrink-0">
        Mở khóa · <x-analysis.price :amount="$price" />
    </x-ui.button>
</form>
