@php
    $content = $item['content'];
    $locked = ! $item['unlocked'];
    // Đang chờ Gemini: mục nào chưa có chữ thì hiện spinner.
    $loading = $item['loading'];
@endphp

<x-analysis.section :analysis="$analysis" :item="$item">
    @if ($loading && $content['summary'] === [])
        <x-analysis.loading label="Đang xem ngũ quan…" />
    @else
        <x-analysis.summary :text="$content['summary']" :locked="$locked" />
    @endif

    <x-slot:detail>
        @if ($loading && collect($content['tabs'])->every(fn (array $tab): bool => $tab['blocks'] === []))
            <x-analysis.loading label="Đang viết luận giải chi tiết…" />
        @else
            <x-analysis.tabs :tabs="$content['tabs']" :locked="$locked" id="ngu-quan" />
        @endif
    </x-slot:detail>
</x-analysis.section>
