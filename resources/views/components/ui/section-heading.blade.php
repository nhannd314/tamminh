{{--
    Usage:
    <x-ui.section-heading eyebrow="Toàn cảnh luận giải" title="Phân tích" accent="tướng số" rule>
        Mô tả ngắn…
    </x-ui.section-heading>

    align: center | left
    size:  lg (uppercase display) | md (sentence case) | sm
--}}
@props([
    'eyebrow' => null,
    'title',
    'accent' => null,
    'align' => 'center',
    'size' => 'lg',
    'rule' => false,
    'as' => 'h2',
])

@php
    $titleClasses = match ($size) {
        'sm' => 'font-serif text-2xl leading-tight font-medium tracking-[-0.01em] text-ink-600 sm:text-3xl lg:text-4xl',
        'md' => 'font-serif text-3xl leading-tight font-medium tracking-[-0.01em] text-ink-600 sm:text-4xl lg:text-5xl',
        default => 'tm-heading uppercase',
    };
@endphp

<div {{ $attributes->class([
    'flex flex-col gap-4',
    'mx-auto items-center text-center' => $align === 'center',
    'items-start' => $align === 'left',
]) }}>
    @if ($eyebrow)
        <x-ui.eyebrow>{{ $eyebrow }}</x-ui.eyebrow>
    @endif

    <{{ $as }} class="{{ $titleClasses }}">
        {{ $title }}@if ($accent)<span class="tm-accent"> {{ $accent }}</span>@endif
    </{{ $as }}>

    @if ($rule)
        <span class="tm-rule"></span>
    @endif

    @if ($slot->isNotEmpty())
        <p @class(['tm-lead', 'max-w-[720px]' => $align === 'center'])>{{ $slot }}</p>
    @endif
</div>
