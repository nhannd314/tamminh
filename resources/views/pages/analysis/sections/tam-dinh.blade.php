@php
    $content = $item['content'];
    $locked = ! $item['unlocked'];

    // Số đo thật từ ảnh (FaceDetector) luôn hiển thị; chưa đo được thì dùng số mẫu.
    $measured = $analysis->face_metrics;
    $lines = $measured['lines'] ?? [24, 42, 63, 82];
    $ratios = $measured['ratios'] ?? $content['ratios'];
    $blurRatios = $locked && ! $measured;

    $zones = collect([['Thượng đình', '15–30 tuổi'], ['Trung đình', '31–50 tuổi'], ['Hạ đình', '51+ tuổi']])
        ->map(fn (array $zone, int $index): array => [...$zone, ($lines[$index] + $lines[$index + 1]) / 2, $ratios[$index]]);
    $largest = max($ratios);
@endphp

<x-analysis.section :analysis="$analysis" :item="$item">
    <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:gap-10">
        <div class="relative flex shrink-0 gap-3 self-center sm:self-start">
            <div @class(['relative w-10 text-right font-serif font-semibold', 'tm-blurred' => $blurRatios])>
                @foreach ($zones as [$name, $ages, $middle, $ratio])
                    <span @class([
                        'absolute right-0 -translate-y-1/2',
                        'text-base text-copper-600' => $ratio === $largest,
                        'text-sm text-ink-400' => $ratio !== $largest,
                    ]) style="top: {{ $middle }}%">{{ $ratio }}%</span>
                @endforeach
            </div>

            <figure class="relative aspect-[250/255] w-48 overflow-hidden rounded-xl border border-cream-300 sm:w-56">
                <x-analysis.face-crop :analysis="$analysis" :box="$sections['overview']['content']['face_box'] ?? null" class="size-full" />
            </figure>

            <div class="relative w-24">
                @foreach ($zones as [$name, $ages, $middle])
                    <div class="absolute left-0 -translate-y-1/2 whitespace-nowrap" style="top: {{ $middle }}%">
                        <div class="text-sm font-semibold text-ink-600">{{ $name }}</div>
                        <div class="text-xs text-ink-400">{{ $ages }}</div>
                    </div>
                @endforeach
            </div>

            @foreach ($lines as $line)
                <div class="pointer-events-none absolute inset-x-0 border-t-2 border-dashed border-copper-500" style="top: {{ $line }}%"></div>
            @endforeach
        </div>

        <div class="min-w-0 flex-1">
            <x-analysis.summary :text="$content['summary']" :locked="$locked" />
            <div class="mt-4 flex items-center gap-3 text-sm font-semibold text-ink-600">
                Đánh giá <x-ui.stars :rating="$content['rating']" :size="18" :class="$locked ? 'tm-blurred' : ''" />
            </div>
        </div>
    </div>

    <x-slot:detail>
        <x-analysis.tabs :tabs="$content['tabs']" :locked="$locked" id="tam-dinh" />
    </x-slot:detail>
</x-analysis.section>
