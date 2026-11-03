{{-- Ảnh vuông của vật phẩm; chưa có ảnh thì hiện chữ Hán trên nền kem. --}}
@props([
    'product',
    'cover' => null,
    'iconClass' => 'text-7xl',
])

@if ($cover)
    <img src="{{ $cover }}" alt="{{ $product->name }}" loading="lazy" {{ $attributes->class('block aspect-square w-full object-cover') }} />
@else
    <div {{ $attributes->class('flex aspect-square w-full items-center justify-center bg-linear-to-br from-copper-100 to-cream-300 font-serif font-medium text-copper-700') }} role="img" aria-label="{{ $product->name }}">
        <span class="{{ $iconClass }}" aria-hidden="true">{{ $product->glyph }}</span>
    </div>
@endif
