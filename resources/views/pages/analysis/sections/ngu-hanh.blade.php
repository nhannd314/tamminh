@use('App\Support\Astrology\Element')

@php
    $content = $item['content'];
    $locked = ! $item['unlocked'];
@endphp

<x-analysis.section :analysis="$analysis" :item="$item">
    <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:gap-10">
        <x-analysis.radar :values="$content['radar']" :class="$locked ? 'tm-blurred' : ''" />

        <div class="min-w-0 flex-1">
            <x-analysis.summary :text="$content['summary']" :locked="$locked" />

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <div class="mb-1.5 text-sm font-semibold text-copper-600">Ưu điểm</div>
                    <ul @class(['tm-bullets', 'tm-blurred' => $locked])>
                        @foreach ($content['strengths'] as $strength)
                            <li>{{ $strength }}</li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <div class="mb-1.5 text-sm font-semibold text-copper-600">Lưu ý</div>
                    <ul @class(['tm-bullets', 'tm-blurred' => $locked])>
                        @foreach ($content['cautions'] as $caution)
                            <li>{{ $caution }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <x-slot:detail>
        <x-analysis.blocks :blocks="$content['blocks']" :locked="$locked" />

        <div class="mt-5 mb-3 text-sm font-semibold text-ink-600">Gợi ý cân bằng ngũ hành</div>
        <div class="grid max-w-[560px] grid-cols-3 gap-3">
            @foreach ($content['balance'] as $tip)
                @php($element = Element::from($tip['element']))
                <div class="flex flex-col items-center gap-1.5 rounded-lg border border-cream-300 bg-white p-3 text-center">
                    <span @class(['flex size-9 items-center justify-center rounded-full font-serif text-base text-white', 'tm-blurred' => $locked]) style="background: {{ $element->color() }}">{{ $element->glyph() }}</span>
                    <div @class(['text-sm font-semibold text-ink-600', 'tm-blurred' => $locked])>{{ $tip['title'] }}</div>
                    <div @class(['text-xs text-ink-300', 'tm-blurred' => $locked])>{{ $tip['hint'] }}</div>
                </div>
            @endforeach
        </div>
    </x-slot:detail>
</x-analysis.section>
