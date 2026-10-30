{{--
    Các khối luận giải dạng thẻ 2 cột (phong thủy, lời khuyên).
    `locked`: tiêu đề và nhãn vẫn hiện, phần chữ bị che mờ.
    Usage: <x-analysis.block-cards :blocks="$content['blocks']" :locked="$locked" />
--}}
@props([
    'blocks' => [],
    'locked' => false,
])

<div {{ $attributes->class('grid grid-cols-1 gap-3 sm:grid-cols-2') }}>
    @foreach ($blocks as $block)
        <div class="flex min-w-0 flex-col gap-3 rounded-lg border border-cream-300 bg-cream-50 p-4 sm:p-5">
            <div class="flex items-start gap-2">
                <span class="pt-0.5 font-serif text-sm font-semibold text-copper-500">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                <div class="min-w-0">
                    <h4 class="font-serif text-lg leading-snug font-semibold text-ink-600">{{ $block['title'] }}</h4>
                    @isset($block['description'])
                        <p class="mt-1 text-xs leading-snug text-ink-400">{{ $block['description'] }}</p>
                    @endisset
                </div>
            </div>

            <ul class="flex flex-col gap-2 border-t border-cream-300 pt-3">
                @foreach ($block['items'] as $row)
                    <li class="flex gap-2">
                        <span class="w-24 shrink-0 text-sm font-semibold text-ink-600">{{ $row['label'] ?? '' }}</span>
                        <span @class(['min-w-0 flex-1 text-sm leading-relaxed text-ink-500', 'tm-blurred' => $locked])>{{ $row['text'] }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
</div>
