{{-- Primary navigation, shared by the desktop header and the mobile drawer. --}}
@props([
    'variant' => 'desktop',
])

@php
    $links = [
        ['label' => 'Trang chủ', 'href' => url('/'), 'active' => request()->is('/')],
        ['label' => 'Xem tướng số', 'href' => '#', 'active' => false],
        ['label' => 'Xem chỉ tay', 'href' => '#', 'active' => false],
        ['label' => 'Kiến thức phong thủy', 'href' => '#', 'active' => false],
        ['label' => 'Chuyên gia', 'href' => '#', 'active' => false],
        ['label' => 'Liên hệ', 'href' => '#', 'active' => false],
    ];
@endphp

@foreach ($links as $link)
    @if ($variant === 'desktop')
        <a href="{{ $link['href'] }}" @class([
            'border-b-2 py-1.5 text-sm whitespace-nowrap transition-colors',
            'border-copper-400 font-semibold text-copper-600' => $link['active'],
            'border-transparent text-ink-500 hover:text-copper-600' => ! $link['active'],
        ]) @if ($link['active']) aria-current="page" @endif>{{ $link['label'] }}</a>
    @else
        <a href="{{ $link['href'] }}" @class([
            'block border-l-[3px] px-6 py-3.5 text-base transition-colors',
            'border-copper-400 bg-copper-50 font-semibold text-copper-700' => $link['active'],
            'border-transparent text-ink-600 hover:bg-cream-50 hover:text-copper-700' => ! $link['active'],
        ]) @if ($link['active']) aria-current="page" @endif>{{ $link['label'] }}</a>
    @endif
@endforeach
