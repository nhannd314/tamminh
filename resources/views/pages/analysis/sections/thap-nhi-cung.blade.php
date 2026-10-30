@php
    $content = $item['content'];
    $locked = ! $item['unlocked'];
    $verdictClasses = [
        'Cát' => 'bg-success/10 text-success',
        'Trung bình' => 'bg-copper-50 text-copper-700',
        'Cần lưu ý' => 'bg-danger/10 text-danger',
    ];
@endphp

<x-analysis.section :analysis="$analysis" :item="$item">
    <x-analysis.summary :text="$content['summary']" :locked="$locked" />

    <x-slot:detail>
        <div class="grid grid-cols-1 items-start gap-3 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($content['palaces'] as $palace)
                <details class="group flex min-w-0 flex-col rounded-lg border border-cream-300 bg-cream-50 p-4 open:col-span-full open:bg-white open:shadow-soft-sm">
                    <summary class="flex cursor-pointer list-none flex-col gap-1.5 [&::-webkit-details-marker]:hidden">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-baseline gap-2">
                                <span class="font-serif text-sm font-semibold text-copper-500">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <h4 class="font-serif text-lg font-semibold text-ink-600">{{ $palace['name'] }}</h4>
                            </div>
                            <span @class(['shrink-0 rounded-full px-2.5 py-0.5 text-xs font-semibold', $verdictClasses[$palace['verdict']] ?? $verdictClasses['Trung bình'], 'tm-blurred' => $locked])>{{ $palace['verdict'] }}</span>
                        </div>
                        <div class="text-xs text-ink-300">Vị trí: {{ $palace['position'] }}</div>
                        <p @class(['text-sm leading-relaxed text-ink-500', 'tm-blurred' => $locked])>{{ $palace['text'] }}</p>
                        <span class="mt-1 inline-flex items-center gap-1 text-sm font-semibold text-copper-600">
                            <span class="group-open:hidden">Đọc chi tiết</span>
                            <span class="hidden group-open:inline">Thu gọn</span>
                            <x-ui.icon name="chevron-down" :size="14" class="transition-transform group-open:rotate-180" />
                        </span>
                    </summary>

                    <div class="mt-3 flex flex-col gap-2 border-t border-cream-300 pt-3 text-sm leading-relaxed text-ink-500">
                        <p><b class="font-semibold text-copper-700">Luận giải:</b> <span @class(['tm-blurred' => $locked])>{{ $palace['analysis'] }}</span></p>
                        <p><b class="font-semibold text-copper-700">Lời khuyên:</b> <span @class(['tm-blurred' => $locked])>{{ $palace['advice'] }}</span></p>
                    </div>
                </details>
            @endforeach
        </div>
    </x-slot:detail>
</x-analysis.section>
