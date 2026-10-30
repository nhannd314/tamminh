{{--
    Các khối luận giải dạng bài viết: tiêu đề + đoạn văn có nhãn đậm.
    Một mục có `text` (một đoạn, nhãn đứng đầu đoạn) hoặc `paragraphs` (nhãn đứng riêng, các đoạn bên dưới).
    `locked`: tiêu đề và nhãn vẫn hiện, phần chữ bị che mờ.
    Usage: <x-analysis.blocks :blocks="$content['blocks']" :locked="$locked" />
--}}
@props([
    'blocks' => [],
    'locked' => false,
])

<div {{ $attributes->class('flex max-w-[860px] flex-col gap-6 text-base leading-relaxed text-ink-500') }}>
    @foreach ($blocks as $block)
        <div class="flex flex-col gap-2">
            <h4 class="font-serif text-lg font-semibold text-ink-600">{{ $block['title'] }}</h4>

            @foreach ($block['items'] as $paragraph)
                @isset($paragraph['paragraphs'])
                    <p><b class="font-semibold text-copper-700">{{ $paragraph['label'] }}:</b></p>
                    @foreach ($paragraph['paragraphs'] as $text)
                        <p @class(['tm-blurred' => $locked])>{{ $text }}</p>
                    @endforeach
                @else
                    <p>
                        @isset($paragraph['label'])
                            <b class="font-semibold text-copper-700">{{ $paragraph['label'] }}:</b>
                        @endisset
                        <span @class(['tm-blurred' => $locked])>{{ $paragraph['text'] }}</span>
                    </p>
                @endisset
            @endforeach
        </div>
    @endforeach
</div>
