{{--
    Phân trang theo design system (thay cho view mặc định của Laravel).
    Usage: <x-ui.pagination :paginator="$posts" />
--}}
@props([
    'paginator',
])

@if ($paginator->hasPages())
    @php
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        $pages = collect([1, $current - 1, $current, $current + 1, $last])->filter(fn (int $page): bool => $page >= 1 && $page <= $last)->unique()->sort()->values();
        $itemClasses = 'flex size-10 items-center justify-center rounded-full border text-sm font-semibold transition-colors';
    @endphp

    <nav {{ $attributes->class('flex flex-wrap items-center justify-center gap-2') }} aria-label="Phân trang">
        @if ($paginator->onFirstPage())
            <span class="{{ $itemClasses }} border-cream-300 text-ink-200" aria-hidden="true"><x-ui.icon name="chevron-down" :size="16" class="rotate-90" /></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $itemClasses }} border-cream-300 bg-white text-ink-500 hover:border-copper-300 hover:text-copper-600" aria-label="Trang trước"><x-ui.icon name="chevron-down" :size="16" class="rotate-90" /></a>
        @endif

        @foreach ($pages as $page)
            @if (! $loop->first && $page - $pages[$loop->index - 1] > 1)
                <span class="px-1 text-ink-300" aria-hidden="true">…</span>
            @endif

            @if ($page === $current)
                <span class="{{ $itemClasses }} border-copper-400 bg-copper-400 text-white" aria-current="page">{{ $page }}</span>
            @else
                <a href="{{ $paginator->url($page) }}" class="{{ $itemClasses }} border-cream-300 bg-white text-ink-500 hover:border-copper-300 hover:text-copper-600">{{ $page }}</a>
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $itemClasses }} border-cream-300 bg-white text-ink-500 hover:border-copper-300 hover:text-copper-600" aria-label="Trang sau"><x-ui.icon name="chevron-down" :size="16" class="-rotate-90" /></a>
        @else
            <span class="{{ $itemClasses }} border-cream-300 text-ink-200" aria-hidden="true"><x-ui.icon name="chevron-down" :size="16" class="-rotate-90" /></span>
        @endif
    </nav>
@endif
