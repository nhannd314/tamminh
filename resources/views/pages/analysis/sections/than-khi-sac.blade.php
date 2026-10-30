@php
    $content = $item['content'];
    $locked = ! $item['unlocked'];
@endphp

<x-analysis.section :analysis="$analysis" :item="$item">
    <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:gap-10">
        <x-analysis.face-crop :analysis="$analysis" :box="$sections['overview']['content']['face_box'] ?? null" tight
            class="size-28 rounded-full border-4 border-copper-100 shadow-soft-sm sm:size-32" />

        <div class="min-w-0 flex-1">
            <x-analysis.summary :text="$content['summary']" :locked="$locked" />
            <div class="mt-4 flex items-center gap-3 text-sm font-semibold text-ink-600">
                Đánh giá <x-ui.stars :rating="$content['rating']" :size="18" :class="$locked ? 'tm-blurred' : ''" />
            </div>
        </div>
    </div>

    <x-slot:detail>
        <x-analysis.blocks :blocks="$content['blocks']" :locked="$locked" />
    </x-slot:detail>
</x-analysis.section>
