@php
    $content = $item['content'];
    $locked = ! $item['unlocked'];
    // Đang chờ Gemini: mục nào chưa có chữ thì hiện spinner.
    $loading = $item['loading'];
@endphp

<x-analysis.section :analysis="$analysis" :item="$item">
    @if ($loading && blank($content['summary']))
        <x-analysis.loading label="Đang chọn chỉ dẫn phong thủy…" />
    @else
        <x-analysis.summary :text="$content['summary']" :locked="$locked" />
    @endif

    <x-slot:detail>
        @if ($loading && $content['blocks'] === [])
            <x-analysis.loading label="Đang viết chỉ dẫn chi tiết…" />
        @else
            <x-analysis.block-cards :blocks="$content['blocks']" :locked="$locked" />
        @endif
    </x-slot:detail>
</x-analysis.section>
