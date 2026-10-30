{{--
    Vòng tương sinh ngũ hành: kích thước mỗi hành theo số lần xuất hiện trong bát tự,
    hành bản mệnh được làm nổi bật.
    Usage: <x-analysis.element-wheel :counts="$chart->elementCounts()" :destiny="$chart->destinyElement()" />
--}}
@props([
    'counts',
    'destiny',
])

@use('App\Support\Astrology\Element')

@php
    $order = [Element::Hoa, Element::Tho, Element::Kim, Element::Thuy, Element::Moc];
    $center = [150, 118];
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

<svg viewBox="0 0 320 270" {{ $attributes->class('h-auto w-full max-w-[320px]') }} role="img" aria-label="Sơ đồ ngũ hành, bản mệnh {{ $destiny->label() }}">
    <defs>
        <radialGradient id="tm-wheel-glow">
            <stop offset="0" stop-color="#E9C886" stop-opacity=".55" />
            <stop offset="1" stop-color="#E9C886" stop-opacity="0" />
        </radialGradient>
    </defs>

    <g transform="translate(10,0)">
        <circle cx="{{ $center[0] }}" cy="{{ $center[1] }}" r="{{ $radius }}" fill="none" stroke="#DAA85A" stroke-width="1.5" stroke-dasharray="4 5" />
        <polyline points="{{ $star }}" fill="none" stroke="#C9B68C" stroke-width="1" />

        @foreach ($nodes as $node)
            @php
                $isDestiny = $node['element'] === $destiny;
                $size = $isDestiny ? 22 : 13 + 2 * min($node['count'], 4);
            @endphp

            @if ($isDestiny)
                <circle cx="{{ $node['x'] }}" cy="{{ $node['y'] }}" r="40" fill="url(#tm-wheel-glow)" />
                <circle cx="{{ $node['x'] }}" cy="{{ $node['y'] }}" r="28" fill="#F4E2BC" />
            @endif

            <circle cx="{{ $node['x'] }}" cy="{{ $node['y'] }}" r="{{ $size }}" fill="{{ $node['element']->color() }}" />
            <text x="{{ $node['x'] }}" y="{{ $node['y'] + 6 }}" text-anchor="middle" font-family="Lora,serif" font-size="{{ $isDestiny ? 20 : 15 }}" fill="#fff">{{ $node['element']->glyph() }}</text>

            @php
                $below = $node['y'] > $center[1];
                $labelY = $below ? $node['y'] + $size + 16 : $node['y'] - $size - 8;
            @endphp
            <text x="{{ $node['x'] }}" y="{{ $labelY }}" text-anchor="middle" font-family="Manrope,sans-serif" font-size="12" font-weight="800" fill="{{ $node['element']->color() }}">
                {{ $node['element']->label() }} · {{ $node['count'] }}
            </text>
            @if ($isDestiny)
                <text x="{{ $node['x'] }}" y="{{ $below ? $labelY + 15 : $labelY - 15 }}" text-anchor="middle" font-family="Manrope,sans-serif" font-size="11" font-weight="700" fill="#946821">Bản mệnh</text>
            @endif
        @endforeach
    </g>
</svg>
