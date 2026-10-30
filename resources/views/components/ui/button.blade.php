{{--
    Usage:
    <x-ui.button>Xem kết quả</x-ui.button>
    <x-ui.button variant="secondary" size="sm" icon="user" href="/login">Đăng nhập</x-ui.button>
    <x-ui.button variant="outline" pill trailing-icon="arrow">Xem tất cả</x-ui.button>

    variant: primary | secondary | outline | ghost | soft
    size:    sm | md | lg | icon
--}}
@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
    'icon' => null,
    'trailingIcon' => null,
    'iconSize' => 16,
    'block' => false,
    'pill' => false,
])

@php
    $classes = collect([
        'tm-btn',
        'tm-btn-'.$variant,
        'tm-btn-'.$size,
        $block ? 'w-full' : null,
        $pill ? 'rounded-full' : null,
    ])->filter()->implode(' ');
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        @if ($icon)<x-ui.icon :name="$icon" :size="$iconSize" />@endif
        {{ $slot }}
        @if ($trailingIcon)<x-ui.icon :name="$trailingIcon" :size="$iconSize" />@endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>
        @if ($icon)<x-ui.icon :name="$icon" :size="$iconSize" />@endif
        {{ $slot }}
        @if ($trailingIcon)<x-ui.icon :name="$trailingIcon" :size="$iconSize" />@endif
    </button>
@endif
