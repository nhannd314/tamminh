{{--
    Đường dẫn phân cấp; mục cuối (không có href) là trang hiện tại.
    Usage: <x-ui.breadcrumb :items="['Trang chủ' => route('home'), 'Bài viết' => null]" />
--}}
@props([
    'items' => [],
])

<nav aria-label="Breadcrumb" {{ $attributes->class('flex flex-wrap items-center gap-1.5 text-sm text-ink-300') }}>
    @foreach ($items as $label => $href)
        @unless ($loop->first)
            <x-ui.icon name="chevron-down" :size="14" class="-rotate-90" />
        @endunless

        @if ($href)
            <a href="{{ $href }}" class="hover:text-copper-600">{{ $label }}</a>
        @else
            <span class="font-medium text-ink-600" aria-current="page">{{ $label }}</span>
        @endif
    @endforeach
</nav>
