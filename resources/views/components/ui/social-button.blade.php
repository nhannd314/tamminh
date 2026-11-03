@props([
    'network',
    'href' => '#',
])

<a href="{{ $href }}" aria-label="{{ ucfirst($network) }}" {{ $attributes->class('tm-social-btn') }}>
    <x-ui.brand-icon :network="$network" />
</a>
