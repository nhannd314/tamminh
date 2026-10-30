{{--
    Numbered content card.
    Usage:
    <x-ui.card number="01" title="Ngũ hành tướng pháp" description="…">…</x-ui.card>

    Set :hover="false" for a static panel (number sits above the title).
--}}
@props([
    'number' => null,
    'title' => null,
    'description' => null,
    'hover' => true,
])

<div {{ $attributes->class([
    'group',
    'tm-card' => $hover,
    'tm-panel' => ! $hover,
]) }}>
    @if ($number && $hover)
        <div class="tm-card-num">{{ $number }}</div>
    @elseif ($number)
        <div class="font-serif text-sm tracking-[0.2em] text-copper-400">{{ $number }}</div>
    @endif

    @if ($title)
        <h3 @class(['tm-card-title', 'max-w-[80%]' => $number && $hover, 'mt-1' => $number && ! $hover])>{{ $title }}</h3>
    @endif

    @if ($description)
        <p class="tm-card-desc">{{ $description }}</p>
    @endif

    {{ $slot }}
</div>
