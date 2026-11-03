{{-- Thẻ điểm "Tổng quan vận mệnh" ở thanh bên; luồng stream gửi lại thẻ này khi luận giải xong. --}}
@php
    $overview = $sections['overview']['content'];
@endphp

<div id="tong-quan-van-menh" class="rounded-xl border border-cream-300 bg-white p-5 text-center shadow-soft-sm">
    <div class="text-sm font-semibold text-ink-600">Tổng quan vận mệnh</div>

    @if ($sections['overview']['loading'])
        <x-analysis.loading label="Đang chấm điểm vận khí…" class="min-h-32 justify-center" />
    @else
        <div class="relative mx-auto mt-3 w-[180px]">
            <svg viewBox="0 0 180 100" class="w-full" aria-hidden="true">
                <path d="M15 90 A75 75 0 0 1 165 90" fill="none" stroke="#F2EBD8" stroke-width="12" stroke-linecap="round" />
                <path d="M15 90 A75 75 0 0 1 165 90" fill="none" stroke="#C89544" stroke-width="12" stroke-linecap="round" pathLength="100" stroke-dasharray="{{ $overview['score'] }} 100" />
            </svg>
            <div class="absolute inset-x-0 bottom-0 font-serif text-4xl font-semibold text-copper-600">
                {{ $overview['score'] }}<span class="text-base font-normal text-ink-300">/100</span>
            </div>
        </div>
        <div class="mt-1 text-sm text-ink-400">{{ $overview['verdict'] }}</div>
        <div class="mt-4 grid grid-cols-3 border-t border-cream-300 pt-3 text-sm">
            <div><div class="text-ink-300">Cát</div><div class="font-semibold text-success">{{ $overview['ratios']['good'] }}%</div></div>
            <div class="border-x border-cream-300"><div class="text-ink-300">Trung bình</div><div class="font-semibold text-copper-600">{{ $overview['ratios']['neutral'] }}%</div></div>
            <div><div class="text-ink-300">Cần lưu ý</div><div class="font-semibold text-danger">{{ $overview['ratios']['caution'] }}%</div></div>
        </div>
    @endif
</div>
