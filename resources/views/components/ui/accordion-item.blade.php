@props([
    'question',
    'open' => false,
])

<div data-accordion-item @class(['border-t border-cream-300 first:border-t-0', 'is-open' => $open])>
    <button type="button" data-accordion-trigger aria-expanded="{{ $open ? 'true' : 'false' }}" class="flex w-full cursor-pointer items-start justify-between gap-4 px-6 py-5 text-left">
        <span class="font-serif text-base leading-snug font-medium text-ink-600 lg:text-lg">{{ $question }}</span>
        <span data-accordion-chevron class="mt-1 text-copper-400 transition-transform duration-300">
            <x-ui.icon name="chevron-down" :size="20" :stroke="1.5" />
        </span>
    </button>
    <div class="tm-accordion-panel">
        <div>
            <div class="px-6 pb-5 pr-12 text-sm leading-relaxed text-ink-500">{{ $slot }}</div>
        </div>
    </div>
</div>
