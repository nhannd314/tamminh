{{--
    Biểu đồ radar ngũ hành (tỷ lệ %), đỉnh trên cùng là Hỏa rồi theo chiều kim đồng hồ.
    Usage: <x-analysis.radar :values="['hoa' => 5, 'tho' => 15, 'kim' => 50, 'thuy' => 20, 'moc' => 10]" />
--}}
@props([
    'values' => [],
])

@use('App\Support\Astrology\Element')

@php
    $order = [Element::Hoa, Element::Tho, Element::Kim, Element::Thuy, Element::Moc];
    $center = 110;
    $radius = 78;
    $max = max(1, ...array_values($values));

    $point = function (int $index, float $distance) use ($center): array {
        $angle = deg2rad(-90 + 72 * $index);

        return [round($center + $distance * cos($angle), 1), round($center + $distance * sin($angle), 1)];
    };

    $polygon = fn (float $distance): string => collect($order)->keys()
        ->map(fn (int $index): string => implode(',', $point($index, $distance)))
        ->implode(' ');

    $star = collect([0, 2, 4, 1, 3])->map(fn (int $index): string => implode(',', $point($index, $radius)))->implode(' ');

    $dataPoints = collect($order)->map(fn (Element $element, int $index): array => [
        'element' => $element,
        'value' => $values[$element->value] ?? 0,
        'xy' => $point($index, $radius * ($values[$element->value] ?? 0) / $max),
        'label' => $point($index, $radius + 20),
    ]);

    $summary = $dataPoints->map(fn (array $data): string => $data['element']->label().' '.$data['value'].'%')->implode(', ');
@endphp

<svg viewBox="-40 -22 300 252" {{ $attributes->class('h-auto w-full max-w-[360px] shrink-0 sm:w-[340px]') }} role="img" aria-label="Biểu đồ ngũ hành: {{ $summary }}">
    <polygon points="{{ $polygon($radius) }}" fill="#FDFBF6" stroke="#E8DCC0" stroke-width="1" />
    @foreach ([0.25, 0.5, 0.75] as $level)
        <polygon points="{{ $polygon($radius * $level) }}" fill="none" stroke="#E8DCC0" stroke-width="1" />
    @endforeach

    {{-- Ngôi sao tương khắc: nối mỗi đỉnh với đỉnh cách nó một đỉnh --}}
    <polygon points="{{ $star }}" fill="none" stroke="#DAA85A" stroke-width="1" stroke-opacity=".7" />

    <polygon points="{{ $dataPoints->map(fn (array $data): string => implode(',', $data['xy']))->implode(' ') }}" fill="#C89544" fill-opacity=".28" stroke="#B6822F" stroke-width="2" stroke-linejoin="round" />

    @foreach ($dataPoints as $data)
        <circle cx="{{ $data['xy'][0] }}" cy="{{ $data['xy'][1] }}" r="3.5" fill="{{ $data['element']->color() }}" stroke="#fff" stroke-width="1.5" />

        @php
            [$labelX, $labelY] = $data['label'];
            $anchor = abs($labelX - $center) < 5 ? 'middle' : ($labelX > $center ? 'start' : 'end');
        @endphp
        <text x="{{ $labelX }}" y="{{ $labelY }}" text-anchor="{{ $anchor }}" font-family="Manrope,sans-serif" font-size="13" font-weight="700" fill="{{ $data['element']->color() }}">
            {{ $data['element']->label() }}<tspan x="{{ $labelX }}" dy="14" font-size="10.5" font-weight="500" fill="#847C70">{{ $data['value'] }}%</tspan>
        </text>
    @endforeach
</svg>
