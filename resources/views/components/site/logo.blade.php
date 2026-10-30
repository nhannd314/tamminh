{{-- Brand lockup. tone: light (on cream/white) | dark (on navy) --}}
@props([
    'tone' => 'light',
    'tagline' => 'Hiểu mình - An tâm sống',
])

<a href="{{ url('/') }}" {{ $attributes->class('flex shrink-0 items-center gap-3') }}>
    <img
        src="{{ asset('images/logo-mark.svg') }}"
        alt=""
        @class([
            'size-11 sm:size-12',
            'brightness-0 invert sepia-50 saturate-400 opacity-95' => $tone === 'dark',
        ])
    />
    <span class="leading-none">
        <span @class([
            'block font-serif text-xl font-semibold sm:text-2xl',
            'text-ink-600' => $tone === 'light',
            'text-white' => $tone === 'dark',
        ])>Tâm Minh</span>
        <span @class([
            'mt-1 block text-xs tracking-wide',
            'hidden text-ink-300 sm:block' => $tone === 'light',
            'text-copper-400' => $tone === 'dark',
        ])>{{ $tagline }}</span>
    </span>
</a>
