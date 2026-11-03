@use('App\Support\Astrology\Element')

@php
    $content = $item['content'];
    $locked = ! $item['unlocked'];
    // Đang chờ Gemini: mục nào chưa có chữ thì hiện spinner.
    $loading = $item['loading'];
@endphp

<x-analysis.section :analysis="$analysis" :item="$item">
    <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:gap-10">
        @if ($loading && array_sum($content['radar']) === 0)
            <x-analysis.loading label="Đang phân tích khuôn mặt…" class="aspect-[300/252] w-full max-w-[360px] shrink-0 justify-center rounded-xl bg-cream-50 sm:w-[340px]" />
        @else
            <x-analysis.radar :values="$content['radar']" :class="$locked ? 'tm-blurred' : ''" />
        @endif

        <div class="min-w-0 flex-1">
            @if ($loading && blank($content['summary']))
                <x-analysis.loading />
            @else
                <x-analysis.summary :text="$content['summary']" :locked="$locked" />
            @endif

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                @foreach (['Ưu điểm' => $content['strengths'], 'Lưu ý' => $content['cautions']] as $title => $points)
                    <div>
                        <div class="mb-1.5 text-sm font-semibold text-copper-600">{{ $title }}</div>
                        @if ($loading && $points === [])
                            <x-analysis.loading />
                        @else
                            <ul @class(['tm-bullets', 'tm-blurred' => $locked])>
                                @foreach ($points as $point)
                                    <li>{{ $point }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <x-slot:detail>
        @if ($loading && $content['blocks'] === [])
            <x-analysis.loading label="Đang viết luận giải chi tiết…" />
        @else
            <x-analysis.blocks :blocks="$content['blocks']" :locked="$locked" />
        @endif

        <div class="mt-6 mb-3 text-base font-semibold text-ink-600">Gợi ý cân bằng ngũ hành</div>
        @if ($loading && $content['balance'] === [])
            <x-analysis.loading />
        @else
            <div class="grid gap-3 sm:grid-cols-3">
                @foreach ($content['balance'] as $tip)
                    @php($element = Element::from($tip['element']))
                    <div class="flex flex-col items-center gap-2 rounded-lg border border-cream-300 bg-white p-4 text-center sm:p-5">
                        <span @class(['flex size-12 items-center justify-center rounded-full font-serif text-xl text-white', 'tm-blurred' => $locked]) style="background: {{ $element->color() }}">{{ $element->glyph() }}</span>
                        <div @class(['text-lg font-semibold text-ink-600', 'tm-blurred' => $locked])>{{ $tip['title'] }}</div>
                        <div @class(['text-[15px] text-ink-400', 'tm-blurred' => $locked])>{{ $tip['hint'] }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-slot:detail>
</x-analysis.section>
