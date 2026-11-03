{{--
    Chỗ đang chờ Gemini viết nội dung. Luồng stream lỗi thì JS thay chữ bằng thông báo lỗi.
    Usage: <x-analysis.loading /> hoặc <x-analysis.loading label="Đang phân tích khuôn mặt…" class="min-h-40" />
--}}
@props([
    'label' => 'Đang luận giải…',
])

<div data-generation-loading {{ $attributes->class('flex items-center gap-2.5 py-2 text-sm text-ink-400') }}>
    <span class="size-4 shrink-0 animate-spin rounded-full border-2 border-copper-300 border-t-transparent" data-generation-spinner></span>
    <span data-generation-loading-label>{{ $label }}</span>
</div>
