@use('App\Support\Astrology\Element')

@php
    $destiny = $chart->destinyElement();
    $counts = $chart->elementCounts();
@endphp

<section class="flex flex-col gap-6 rounded-xl border border-cream-300 bg-white p-5 shadow-soft-sm sm:p-6" aria-label="Bát tự và bản mệnh">
    <div class="grid gap-6 lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)]">
        <div>
            <h3 class="tm-subhead">Bát tự</h3>
            <div class="grid grid-cols-2 gap-y-4 text-center sm:grid-cols-4">
                @foreach ($chart->pillars() as $label => $pillar)
                    <div @class(['border-l border-cream-300' => ! $loop->first, 'max-sm:border-l-0' => $loop->index === 2])>
                        <div class="text-xs font-semibold tracking-eyebrow text-ink-300 uppercase">{{ $label }}</div>
                        <div class="mt-1.5 px-1 font-serif text-xl font-semibold whitespace-nowrap text-ink-600">
                            {{ $pillar?->name() ?? '—' }}
                        </div>
                        @unless ($pillar)
                            <div class="text-xs text-ink-300">Chưa rõ giờ sinh</div>
                        @endunless
                    </div>
                @endforeach
            </div>

            <div class="mt-5 flex flex-wrap items-center gap-x-3 gap-y-1 border-t border-cream-300 pt-4 text-sm">
                <span class="text-ink-300">Ngũ hành:</span>
                <span class="flex flex-wrap gap-3 font-medium text-ink-600">
                    @foreach (Element::cases() as $element)
                        <span class="inline-flex items-center gap-1.5">
                            <span class="size-2.5 rounded-full" style="background: {{ $element->color() }}"></span>
                            {{ $element->label() }} {{ $counts[$element->value] }}
                        </span>
                    @endforeach
                </span>
            </div>
        </div>

        <div class="flex flex-col justify-center gap-4 border-t border-cream-300 pt-6 lg:border-t-0 lg:border-l lg:pt-0 lg:pl-8">
            <div class="flex items-center gap-4">
                <span class="flex size-14 shrink-0 items-center justify-center rounded-full font-serif text-2xl text-white" style="background: {{ $destiny->color() }}">{{ $destiny->glyph() }}</span>
                <div>
                    <div class="text-xs font-semibold tracking-eyebrow text-copper-700 uppercase">Mệnh chủ</div>
                    <div class="font-serif text-2xl font-semibold text-copper-600">{{ $chart->destiny() }}</div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <div class="text-ink-300">Cục mệnh</div>
                    <div class="font-semibold text-ink-600">{{ $chart->cuc() ?? 'Cần giờ sinh' }}</div>
                </div>
                <div>
                    <div class="text-ink-300">Thân cư</div>
                    <div class="font-semibold text-ink-600">{{ $chart->bodyPalace() ?? 'Cần giờ sinh' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid items-center gap-6 border-t border-cream-300 pt-6 sm:grid-cols-[minmax(0,1fr)_320px]">
        <div class="flex flex-col gap-3 text-base leading-relaxed text-ink-500">
            @foreach ($sections['overview']['content']['destiny'] as $paragraph)
                <p>
                    @if ($loop->first)
                        <b class="font-semibold text-copper-700">{{ $chart->destiny() }}</b> - mệnh {{ $destiny->label() }}, được {{ $destiny->generatedBy()->label() }} sinh và sinh ra {{ $destiny->generates()->label() }}.
                    @endif
                    {{ $paragraph }}
                </p>
            @endforeach
        </div>

        <div class="flex justify-center">
            <x-analysis.element-wheel :counts="$counts" :destiny="$destiny" />
        </div>
    </div>
</section>
