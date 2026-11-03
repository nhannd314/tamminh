{{--
    Vòng tương sinh ngũ hành: kích thước mỗi hành theo số lần xuất hiện trong bát tự,
    hành bản mệnh được khoanh viền đứt.
    `animated`: vòng xoay nửa vòng khi hiện ra (theo --lazy-delay của khung chứa), sau đó hành bản mệnh nhấp nháy 3 nhịp rồi dừng.
    Usage: <x-analysis.element-wheel :counts="$chart->elementCounts()" :destiny="$chart->destinyElement()" animated />
--}}
@props([
    'counts',
    'destiny',
    'animated' => false,
])

@use('App\Support\Astrology\Element')

@php
    $order = [Element::Hoa, Element::Tho, Element::Kim, Element::Thuy, Element::Moc];
    $center = [150, 150];
    $radius = 80;

    $nodes = collect($order)->map(function (Element $element, int $index) use ($center, $radius, $counts): array {
        $angle = deg2rad(-90 + 72 * $index);

        return [
            'element' => $element,
            'x' => round($center[0] + $radius * cos($angle), 1),
            'y' => round($center[1] + $radius * sin($angle), 1),
            'count' => $counts[$element->value] ?? 0,
        ];
    });

    $star = collect([0, 2, 4, 1, 3, 0])->map(fn (int $index): string => $nodes[$index]['x'].','.$nodes[$index]['y'])->implode(' ');
@endphp

<svg viewBox="0 0 320 290" {{ $attributes->class('h-auto w-full max-w-[380px]') }} role="img" aria-label="Sơ đồ ngũ hành, bản mệnh {{ $destiny->label() }}">
    <g transform="translate(10,0)">
        <g @class(['tm-wheel-spin' => $animated])>
            {{-- Vòng tròn vô hình bao trọn sơ đồ để tâm xoay trùng tâm vòng ngũ hành --}}
            <circle cx="{{ $center[0] }}" cy="{{ $center[1] }}" r="140" fill="none" />
            <circle cx="{{ $center[0] }}" cy="{{ $center[1] }}" r="{{ $radius }}" fill="none" stroke="#DAA85A" stroke-width="1.5" stroke-dasharray="4 5" />
            <polyline points="{{ $star }}" fill="none" stroke="#C9B68C" stroke-width="1" />

            @foreach ($nodes as $node)
                @php
                    $isDestiny = $node['element'] === $destiny;
                    $size = $isDestiny ? 22 : 13 + 2 * min($node['count'], 4);
                @endphp

                <g transform="translate({{ $node['x'] }} {{ $node['y'] }})">
                    <g @class(['tm-wheel-blink' => $isDestiny && $animated])>
                        @if ($isDestiny)
                            <circle r="29" fill="none" stroke="#946821" stroke-width="1.5" stroke-dasharray="4 3" />
                        @endif

                        <circle r="{{ $size }}" fill="{{ $node['element']->color() }}" />
                        <text y="6" text-anchor="middle" font-family="Lora,serif" font-size="{{ $isDestiny ? 20 : 15 }}" fill="#fff">{{ $node['element']->glyph() }}</text>
                    </g>
                </g>

                @php
                    // Nhãn nằm ngoài viền đứt của hành bản mệnh.
                    $outer = $isDestiny ? 30 : $size;
                    $below = $node['y'] > $center[1];
                    $labelY = $below ? $node['y'] + $outer + 16 : $node['y'] - $outer - 8;
                    // Hai hành hai bên phía trên (Mộc, Thổ): đẩy nhãn ra ngoài để không đè lên vòng tròn đứt.
                    $dx = $node['x'] - $center[0];
                    $labelX = ! $below && abs($dx) > 5 ? $node['x'] + ($dx > 0 ? 18 : -18) : $node['x'];
                @endphp
                <text x="{{ $labelX }}" y="{{ $labelY }}" text-anchor="middle" font-family="Manrope,sans-serif" font-size="13" font-weight="800" fill="{{ $node['element']->color() }}">
                    {{ $node['element']->label() }} · {{ $node['count'] }}
                </text>
                @if ($isDestiny)
                    <text x="{{ $labelX }}" y="{{ $below ? $labelY + 15 : $labelY - 15 }}" text-anchor="middle" font-family="Manrope,sans-serif" font-size="12" font-weight="700" fill="#946821">Bản mệnh</text>
                @endif
            @endforeach
        </g>
    </g>
</svg>
