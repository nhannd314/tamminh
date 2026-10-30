@php
    $content = $item['content'];
    $locked = ! $item['unlocked'];
@endphp

<x-analysis.section :analysis="$analysis" :item="$item">
    <x-analysis.summary :text="$content['summary']" :locked="$locked" />

    <x-slot:detail>
        <x-analysis.block-cards :blocks="$content['blocks']" :locked="$locked" />
    </x-slot:detail>
</x-analysis.section>
