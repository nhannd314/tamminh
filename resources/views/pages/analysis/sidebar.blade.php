@php
    $overview = $sections['overview']['content'];
    $profile = [
        ['calendar', 'Ngày sinh', $analysis->birth_date->format('d/m/Y')],
        ['moon', 'Âm lịch', $chart->lunarDate->format()],
        ['clock', 'Giờ sinh', $analysis->birth_time ? $analysis->birth_time.' (giờ '.$chart->hourBranchName().')' : 'Chưa rõ'],
        ['user', 'Giới tính', $analysis->gender->label()],
        ['sparkle', 'Tuổi', $chart->age().' tuổi'],
    ];
@endphp

<aside class="flex flex-col gap-5 lg:sticky lg:top-24">
    <div class="overflow-hidden rounded-xl border border-cream-300 bg-white shadow-soft-sm">
        <img src="{{ route('analyses.portrait', $analysis) }}" alt="Ảnh chân dung của {{ $analysis->name }}" class="block aspect-[4/3.4] w-full bg-cream-200 object-cover" />

        <div class="flex flex-col gap-2.5 p-5">
            <div class="mb-1 flex items-center gap-2 font-serif text-xl font-semibold text-ink-600">
                {{ $analysis->name }}
                <x-ui.icon name="badge" :size="18" class="shrink-0 text-copper-500" />
            </div>

            @foreach ($profile as [$icon, $label, $value])
                <div class="grid grid-cols-[16px_80px_minmax(0,1fr)] items-start gap-x-2 text-sm">
                    <x-ui.icon :name="$icon" :size="16" class="mt-0.5 text-copper-500" />
                    <span class="text-ink-300">{{ $label }}:</span>
                    <span class="font-medium text-ink-600">{{ $value }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <nav class="hidden rounded-xl border border-cream-300 bg-white p-3 shadow-soft-sm lg:block" data-toc data-print-hidden aria-label="Nội dung luận giải">
        <div class="px-2 pt-1 pb-2 text-sm font-semibold text-ink-600">Nội dung luận giải</div>
        <div class="flex flex-col">
            @foreach ($sections->except('overview') as $item)
                <a href="#{{ $item['type']->anchor() }}" class="tm-toc-link">
                    <span class="tm-toc-num">{{ $item['type']->number() }}</span>
                    <span class="flex-1">{{ $item['type']->label() }}</span>
                    @unless ($item['unlocked'])
                        <x-ui.icon name="lock" :size="14" class="text-ink-200" />
                    @endunless
                </a>
            @endforeach
        </div>
    </nav>

    <div class="rounded-xl border border-cream-300 bg-white p-5 text-center shadow-soft-sm">
        <div class="text-sm font-semibold text-ink-600">Tổng quan vận mệnh</div>
        <div class="relative mx-auto mt-3 w-[180px]">
            <svg viewBox="0 0 180 100" class="w-full" aria-hidden="true">
                <path d="M15 90 A75 75 0 0 1 165 90" fill="none" stroke="#F2EBD8" stroke-width="12" stroke-linecap="round" />
                <path d="M15 90 A75 75 0 0 1 165 90" fill="none" stroke="#C89544" stroke-width="12" stroke-linecap="round" pathLength="100" stroke-dasharray="{{ $overview['score'] }} 100" />
            </svg>
            <div class="absolute inset-x-0 bottom-0 font-serif text-4xl font-semibold text-copper-600">
                {{ $overview['score'] }}<span class="text-base font-normal text-ink-300">/100</span>
            </div>
        </div>
        <div class="mt-1 text-sm text-ink-400">{{ $overview['verdict'] }}</div>
        <div class="mt-4 grid grid-cols-3 border-t border-cream-300 pt-3 text-sm">
            <div><div class="text-ink-300">Cát</div><div class="font-semibold text-success">{{ $overview['ratios']['good'] }}%</div></div>
            <div class="border-x border-cream-300"><div class="text-ink-300">Trung bình</div><div class="font-semibold text-copper-600">{{ $overview['ratios']['neutral'] }}%</div></div>
            <div><div class="text-ink-300">Cần lưu ý</div><div class="font-semibold text-danger">{{ $overview['ratios']['caution'] }}%</div></div>
        </div>
    </div>

    <div class="hidden rounded-xl border border-cream-300 bg-cream-50 p-6 text-center lg:block">
        <div class="mb-3 text-sm font-semibold text-ink-600">Lời cổ nhân</div>
        <p class="font-serif text-base leading-relaxed text-ink-500 italic">“Tướng tùy tâm sinh,<br />Tâm thiện tướng an.<br />Mệnh do mình tạo,<br />Phúc do đức mà thành.”</p>
    </div>
</aside>
