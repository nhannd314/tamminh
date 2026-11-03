@use('App\Support\Astrology\Element')

{{--
    Bát tự và bản mệnh. Số liệu lá số có sẵn nhưng vẫn hiện dần từng phần (tm-lazy) khi mở trang,
    sơ đồ ngũ hành xoay vào sau cùng. Luồng stream chỉ thay đoạn luận bản mệnh (overview-destiny)
    nên hiệu ứng không bị cắt ngang. Hiệu ứng chỉ chạy ở lần mở đầu tiên; `lazy` = false để tắt.
--}}
@php
    $destiny = $chart->destinyElement();
    $counts = $chart->elementCounts();
    $lazy ??= true;
    // Số liệu có sẵn nhưng hiện chậm, lần lượt từng phần và có nhịp dừng để khách kịp đọc (ms kể từ lúc mở trang):
    // 4 trụ bát tự cách nhau 0,9 giây, sau đó mỗi phần dừng khoảng 1,5 giây.
    $lazyDelays = [
        0 => 600, 1 => 1500, 2 => 2400, 3 => 3300, // Năm, Tháng, Ngày, Giờ
        4 => 4800,  // dòng ngũ hành
        5 => 6300,  // mệnh chủ
        6 => 7700,  // cục mệnh, thân cư
        7 => 9200,  // sơ đồ ngũ hành
    ];
    $lazyDelay = fn (int $step): string => $lazy ? "--lazy-delay: {$lazyDelays[$step]}ms" : '';
@endphp

<section id="{{ \App\AnalysisSection::Overview->anchor() }}" class="flex flex-col gap-6 rounded-xl border border-cream-300 bg-white p-5 shadow-soft-sm sm:p-6" aria-label="Bát tự và bản mệnh">
    {{-- Số liệu lá số bên trái, sơ đồ ngũ hành bên phải cùng chiều cao; đoạn luận dài nằm riêng hàng dưới. --}}
    <div class="grid items-center gap-6 xl:grid-cols-[minmax(0,1fr)_320px] xl:gap-8">
        <div class="flex flex-col gap-5">
            <div>
                <h3 class="tm-subhead">Bát tự</h3>
                <div class="grid grid-cols-2 gap-y-4 text-center sm:grid-cols-4">
                    @foreach ($chart->pillars() as $label => $pillar)
                        <div @class(['border-l border-cream-300' => ! $loop->first, 'max-sm:border-l-0' => $loop->index === 2])>
                            <div @class(['tm-lazy' => $lazy]) style="{{ $lazyDelay($loop->index) }}">
                                <div class="text-xs font-semibold tracking-eyebrow text-ink-300 uppercase">{{ $label }}</div>
                                <div class="mt-1.5 px-1 font-serif text-lg font-semibold whitespace-nowrap text-ink-600">
                                    {{ $pillar?->name() ?? '—' }}
                                </div>
                                @unless ($pillar)
                                    <div class="text-xs text-ink-300">Chưa rõ giờ sinh</div>
                                @endunless
                            </div>
                        </div>
                    @endforeach
                </div>

                <div @class(['mt-5 flex flex-wrap items-center gap-x-3 gap-y-1 border-t border-cream-300 pt-4 text-sm', 'tm-lazy' => $lazy]) style="{{ $lazyDelay(4) }}">
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

            <div class="flex flex-wrap items-center gap-x-6 gap-y-4 rounded-lg bg-cream-50 px-5 py-4">
                <div @class(['flex items-center gap-4', 'tm-lazy' => $lazy]) style="{{ $lazyDelay(5) }}">
                    <span class="flex size-14 shrink-0 items-center justify-center rounded-full font-serif text-2xl text-white" style="background: {{ $destiny->color() }}">{{ $destiny->glyph() }}</span>
                    <div>
                        <div class="text-xs font-semibold tracking-eyebrow text-copper-700 uppercase">Mệnh chủ</div>
                        <div class="font-serif text-[22px] font-semibold text-copper-600">{{ $chart->destiny() }}</div>
                    </div>
                </div>
                <dl @class(['grid grid-cols-[auto_minmax(0,1fr)] gap-x-4 gap-y-1.5 text-sm sm:border-l sm:border-cream-300 sm:pl-6', 'tm-lazy' => $lazy]) style="{{ $lazyDelay(6) }}">
                    <dt class="text-xs leading-6 text-ink-300">Cục mệnh</dt>
                    <dd class="font-semibold text-ink-600">{{ $chart->cuc() ?? 'Cần giờ sinh' }}</dd>
                    <dt class="text-xs leading-6 text-ink-300">Thân cư</dt>
                    <dd class="font-semibold text-ink-600">{{ $chart->bodyPalace() ?? 'Cần giờ sinh' }}</dd>
                </dl>
            </div>
        </div>

        <div @class(['flex justify-center', 'tm-lazy' => $lazy]) style="{{ $lazyDelay(7) }}">
            <x-analysis.element-wheel :counts="$counts" :destiny="$destiny" :animated="$lazy" class="xl:max-w-[320px]" />
        </div>
    </div>

    <div class="border-t border-cream-300 pt-6">
        @include('pages.analysis.overview-destiny')
    </div>
</section>
