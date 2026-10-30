@props([
    'post',
    'href' => '#',
])

{{-- @var array{category: string, glyph: string, tone: string, title: string, excerpt: string, author: string, date: string, minutes: int} $post --}}
@php
    $cover = match ($post['tone']) {
        'navy' => 'bg-linear-to-br from-navy-500 to-navy-400 text-copper-400',
        'cream' => 'bg-linear-to-br from-cream-300 to-copper-50 text-copper-600',
        default => 'bg-linear-to-br from-copper-100 to-cream-300 text-copper-700',
    };
@endphp

<article {{ $attributes->class('group relative flex flex-col overflow-hidden rounded-2xl border border-cream-300 bg-white transition-all duration-300 hover:-translate-y-0.5 hover:border-copper-200 hover:shadow-soft-md') }}>
    <div class="relative flex h-[180px] items-center justify-center overflow-hidden lg:h-[200px] {{ $cover }}">
        <span class="relative z-10 font-serif text-8xl leading-none font-medium tracking-[-0.04em] text-shadow-sm transition-transform duration-500 group-hover:scale-105" aria-hidden="true">{{ $post['glyph'] }}</span>
        <svg class="pointer-events-none absolute -top-14 -right-14 size-60" viewBox="0 0 80 80" fill="none" aria-hidden="true">
            <circle cx="40" cy="40" r="36" stroke="currentColor" stroke-opacity=".25" />
            <circle cx="40" cy="40" r="28" stroke="currentColor" stroke-opacity=".18" />
            <circle cx="40" cy="40" r="20" stroke="currentColor" stroke-opacity=".12" />
        </svg>
        <span @class([
            'tm-overline absolute bottom-3.5 left-4 rounded px-3 py-1.5 text-copper-700 backdrop-blur',
            'bg-cream-50/95' => $post['tone'] === 'navy',
            'bg-white/95' => $post['tone'] !== 'navy',
        ])>{{ $post['category'] }}</span>
    </div>

    <div class="flex flex-1 flex-col gap-3 p-6">
        <h3 class="font-serif text-lg leading-snug font-semibold tracking-[-0.005em] text-ink-600 lg:text-xl">
            <a href="{{ $href }}" class="after:absolute after:inset-0">{{ $post['title'] }}</a>
        </h3>
        <p class="line-clamp-3 flex-1 text-sm leading-relaxed text-ink-300">{{ $post['excerpt'] }}</p>
        <div class="flex flex-wrap items-center gap-1.5 border-t border-cream-300 pt-3.5 text-xs text-ink-300">
            <span class="font-semibold text-copper-700">{{ $post['author'] }}</span>
            <span class="text-ink-200">•</span>
            <span>{{ $post['date'] }}</span>
            <span class="text-ink-200">•</span>
            <span>{{ $post['minutes'] }} phút đọc</span>
        </div>
    </div>
</article>
