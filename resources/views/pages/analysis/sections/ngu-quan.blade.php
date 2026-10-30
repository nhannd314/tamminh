@php
    $content = $item['content'];
    $locked = ! $item['unlocked'];
@endphp

<x-analysis.section :analysis="$analysis" :item="$item">
    <x-analysis.summary :text="$content['summary']" :locked="$locked" />

    <x-slot:detail>
        <x-analysis.tabs :tabs="$content['tabs']" :locked="$locked" id="ngu-quan" />
    </x-slot:detail>
</x-analysis.section>
