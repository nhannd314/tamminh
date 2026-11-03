@php
    $content = $item['content'];
    $locked = ! $item['unlocked'];
    // Đang chờ Gemini: mục nào chưa có chữ thì hiện spinner.
    $loading = $item['loading'];
@endphp

<x-analysis.section :analysis="$analysis" :item="$item">
    <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:gap-10">
        <x-analysis.face-crop :analysis="$analysis" :box="$sections['overview']['content']['face_box'] ?? null" tight
            class="size-28 rounded-full border-4 border-copper-100 shadow-soft-sm sm:size-32" />

        <div class="min-w-0 flex-1">
            @if ($loading && blank($content['summary']))
                <x-analysis.loading label="Đang xem thần, khí, sắc…" />
            @else
                <x-analysis.summary :text="$content['summary']" :locked="$locked" />
            @endif

            @unless ($loading && $content['rating'] === 0)
                <div class="mt-4 flex items-center gap-3 text-sm font-semibold text-ink-600">
                    Đánh giá <x-ui.stars :rating="$content['rating']" :size="18" :class="$locked ? 'tm-blurred' : ''" />
                </div>
            @endunless
        </div>
    </div>

    <x-slot:detail>
        @if ($loading && $content['blocks'] === [])
            <x-analysis.loading label="Đang viết luận giải chi tiết…" />
        @else
            <x-analysis.blocks :blocks="$content['blocks']" :locked="$locked" />
        @endif
    </x-slot:detail>
</x-analysis.section>
