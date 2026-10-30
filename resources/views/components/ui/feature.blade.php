{{-- Icon ring + short label (hero / section highlights). --}}
@props([
    'icon',
    'label',
])

<div {{ $attributes->class('group flex flex-col items-start gap-3') }}>
    <x-ui.icon-ring :icon="$icon" size="fluid" />
    <div class="max-w-[160px] text-[13px] leading-snug text-ink-600 sm:text-sm">{{ $label }}</div>
</div>
