{{--
    Usage:
    <x-ui.accordion>
        <x-ui.accordion-item question="…" open>…</x-ui.accordion-item>
    </x-ui.accordion>

    Pass :multiple="true" to allow several items open at once.
--}}
@props([
    'multiple' => false,
])

<div data-accordion="{{ $multiple ? 'multiple' : 'single' }}" {{ $attributes->class('flex flex-col overflow-hidden rounded-2xl border border-cream-300 bg-white shadow-soft-sm') }}>
    {{ $slot }}
</div>
