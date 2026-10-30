{{-- Outlined copper circle holding an icon. size: sm (40px) | md (48px) | lg (56px) | fluid (48px → 56px from sm) --}}
@props([
    'icon',
    'size' => 'lg',
])

@php
    [$box, $iconSize] = match ($size) {
        'sm' => ['size-10', 20],
        'md' => ['size-12', 24],
        'fluid' => ['size-12 sm:size-14', 24],
        default => ['size-14', 26],
    };
@endphp

<span {{ $attributes->class(['tm-icon-ring', $box]) }}>
    <x-ui.icon :name="$icon" :size="$iconSize" />
</span>
